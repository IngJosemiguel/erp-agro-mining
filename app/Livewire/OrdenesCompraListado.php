<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\OrdenCompra;
use App\Models\OrdenCompraDetalle;
use App\Models\Proveedor;
use App\Models\Producto;
use App\Traits\TieneContextoEmpresa;

class OrdenesCompraListado extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroEstado = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';

    // Modal nueva OC
    public bool $showModal = false;
    public string $proveedor_id = '';
    public string $buscarProveedor = '';
    public array $proveedoresEncontrados = [];
    public string $proveedorTexto = '';
    public string $fecha_emision = '';
    public string $fecha_entrega_estimada = '';
    public string $condiciones = '';
    public string $observaciones = '';
    public array $items = [];
    public string $buscarProducto = '';
    public array $productosEncontrados = [];
    public float $subtotal = 0;
    public float $igv = 0;
    public float $total = 0;

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fechaDesde = now()->startOfMonth()->toDateString();
        $this->fechaHasta = now()->toDateString();
        $this->fecha_emision = now()->toDateString();
    }

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }
    public function updatingFiltroEstado(): void
    {
        $this->resetPage();
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
            $this->proveedorTexto = "{$p->numero_documento} — {$p->razon_social}";
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
                ->select('id', 'codigo_interno', 'nombre', 'precio_compra')
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
        $sub = 0;
        foreach ($this->items as $item) {
            $sub += $item['cantidad'] * $item['precio_unitario'];
        }
        $this->subtotal = round($sub, 2);
        $this->igv = round($sub * 0.18, 2);
        $this->total = round($sub + $this->igv, 2);
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
            'fecha_emision' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        $oc = OrdenCompra::create([
            'empresa_id' => $this->empresaId,
            'proveedor_id' => (int) $this->proveedor_id,
            'user_id' => auth()->id() ?? 1,
            'codigo' => OrdenCompra::generarCodigo(),
            'fecha_emision' => $this->fecha_emision,
            'fecha_entrega_estimada' => $this->fecha_entrega_estimada ?: null,
            'subtotal' => $this->subtotal,
            'igv' => $this->igv,
            'total' => $this->total,
            'estado' => 'borrador',
            'condiciones' => $this->condiciones ?: null,
            'observaciones' => $this->observaciones ?: null,
        ]);

        foreach ($this->items as $item) {
            OrdenCompraDetalle::create([
                'orden_compra_id' => $oc->id,
                'producto_id' => $item['producto_id'],
                'descripcion' => $item['nombre'],
                'cantidad' => $item['cantidad'],
                'precio_unitario' => $item['precio_unitario'],
                'total' => round($item['cantidad'] * $item['precio_unitario'], 2),
            ]);
        }

        session()->flash('success', "Orden de Compra {$oc->codigo} creada correctamente.");
        $this->showModal = false;
        $this->resetForm();
    }

    public function cambiarEstado(int $id, string $estado): void
    {
        $oc = OrdenCompra::find($id);
        if ($oc) {
            $oc->update(['estado' => $estado]);
            session()->flash('success', "OC {$oc->codigo} marcada como {$estado}.");
        }
    }

    protected function resetForm(): void
    {
        $this->proveedor_id = '';
        $this->proveedorTexto = '';
        $this->fecha_emision = now()->toDateString();
        $this->fecha_entrega_estimada = '';
        $this->condiciones = '';
        $this->observaciones = '';
        $this->items = [];
        $this->subtotal = 0;
        $this->igv = 0;
        $this->total = 0;
        $this->buscarProveedor = '';
        $this->buscarProducto = '';
        $this->proveedoresEncontrados = [];
        $this->productosEncontrados = [];
    }

    public function render()
    {
        $query = OrdenCompra::query()
            ->where('empresa_id', $this->empresaId)
            ->with(['proveedor:id,razon_social,numero_documento', 'usuario:id,name'])
            ->withCount('detalles');

        if ($this->buscar) {
            $query->where(function ($q) {
                $q->where('codigo', 'LIKE', "%{$this->buscar}%")
                    ->orWhereHas('proveedor', fn($pq) =>
                        $pq->where('razon_social', 'LIKE', "%{$this->buscar}%"));
            });
        }

        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }
        if ($this->fechaDesde && $this->fechaHasta) {
            $query->whereBetween('fecha_emision', [$this->fechaDesde, $this->fechaHasta]);
        }

        $totalOC = OrdenCompra::where('empresa_id', $this->empresaId)->whereNotIn('estado', ['anulada'])->sum('total');
        $pendientesOC = OrdenCompra::where('empresa_id', $this->empresaId)->whereIn('estado', ['borrador', 'enviada'])->count();

        return view('livewire.ordenes-compra-listado', [
            'ordenes' => $query->orderByDesc('fecha_emision')->orderByDesc('id')->paginate(20),
            'totalOC' => $totalOC,
            'pendientesOC' => $pendientesOC,
        ]);
    }
}
