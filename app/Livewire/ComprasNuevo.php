<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Models\Almacen;
use App\Services\InventarioService;
use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;

class ComprasNuevo extends Component
{
    use TieneContextoEmpresa;
    // Proveedor
    public string $proveedor_id = '';
    public string $buscarProveedor = '';
    public array $proveedoresEncontrados = [];
    public string $proveedorSeleccionado = '';

    // Documento
    public string $tipo_documento = '01';
    public string $serie = '';
    public string $correlativo = '';
    public string $fecha_emision = '';
    public string $fecha_vencimiento = '';
    public string $forma_pago = 'contado';
    public string $almacen_id = '';
    public string $observaciones = '';

    // Detalle de productos
    public array $items = [];
    public string $buscarProducto = '';
    public array $productosEncontrados = [];

    // Totales
    public float $subtotal = 0;
    public float $igv = 0;
    public float $total = 0;

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fecha_emision = now()->toDateString();
        $this->items = [];
    }

    public function updatedBuscarProveedor(): void
    {
        if (strlen($this->buscarProveedor) >= 2) {
            $this->proveedoresEncontrados = Proveedor::where('empresa_id', $this->empresaId)
                ->where('activo', true)
                ->where(function ($q) {
                    $q->where('razon_social', 'LIKE', "%{$this->buscarProveedor}%")
                        ->orWhere('numero_documento', 'LIKE', "%{$this->buscarProveedor}%");
                })
                ->select('id', 'numero_documento', 'razon_social')
                ->limit(8)->get()->toArray();
        } else {
            $this->proveedoresEncontrados = [];
        }
    }

    public function seleccionarProveedor(int $id): void
    {
        $p = Proveedor::find($id);
        if ($p) {
            $this->proveedor_id = (string) $p->id;
            $this->proveedorSeleccionado = "{$p->numero_documento} — {$p->razon_social}";
            $this->buscarProveedor = '';
            $this->proveedoresEncontrados = [];
        }
    }

    public function updatedBuscarProducto(): void
    {
        if (strlen($this->buscarProducto) >= 2) {
            $this->productosEncontrados = Producto::where('empresa_id', $this->empresaId)
                ->where('activo', true)
                ->buscar($this->buscarProducto)
                ->select('id', 'codigo_interno', 'nombre', 'precio_compra', 'es_gravado_igv')
                ->limit(10)->get()->toArray();
        } else {
            $this->productosEncontrados = [];
        }
    }

    public function agregarProducto(int $id): void
    {
        $p = Producto::find($id);
        if ($p) {
            $this->items[] = [
                'producto_id' => $p->id,
                'codigo' => $p->codigo_interno,
                'nombre' => $p->nombre,
                'cantidad' => 1,
                'precio_unitario' => (float) $p->precio_compra,
                'es_gravado' => (bool) $p->es_gravado_igv,
                'descuento' => 0,
            ];
            $this->buscarProducto = '';
            $this->productosEncontrados = [];
            $this->recalcular();
        }
    }

    public function eliminarItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->recalcular();
    }

    public function updatedItems(): void
    {
        $this->recalcular();
    }

    protected function recalcular(): void
    {
        $subtotal = 0;
        $igv = 0;
        foreach ($this->items as &$item) {
            $valor = ($item['cantidad'] * $item['precio_unitario']) - $item['descuento'];
            $itemIgv = $item['es_gravado'] ? round($valor * 0.18, 2) : 0;
            $subtotal += $valor;
            $igv += $itemIgv;
        }
        $this->subtotal = round($subtotal, 2);
        $this->igv = round($igv, 2);
        $this->total = round($subtotal + $igv, 2);
    }

    public function guardar(): void
    {
        $this->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'tipo_documento' => 'required|in:01,03',
            'serie' => 'required|string|max:10',
            'correlativo' => 'required|string|max:20',
            'fecha_emision' => 'required|date',
            'almacen_id' => 'required|exists:almacenes,id',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::transaction(function () {
                $compra = Compra::create([
                    'empresa_id' => $this->empresaId,
                    'sucursal_id' => $this->sucursalId,
                    'almacen_id' => (int) $this->almacen_id,
                    'proveedor_id' => (int) $this->proveedor_id,
                    'user_id' => auth()->id() ?? 1,
                    'tipo_documento' => $this->tipo_documento,
                    'serie' => strtoupper($this->serie),
                    'correlativo' => $this->correlativo,
                    'fecha_emision' => $this->fecha_emision,
                    'fecha_vencimiento' => $this->fecha_vencimiento ?: null,
                    'forma_pago' => $this->forma_pago,
                    'subtotal' => $this->subtotal,
                    'base_imponible' => $this->subtotal,
                    'igv' => $this->igv,
                    'total' => $this->total,
                    'estado' => 'registrada',
                    'observaciones' => $this->observaciones ?: null,
                ]);

                $inventarioService = app(InventarioService::class);

                foreach ($this->items as $item) {
                    $valorCompra = ($item['cantidad'] * $item['precio_unitario']) - $item['descuento'];
                    $itemIgv = $item['es_gravado'] ? round($valorCompra * 0.18, 2) : 0;

                    CompraDetalle::create([
                        'compra_id' => $compra->id,
                        'producto_id' => $item['producto_id'],
                        'descripcion' => $item['nombre'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'],
                        'descuento' => $item['descuento'],
                        'valor_compra' => $valorCompra,
                        'igv' => $itemIgv,
                        'total' => $valorCompra + $itemIgv,
                    ]);

                    // Ingresar al inventario
                    $inventarioService->registrarIngreso([
                        'empresa_id' => $this->empresaId,
                        'producto_id' => $item['producto_id'],
                        'almacen_id' => (int) $this->almacen_id,
                        'cantidad' => (float) $item['cantidad'],
                        'costo_unitario' => (float) $item['precio_unitario'],
                        'tipo_operacion' => '01',
                        'motivo' => "Compra {$compra->serie}-{$compra->correlativo}",
                        'documento_tipo' => $compra->tipo_documento,
                        'documento_serie' => $compra->serie,
                        'documento_numero' => $compra->correlativo,
                        'documento_fecha' => $compra->fecha_emision,
                        'documentable_type' => Compra::class,
                        'documentable_id' => $compra->id,
                        'fecha_movimiento' => $compra->fecha_emision,
                        'user_id' => auth()->id() ?? 1,
                    ]);
                }

                $compra->update(['estado' => 'ingresada_almacen']);
            });

            session()->flash('success', 'Compra registrada e ingresada al almacén correctamente.');
            $this->redirect(route('compras.listado'));
        } catch (\Exception $e) {
            session()->flash('error', 'Error al registrar la compra: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.compras-nuevo', [
            'almacenes' => Almacen::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }
}
