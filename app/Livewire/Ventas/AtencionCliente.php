<?php

namespace App\Livewire\Ventas;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\ConsultaDocumentoService;
use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;
use App\Models\SerieComprobante;

class AtencionCliente extends Component
{
    use TieneContextoEmpresa;
    // Cabecera Documento
    public $tipoDocumento = '00';
    public $serieSeleccionada = '';
    public $seriesDisponibles = [];
    public $moneda = 'PEN';
    public $formaPago = 'contado';
    public $observaciones = '';

    // Cliente
    public $clienteDocTipo = 'DNI';
    public $clienteDocNumero = '';
    public $clienteRazonSocial = '';
    public $clienteDireccion = '';
    public $clienteId = null;

    public $mensajeCliente = '';
    public $tipoMensajeCliente = ''; // success, error, warning

    // Productos Busqueda
    public $buscarProducto = '';
    public $productosResultados = [];

    // Carrito de Ventas
    public $items = [];

    // Totales
    public $subtotal = 0;
    public $igv = 0;
    public $total = 0;

    // empresaId, sucursalId, almacenId resueltos por TieneContextoEmpresa

    // Modal resultado SUNAT
    public bool $ventaEmitida = false;
    public string $ventaNumero = '';
    public string $ventaCliente = '';
    public string $ventaTotal = '0.00';
    public bool $sunatAceptado = false;
    public string $sunatMensaje = '';
    public ?int $ventaId = null;

    protected $listeners = ['productoBarcodeScanned'];

    public function mount()
    {
        $this->inicializarContexto();
        $this->cargarSeries();
    }

    public function cargarSeries()
    {
        $this->seriesDisponibles = SerieComprobante::where('empresa_id', $this->empresaId)
            ->where('tipo_documento', $this->tipoDocumento)
            ->where('activo', true)
            ->get();

        if ($this->seriesDisponibles->isNotEmpty()) {
            $this->serieSeleccionada = $this->seriesDisponibles->first()->serie;
        } else {
            $this->serieSeleccionada = '';
        }
    }

    public function updatedTipoDocumento()
    {
        // Auto-cambiar tipo de documento de cliente
        if ($this->tipoDocumento === '01') {
            $this->clienteDocTipo = 'RUC';
        } elseif ($this->tipoDocumento === '03') {
            $this->clienteDocTipo = 'DNI';
        }
        $this->cargarSeries();
    }

    public function consultarDniRuc()
    {
        $this->clienteDocNumero = trim($this->clienteDocNumero);
        if (empty($this->clienteDocNumero))
            return;

        $this->mensajeCliente = "Consultando...";
        $this->tipoMensajeCliente = 'warning';

        // Buscar BD Local
        $cliente = Cliente::where('empresa_id', $this->empresaId)
            ->where('numero_documento', $this->clienteDocNumero)
            ->first();

        if ($cliente) {
            $this->clienteId = $cliente->id;
            $this->clienteRazonSocial = $cliente->razon_social;
            $this->clienteDireccion = $cliente->direccion ?? '';
            $this->mensajeCliente = '✅ Cliente encontrado en nuestra base de datos.';
            $this->tipoMensajeCliente = 'success';
            return;
        }

        // Si no está, buscar en API
        try {
            $service = app(ConsultaDocumentoService::class);
            $res = $service->consultarDocumento($this->clienteDocTipo, $this->clienteDocNumero);

            if ($res['success']) {
                $this->clienteRazonSocial = $res['data']['razon_social'];
                $this->clienteDireccion = $res['data']['direccion'] ?? '';
                $this->clienteId = null;
                $this->mensajeCliente = '✅ Extraído desde ' . strtoupper($res['source']) . ' (Se guardará al emitir)';
                $this->tipoMensajeCliente = 'success';
            } else {
                $this->mensajeCliente = '❌ ' . ($res['error'] ?? 'Documento no encontrado o inválido.');
                $this->tipoMensajeCliente = 'error';
                $this->clienteRazonSocial = '';
                $this->clienteDireccion = '';
            }
        } catch (\Exception $e) {
            $this->mensajeCliente = '❌ Error de servicio API.';
            $this->tipoMensajeCliente = 'error';
        }
    }

    public function updatedBuscarProducto()
    {
        if (strlen($this->buscarProducto) < 2) {
            $this->productosResultados = [];
            return;
        }

        $this->productosResultados = Producto::deEmpresa($this->empresaId)
            ->activos()
            ->where(function ($q) {
                $q->where('nombre', 'like', "%{$this->buscarProducto}%")
                    ->orWhere('codigo_interno', 'like', "%{$this->buscarProducto}%")
                    ->orWhere('codigo_barras', 'like', "%{$this->buscarProducto}%");
            })
            ->with('inventarioStock')
            ->limit(8)
            ->get()
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'codigo' => $p->codigo_interno,
                    'precio' => (float) $p->precio_venta,
                    'es_gravado_igv' => $p->es_gravado_igv,
                    'stock' => $p->inventarioStock ? $p->inventarioStock->sum('cantidad') : 0
                ];
            })->toArray();
    }

    public function agregarProducto($productoId)
    {
        $p = collect($this->productosResultados)->firstWhere('id', $productoId);

        if (!$p)
            return;

        // Si ya existe, sumar 1
        $idx = collect($this->items)->search(fn($item) => $item['id'] == $productoId);

        if ($idx !== false) {
            $this->items[$idx]['cantidad']++;
        } else {
            $this->items[] = [
                'id' => $p['id'],
                'nombre' => $p['nombre'],
                'codigo' => $p['codigo'],
                'cantidad' => 1,
                'precio_unitario' => $p['precio'],
                'es_gravado_igv' => $p['es_gravado_igv'],
                'descuento' => 0,
            ];
        }

        $this->buscarProducto = '';
        $this->productosResultados = [];
        $this->calcularTotales();
    }

    public function eliminarItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calcularTotales();
    }

    public function incrementarItem($index)
    {
        $this->items[$index]['cantidad'] = ($this->items[$index]['cantidad'] ?? 1) + 1;
        $this->calcularTotales();
    }

    public function decrementarItem($index)
    {
        $qty = ($this->items[$index]['cantidad'] ?? 1) - 1;
        if ($qty <= 0) {
            $this->eliminarItem($index);
            return;
        }
        $this->items[$index]['cantidad'] = $qty;
        $this->calcularTotales();
    }

    public function nuevaVenta()
    {
        $this->ventaEmitida = false;
        $this->ventaId = null;
        $this->items = [];
        $this->subtotal = 0;
        $this->igv = 0;
        $this->total = 0;
        $this->clienteDocNumero = '';
        $this->clienteRazonSocial = '';
        $this->clienteDireccion = '';
        $this->clienteId = null;
        $this->mensajeCliente = '';
        $this->tipoDocumento = '00';
        $this->cargarSeries();
    }

    public function calcularTotales()
    {
        $st = 0;
        $ig_total = 0;

        foreach ($this->items as &$item) {
            $cant = (float) ($item['cantidad'] ?? 0);
            $precio = (float) ($item['precio_unitario'] ?? 0);
            $dscto = (float) ($item['descuento'] ?? 0);

            $bruto = ($cant * $precio) - $dscto;

            if ($item['es_gravado_igv']) {
                $base = $bruto / 1.18;
                $ig = $bruto - $base;
            } else {
                $base = $bruto;
                $ig = 0;
            }

            $item['total_linea'] = $bruto;
            $st += $base;
            $ig_total += $ig;
        }

        $this->subtotal = $st;
        $this->igv = $ig_total;
        $this->total = $st + $ig_total;
    }

    public function procesarVenta()
    {
        $this->validate([
            'clienteDocNumero' => 'required',
            'clienteRazonSocial' => 'required',
            'tipoDocumento' => 'required',
            'serieSeleccionada' => 'required',
            'items' => 'required|array|min:1'
        ]);

        if ($this->tipoDocumento === '01' && strlen($this->clienteDocNumero) !== 11) {
            $this->addError('clienteDocNumero', 'Para factura se requiere RUC válido de 11 dígitos.');
            return;
        }

        DB::beginTransaction();
        try {
            // 1. Crear o recuperar Cliente
            if (!$this->clienteId) {
                $cli = Cliente::create([
                    'empresa_id' => $this->empresaId,
                    'tipo_documento' => $this->clienteDocTipo,
                    'numero_documento' => $this->clienteDocNumero,
                    'razon_social' => $this->clienteRazonSocial,
                    'direccion' => $this->clienteDireccion,
                    'activo' => true
                ]);
                $this->clienteId = $cli->id;
            }

            // 2. Obtener correlativo de serie
            $serie = SerieComprobante::where('empresa_id', $this->empresaId)
                ->where('serie', $this->serieSeleccionada)
                ->lockForUpdate()
                ->first();

            $correlativoActual = $serie->correlativo_actual;
            $serie->increment('correlativo_actual');

            // 3. Crear cabecera Venta
            $venta = Venta::create([
                'empresa_id' => $this->empresaId,
                'sucursal_id' => $this->sucursalId,
                'almacen_id' => $this->almacenId,
                'cliente_id' => $this->clienteId,
                'user_id' => auth()->id() ?? 1,
                'tipo_documento' => $this->tipoDocumento,
                'serie' => $this->serieSeleccionada,
                'correlativo' => $correlativoActual,
                'fecha_emision' => now()->toDateString(),
                'hora_emision' => now()->format('H:i:s'),
                'moneda' => $this->moneda,
                'forma_pago' => $this->formaPago,
                'descuento_global' => 0,
                'base_imponible' => $this->subtotal,
                'igv' => $this->igv,
                'total' => $this->total,
                'monto_pagado' => $this->formaPago === 'contado' ? $this->total : 0,
                'monto_pendiente' => $this->formaPago === 'credito' ? $this->total : 0,
                'estado' => 'emitido',
                'observaciones' => $this->observaciones,
            ]);

            // Pre-cargar producto para evitar N+1 (carga en bloque fuera del foreach)
            $productoIds = array_column($this->items, 'id');
            $productosMap = Producto::whereIn('id', $productoIds)
                ->with('unidadMedida:id,codigo_sunat')
                ->get()->keyBy('id');

            // 4. Crear detalle
            foreach ($this->items as $idx => $it) {
                $totalLinea = isset($it['total_linea']) ? $it['total_linea'] :
                    (($it['precio_unitario'] * $it['cantidad']) - ($it['descuento'] ?? 0));
                $valorVenta = $it['es_gravado_igv'] ? round($totalLinea / 1.18, 4) : $totalLinea;
                $igvItem = round($totalLinea - $valorVenta, 2);

                $prod = $productosMap->get($it['id']);

                $venta->detalles()->create([
                    'producto_id' => $it['id'],
                    'orden' => $idx + 1,
                    'codigo_producto' => $prod?->codigo_interno ?? $it['codigo'],
                    'descripcion' => $it['nombre'],
                    'unidad_medida' => $prod?->unidadMedida?->codigo_sunat ?? 'NIU',
                    'cantidad' => $it['cantidad'],
                    'precio_unitario' => $it['precio_unitario'],
                    'descuento' => $it['descuento'] ?? 0,
                    'porcentaje_descuento' => 0,
                    'valor_venta' => round($valorVenta, 2),
                    'igv' => $igvItem,
                    'total' => round($totalLinea, 2),
                    'tipo_afectacion_igv' => $it['es_gravado_igv'] ? '10' : '20',
                ]);

            }

            DB::commit();

            // 5. Emitir comprobante a SUNAT si es Factura o Boleta
            $this->sunatAceptado = false;
            $this->sunatMensaje = 'Venta registrada localmente.';

            if (in_array($this->tipoDocumento, ['01', '03'])) {
                try {
                    $feService = app(\App\Services\Sunat\FacturacionElectronicaService::class);
                    $feService->inicializar($venta->empresa);
                    $resultado = $feService->enviarComprobante($venta);
                    $this->sunatAceptado = $resultado['success'] && ($resultado['codigo'] ?? '') === '0';
                    $this->sunatMensaje = $resultado['mensaje'] ?? ($resultado['success'] ? 'Aceptado por SUNAT.' : 'Error SUNAT: venta guardada.');
                } catch (\Exception $ex) {
                    $this->sunatMensaje = 'Venta guardada. Error de conexión SUNAT: ' . $ex->getMessage();
                }
            } else {
                $this->sunatMensaje = 'Nota de venta registrada correctamente.';
            }

            // Mostrar modal de éxito con datos
            $this->ventaId = $venta->id;
            $this->ventaNumero = "{$venta->serie}-{$venta->correlativo}";
            $this->ventaCliente = $this->clienteRazonSocial;
            $this->ventaTotal = number_format($this->total, 2);
            $this->ventaEmitida = true;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('general', 'Error al procesar: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.ventas.atencion-cliente')->layout('layouts.app');
    }
}
