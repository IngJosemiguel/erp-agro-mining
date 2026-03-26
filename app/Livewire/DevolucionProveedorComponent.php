<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DevolucionProveedor;
use App\Models\DevolucionProveedorDetalle;
use App\Models\Proveedor;
use App\Models\Almacen;
use App\Models\Producto;
use App\Models\Compra;
use App\Services\InventarioService;
use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;

class DevolucionProveedorComponent extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroEstado = '';

    // Modal
    public bool $showModal = false;
    public string $proveedor_id = '';
    public string $buscarProveedor = '';
    public array $proveedoresEncontrados = [];
    public string $proveedorTexto = '';
    public string $almacen_id = '';
    public string $fecha = '';
    public string $motivo = '';
    public string $observaciones = '';
    public array $items = [];
    public string $buscarProducto = '';
    public array $productosEncontrados = [];
    public float $total = 0;

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fecha = now()->toDateString();
    }
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedBuscarProveedor(): void
    {
        if (strlen($this->buscarProveedor) >= 2) {
            $this->proveedoresEncontrados = Proveedor::where('empresa_id', $this->empresaId)->where('activo', true)
                ->where(fn($q) =>
                    $q->where('razon_social', 'LIKE', "%{$this->buscarProveedor}%")
                        ->orWhere('numero_documento', 'LIKE', "%{$this->buscarProveedor}%"))
                ->select('id', 'numero_documento', 'razon_social')->limit(8)->get()->toArray();
        } else {
            $this->proveedoresEncontrados = [];
        }
    }

    public function seleccionarProveedor(int $id): void
    {
        $p = Proveedor::find($id);
        if ($p) {
            $this->proveedor_id = (string) $p->id;
            $this->proveedorTexto = "{$p->numero_documento} — {$p->razon_social}";
            $this->buscarProveedor = '';
            $this->proveedoresEncontrados = [];
        }
    }

    public function updatedBuscarProducto(): void
    {
        if (strlen($this->buscarProducto) >= 2) {
            $this->productosEncontrados = Producto::where('empresa_id', $this->empresaId)->where('activo', true)
                ->buscar($this->buscarProducto)
                ->select('id', 'codigo_interno', 'nombre', 'precio_compra')
                ->limit(8)->get()->toArray();
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
                'costo_unitario' => (float) $p->precio_compra,
                'motivo_item' => '',
            ];
            $this->buscarProducto = '';
            $this->productosEncontrados = [];
            $this->recalcular();
        }
    }

    public function eliminarItem(int $i): void
    {
        unset($this->items[$i]);
        $this->items = array_values($this->items);
        $this->recalcular();
    }

    public function updatedItems(): void
    {
        $this->recalcular();
    }

    protected function recalcular(): void
    {
        $this->total = round(collect($this->items)->sum(fn($i) => $i['cantidad'] * $i['costo_unitario']), 2);
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'proveedor_id' => 'required|exists:proveedores,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'fecha' => 'required|date',
            'motivo' => 'required|string|min:5',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::transaction(function () {
                $dev = DevolucionProveedor::create([
                    'empresa_id' => $this->empresaId,
                    'proveedor_id' => (int) $this->proveedor_id,
                    'almacen_id' => (int) $this->almacen_id,
                    'user_id' => auth()->id() ?? 1,
                    'codigo' => DevolucionProveedor::generarCodigo(),
                    'fecha' => $this->fecha,
                    'motivo' => $this->motivo,
                    'total' => $this->total,
                    'estado' => 'completada',
                    'observaciones' => $this->observaciones ?: null,
                ]);

                $inv = app(InventarioService::class);
                foreach ($this->items as $item) {
                    DevolucionProveedorDetalle::create([
                        'devolucion_proveedor_id' => $dev->id,
                        'producto_id' => $item['producto_id'],
                        'cantidad' => (float) $item['cantidad'],
                        'costo_unitario' => (float) $item['costo_unitario'],
                        'total' => round($item['cantidad'] * $item['costo_unitario'], 2),
                        'motivo_item' => $item['motivo_item'] ?: null,
                    ]);

                    // Salida de inventario — producto retorna al proveedor
                    $inv->registrarSalida([
                        'empresa_id' => $this->empresaId,
                        'producto_id' => $item['producto_id'],
                        'almacen_id' => (int) $this->almacen_id,
                        'cantidad' => (float) $item['cantidad'],
                        'costo_unitario' => (float) $item['costo_unitario'],
                        'tipo_operacion' => '07',
                        'motivo' => "Devolución a proveedor {$dev->codigo}",
                        'documentable_type' => DevolucionProveedor::class,
                        'documentable_id' => $dev->id,
                        'fecha_movimiento' => $this->fecha,
                        'user_id' => auth()->id() ?? 1,
                    ]);
                }
            });

            session()->flash('success', 'Devolución registrada y stock descontado correctamente.');
            $this->showModal = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function cambiarEstado(int $id, string $estado): void
    {
        $dev = DevolucionProveedor::find($id);
        if ($dev) {
            $dev->update(['estado' => $estado]);
            session()->flash('success', "Devolución {$dev->codigo} marcada como {$estado}.");
        }
    }

    protected function resetForm(): void
    {
        $this->proveedor_id = '';
        $this->proveedorTexto = '';
        $this->almacen_id = '';
        $this->fecha = now()->toDateString();
        $this->motivo = '';
        $this->observaciones = '';
        $this->items = [];
        $this->total = 0;
        $this->buscarProveedor = '';
        $this->buscarProducto = '';
        $this->proveedoresEncontrados = [];
        $this->productosEncontrados = [];
    }

    public function render()
    {
        $query = DevolucionProveedor::where('empresa_id', $this->empresaId)
            ->with(['proveedor:id,razon_social,numero_documento', 'almacen:id,nombre', 'usuario:id,name'])
            ->withCount('detalles');

        if ($this->buscar) {
            $query->where(fn($q) =>
                $q->where('codigo', 'LIKE', "%{$this->buscar}%")
                    ->orWhereHas('proveedor', fn($pq) =>
                        $pq->where('razon_social', 'LIKE', "%{$this->buscar}%")));
        }
        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        $totalDevuelto = DevolucionProveedor::where('empresa_id', $this->empresaId)
            ->whereNotIn('estado', ['anulada'])->sum('total');
        $cantDevoluciones = DevolucionProveedor::where('empresa_id', $this->empresaId)
            ->whereNotIn('estado', ['anulada'])->count();

        return view('livewire.devolucion-proveedor', [
            'devoluciones' => $query->orderByDesc('fecha')->orderByDesc('id')->paginate(20),
            'almacenes' => Almacen::where('activo', true)->orderBy('nombre')->get(),
            'totalDevuelto' => $totalDevuelto,
            'cantDevoluciones' => $cantDevoluciones,
        ]);
    }
}
