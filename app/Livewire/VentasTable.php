<?php

namespace App\Livewire;

use App\Models\Venta;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Almacen;
use App\Models\SerieComprobante;
use App\Services\VentaService;
use App\Services\Sunat\FacturacionElectronicaService;
use App\Services\ConsultaDocumentoService;
use App\Traits\TieneContextoEmpresa;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class VentasTable extends Component
{
    use WithPagination, TieneContextoEmpresa;

    // ─── Filtros de listado ────────────────────────────────────
    public string $buscar = '';
    public string $filtroEstado = '';
    public string $filtroTipoDoc = '';
    public string $filtroFechaDesde = '';
    public string $filtroFechaHasta = '';
    public string $ordenBy = 'id';
    public string $ordenDir = 'desc';

    // ─── Modal Nueva Venta ─────────────────────────────────────
    public bool $mostrarModal = false;
    public bool $mostrarModalDetalle = false;

    // Cabecera de la venta
    public string $tipoDocumento = '00'; // 00=Nota de Venta, 01=Factura, 03=Boleta
    public string $moneda = 'PEN';
    public string $formaPago = 'contado';
    public string $observaciones = '';

    // Cliente
    public string $clienteDocTipo = 'DNI';
    public string $clienteDocNumero = '';
    public string $clienteRazonSocial = '';
    public string $clienteDireccion = '';
    public ?int $clienteId = null;
    public bool $buscandoCliente = false;
    public string $mensajeCliente = '';

    // Búsqueda de productos
    public string $buscarProducto = '';
    public array $productosEncontrados = [];
    public bool $buscandoProducto = false;

    // Ítems del carrito
    public array $items = [];

    // Totales calculados
    public float $subtotal = 0;
    public float $descuentoGlobal = 0;
    public float $baseImponible = 0;
    public float $igv = 0;
    public float $total = 0;

    // Series disponibles para el tipo de documento
    public array $seriesDisponibles = [];

    // Venta seleccionada para ver detalle
    public ?int $ventaDetalleId = null;

    // ─── Almacén activo (resuelto desde TieneContextoEmpresa) ───

    protected $listeners = [
        'ventaCreada' => '$refresh',
    ];

    protected function rules(): array
    {
        return [
            'tipoDocumento' => 'required|in:00,01,03,07,08',
            'moneda' => 'required|in:PEN,USD',
            'formaPago' => 'required|in:contado,credito',
            'clienteDocNumero' => 'required|string',
            'clienteRazonSocial' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ];
    }

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->filtroFechaDesde = now()->startOfMonth()->format('Y-m-d');
        $this->filtroFechaHasta = now()->format('Y-m-d');
        $this->cargarSeriesDisponibles();
    }

    // ═══════════════════════════════════════════════════════════
    //  RENDER
    // ═══════════════════════════════════════════════════════════

    public function render()
    {
        $ventas = Venta::deEmpresa($this->empresaId)
            ->with(['cliente:id,razon_social,numero_documento', 'vendedor:id,name'])
            ->when($this->buscar, fn($q) => $q->where(function ($q) {
                $q->where('serie', 'like', "%{$this->buscar}%")
                    ->orWhere('correlativo', 'like', "%{$this->buscar}%")
                    ->orWhereHas('cliente', fn($q) => $q->where('razon_social', 'like', "%{$this->buscar}%"));
            }))
            ->when($this->filtroEstado, fn($q) => $q->where('estado', $this->filtroEstado))
            ->when($this->filtroTipoDoc, fn($q) => $q->where('tipo_documento', $this->filtroTipoDoc))
            ->when($this->filtroFechaDesde, fn($q) => $q->whereDate('fecha_emision', '>=', $this->filtroFechaDesde))
            ->when($this->filtroFechaHasta, fn($q) => $q->whereDate('fecha_emision', '<=', $this->filtroFechaHasta))
            ->orderBy($this->ordenBy, $this->ordenDir)
            ->paginate(15);

        $estadisticas = [
            'total_dia' => Venta::deEmpresa($this->empresaId)
                ->whereDate('fecha_emision', today())
                ->whereNotIn('estado', ['anulado', 'borrador'])
                ->sum('total'),
            'pendientes_sunat' => Venta::deEmpresa($this->empresaId)->pendientesSunat()->count(),
            'count_dia' => Venta::deEmpresa($this->empresaId)
                ->whereDate('fecha_emision', today())
                ->whereNotIn('estado', ['anulado', 'borrador'])
                ->count(),
        ];

        return view('livewire.ventas-table', compact('ventas', 'estadisticas'));
    }

    // ═══════════════════════════════════════════════════════════
    //  MODAL NUEVA VENTA
    // ═══════════════════════════════════════════════════════════

    public function abrirModal(): void
    {
        $this->reset([
            'tipoDocumento',
            'moneda',
            'formaPago',
            'observaciones',
            'clienteDocTipo',
            'clienteDocNumero',
            'clienteRazonSocial',
            'clienteDireccion',
            'clienteId',
            'mensajeCliente',
            'buscarProducto',
            'productosEncontrados',
            'items',
            'subtotal',
            'descuentoGlobal',
            'baseImponible',
            'igv',
            'total',
        ]);
        $this->tipoDocumento = '00';
        $this->moneda = 'PEN';
        $this->formaPago = 'contado';
        $this->cargarSeriesDisponibles();
        $this->mostrarModal = true;
    }

    public function cerrarModal(): void
    {
        $this->mostrarModal = false;
        $this->resetValidation();
    }

    // ═══════════════════════════════════════════════════════════
    //  CONSULTA DNI/RUC EN TIEMPO REAL
    // ═══════════════════════════════════════════════════════════

    public function consultarDocumentoCliente(): void
    {
        $numero = trim($this->clienteDocNumero);
        if (empty($numero))
            return;

        $this->buscandoCliente = true;
        $this->mensajeCliente = '';

        // Primero buscar en la BD local
        $clienteLocal = Cliente::where('empresa_id', $this->empresaId)
            ->where('numero_documento', $numero)
            ->first();

        if ($clienteLocal) {
            $this->clienteId = $clienteLocal->id;
            $this->clienteRazonSocial = $clienteLocal->razon_social;
            $this->clienteDireccion = $clienteLocal->direccion ?? '';
            $this->mensajeCliente = '✓ Cliente encontrado en base de datos local.';
            $this->buscandoCliente = false;
            return;
        }

        // Si no está local, consultar API
        try {
            $service = app(ConsultaDocumentoService::class);
            $resultado = $service->consultarDocumento($this->clienteDocTipo, $numero);

            if ($resultado['success']) {
                $data = $resultado['data'];
                $this->clienteRazonSocial = $data['razon_social'];
                $this->clienteDireccion = $data['direccion'] ?? '';
                $this->clienteId = null; // No existe en BD aún, se creará al guardar
                $this->mensajeCliente = '✓ Datos obtenidos de ' . strtoupper($resultado['source'] ?? 'API') . '. Se registrará al guardar.';
            } else {
                $this->mensajeCliente = '⚠ ' . ($resultado['error'] ?? 'No se encontró el documento.');
            }
        } catch (\Exception $e) {
            $this->mensajeCliente = '⚠ Error de conexión con el servicio de consulta.';
        }

        $this->buscandoCliente = false;
    }

    // ═══════════════════════════════════════════════════════════
    //  BÚSQUEDA DE PRODUCTOS
    // ═══════════════════════════════════════════════════════════

    public function updatedBuscarProducto(): void
    {
        if (strlen($this->buscarProducto) < 2) {
            $this->productosEncontrados = [];
            return;
        }

        $this->productosEncontrados = Producto::deEmpresa($this->empresaId)
            ->activos()
            ->disponiblePos()
            ->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->buscarProducto}%")
                    ->orWhere('codigo_interno', 'like', "%{$this->buscarProducto}%")
                    ->orWhere('codigo_barras', 'like', "%{$this->buscarProducto}%");
            })
            ->with('inventarioStock')
            ->limit(10)
            ->get(['id', 'nombre', 'codigo_interno', 'precio_venta', 'es_gravado_igv'])
            ->map(fn($p) => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'codigo' => $p->codigo_interno,
                'precio' => (float) $p->precio_venta,
                'igv_porcentaje' => $p->es_gravado_igv ? 18.00 : 0.00,
                'tipo_afectacion' => $p->es_gravado_igv ? '10' : '20', // 10=Gravado, 20=Exonerado
                'stock' => $p->inventarioStock ? $p->inventarioStock->sum('stock_actual') : 0,
            ])->toArray();
    }

    public function agregarProducto(int $productoId): void
    {
        $encontrado = collect($this->productosEncontrados)->firstWhere('id', $productoId);
        if (!$encontrado)
            return;

        // ¿Ya está en el carrito?
        $idx = collect($this->items)->search(fn($i) => $i['producto_id'] === $productoId);

        if ($idx !== false) {
            $this->items[$idx]['cantidad'] += 1;
        } else {
            $this->items[] = [
                'producto_id' => $productoId,
                'nombre' => $encontrado['nombre'],
                'codigo' => $encontrado['codigo'],
                'cantidad' => 1,
                'precio_unitario' => $encontrado['precio'],
                'igv_porcentaje' => $encontrado['igv_porcentaje'],
                'tipo_afectacion' => $encontrado['tipo_afectacion'],
                'descuento' => 0,
                'stock_disponible' => $encontrado['stock'],
            ];
        }

        $this->buscarProducto = '';
        $this->productosEncontrados = [];
        $this->calcularTotales();
    }

    public function quitarItem(int $index): void
    {
        array_splice($this->items, $index, 1);
        $this->calcularTotales();
    }

    public function updatedItems(): void
    {
        $this->calcularTotales();
    }

    public function calcularTotales(): void
    {
        $subtotal = 0;
        $igvTotal = 0;

        foreach ($this->items as &$item) {
            $cantidad = (float) ($item['cantidad'] ?? 0);
            $precio = (float) ($item['precio_unitario'] ?? 0);
            $descuento = (float) ($item['descuento'] ?? 0);
            $igvPct = (float) ($item['igv_porcentaje'] ?? 18);
            $tipoAfectacion = $item['tipo_afectacion'] ?? '10';

            $valorBruto = $cantidad * $precio;
            $valorNeto = $valorBruto - $descuento;

            // Precio ya incluye IGV — extraer base imponible
            if ($tipoAfectacion === '10') { // Gravado
                $valorVenta = round($valorNeto / (1 + $igvPct / 100), 4);
                $igv = round($valorNeto - $valorVenta, 4);
            } else { // Inafecto, exonerado
                $valorVenta = $valorNeto;
                $igv = 0;
            }

            $item['valor_venta'] = $valorVenta;
            $item['igv'] = $igv;
            $item['total_item'] = $valorNeto;

            $subtotal += $valorNeto;
            $igvTotal += $igv;
        }
        unset($item);

        $this->subtotal = round($subtotal, 2);
        $this->baseImponible = round($subtotal - $igvTotal, 2);
        $this->igv = round($igvTotal, 2);
        $this->total = round($subtotal, 2);
    }

    // ═══════════════════════════════════════════════════════════
    //  GUARDAR VENTA
    // ═══════════════════════════════════════════════════════════

    public function guardarVenta(): void
    {
        $this->validate();

        if (empty($this->items)) {
            $this->addError('items', 'Debe agregar al menos un producto.');
            return;
        }

        DB::beginTransaction();
        try {
            // Crear o encontrar cliente
            $cliente = $this->obtenerOCrearCliente();

            // Obtener serie y correlativo
            $serie = $this->obtenerSerie();

            // Preparar datos para VentaService
            $datosVenta = [
                'empresa_id' => $this->empresaId,
                'sucursal_id' => $this->sucursalId,
                'almacen_id' => $this->almacenId,
                'cliente_id' => $cliente->id,
                'user_id' => Auth::id() ?? 1,
                'tipo_documento' => $this->tipoDocumento,
                'serie' => $serie['serie'],
                'moneda' => $this->moneda,
                'forma_pago' => $this->formaPago,
                'observaciones' => $this->observaciones,
                'items' => array_map(fn($item) => [
                    'producto_id' => $item['producto_id'],
                    'descripcion' => $item['nombre'],
                    'cantidad' => (float) $item['cantidad'],
                    'precio_unitario' => (float) $item['precio_unitario'],
                    'descuento' => (float) ($item['descuento'] ?? 0),
                    'tipo_afectacion_igv' => $item['tipo_afectacion'] ?? '10',
                    'porcentaje_igv' => (float) $item['igv_porcentaje'],
                ], $this->items),
            ];

            $ventaService = app(VentaService::class);
            $venta = $ventaService->crearVenta($datosVenta);

            DB::commit();

            $this->mostrarModal = false;
            $this->resetValidation();
            session()->flash('success', "Venta {$venta->serie}-{$venta->correlativo} creada exitosamente.");
            $this->dispatch('ventaCreada');

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Error al crear la venta: ' . $e->getMessage());
        }
    }

    /**
     * Enviar un comprobante SUNAT desde el listado.
     */
    public function enviarSunat(int $ventaId): void
    {
        $venta = Venta::with(['detalles.producto.unidadMedida', 'cliente', 'empresa', 'cuotas'])
            ->findOrFail($ventaId);

        if (!$venta->esComprobanteSunat()) {
            session()->flash('error', 'Las Notas de Venta no se envían a SUNAT.');
            return;
        }

        try {
            $feService = app(FacturacionElectronicaService::class);
            $feService->inicializar($venta->empresa);
            $resultado = $feService->enviarComprobante($venta);

            if ($resultado['success']) {
                session()->flash('success', "Comprobante enviado a SUNAT. Código: {$resultado['codigo']} - {$resultado['mensaje']}");
            } else {
                session()->flash('error', "SUNAT rechazó: {$resultado['codigo']} - {$resultado['mensaje']}");
            }
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        } catch (\Exception $e) {
            session()->flash('error', 'Error al conectar con SUNAT: ' . $e->getMessage());
        }
    }

    /**
     * Ver detalle de una venta.
     */
    public function verDetalle(int $ventaId): void
    {
        $this->ventaDetalleId = $ventaId;
        $this->mostrarModalDetalle = true;
    }

    // ═══════════════════════════════════════════════════════════
    //  HELPERS PRIVADOS
    // ═══════════════════════════════════════════════════════════

    private function obtenerOCrearCliente(): Cliente
    {
        if ($this->clienteId) {
            return Cliente::findOrFail($this->clienteId);
        }

        // Crear cliente si vino de la API y no existía
        return Cliente::firstOrCreate(
            [
                'empresa_id' => $this->empresaId,
                'numero_documento' => $this->clienteDocNumero,
            ],
            [
                'tipo_documento' => $this->clienteDocTipo,
                'razon_social' => $this->clienteRazonSocial,
                'direccion' => $this->clienteDireccion,
                'activo' => true,
            ]
        );
    }

    private function obtenerSerie(): array
    {
        $serie = SerieComprobante::where('empresa_id', $this->empresaId)
            ->where('tipo_documento', $this->tipoDocumento)
            ->where('activo', true)
            ->first();

        if (!$serie) {
            // Serie por defecto si no hay configurada
            $prefijos = ['00' => 'NV01', '01' => 'F001', '03' => 'B001'];
            return [
                'serie' => $prefijos[$this->tipoDocumento] ?? 'X001',
                'correlativo' => str_pad(
                    Venta::deEmpresa($this->empresaId)
                        ->where('tipo_documento', $this->tipoDocumento)
                        ->max('correlativo') + 1,
                    8,
                    '0',
                    STR_PAD_LEFT
                ),
            ];
        }

        return [
            'serie' => $serie->serie,
            'correlativo' => $serie->siguienteCorrelativo(),
        ];
    }

    private function cargarSeriesDisponibles(): void
    {
        $this->seriesDisponibles = SerieComprobante::where('empresa_id', $this->empresaId)
            ->where('activo', true)
            ->get(['serie', 'tipo_documento'])
            ->groupBy('tipo_documento')
            ->toArray();
    }

    public function limpiarBusquedaProducto(): void
    {
        $this->buscarProducto = '';
        $this->productosEncontrados = [];
    }

    public function updatedBuscar(): void
    {
        $this->resetPage();
    }
    public function updatedFiltroEstado(): void
    {
        $this->resetPage();
    }
    public function updatedFiltroTipoDoc(): void
    {
        $this->resetPage();
    }
}
