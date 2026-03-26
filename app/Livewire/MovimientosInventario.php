<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\KardexMovimiento;
use App\Models\Producto;
use App\Models\Almacen;
use App\Services\InventarioService;
use App\Traits\TieneContextoEmpresa;

class MovimientosInventario extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroTipo = '';
    public string $filtroAlmacen = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';

    // Modal de nuevo movimiento
    public bool $showModal = false;
    public string $tipo_movimiento = 'ingreso';
    public string $producto_id = '';
    public string $almacen_id = '';
    public string $cantidad = '';
    public string $costo_unitario = '0';
    public string $tipo_operacion = '02'; // 01=Compra, 02=Ajuste, 16=Venta
    public string $motivo = '';
    public string $fecha_movimiento = '';

    // Búsqueda de producto
    public string $buscarProducto = '';
    public array $productosEncontrados = [];
    public string $productoSeleccionado = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fechaDesde = now()->startOfMonth()->toDateString();
        $this->fechaHasta = now()->toDateString();
        $this->fecha_movimiento = now()->toDateString();
    }

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }
    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }
    public function updatingFiltroAlmacen(): void
    {
        $this->resetPage();
    }

    public function updatedBuscarProducto(): void
    {
        if (strlen($this->buscarProducto) >= 2) {
            $this->productosEncontrados = Producto::where('empresa_id', $this->empresaId)
                ->where('activo', true)
                ->where('tipo_producto', 'bien')
                ->buscar($this->buscarProducto)
                ->select('id', 'codigo_interno', 'nombre', 'precio_compra')
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->productosEncontrados = [];
        }
    }

    public function seleccionarProducto(int $id): void
    {
        $producto = Producto::find($id);
        if ($producto) {
            $this->producto_id = (string) $producto->id;
            $this->productoSeleccionado = "[{$producto->codigo_interno}] {$producto->nombre}";
            $this->costo_unitario = (string) ($producto->precio_compra ?? 0);
            $this->buscarProducto = '';
            $this->productosEncontrados = [];
        }
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'tipo_movimiento' => 'required|in:ingreso,salida',
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'cantidad' => 'required|numeric|min:0.0001',
            'tipo_operacion' => 'required|string|max:2',
            'fecha_movimiento' => 'required|date',
        ]);

        if ($this->tipo_movimiento === 'ingreso') {
            $this->validate(['costo_unitario' => 'required|numeric|min:0']);
        }

        $service = app(InventarioService::class);

        try {
            $params = [
                'empresa_id' => $this->empresaId,
                'producto_id' => (int) $this->producto_id,
                'almacen_id' => (int) $this->almacen_id,
                'cantidad' => (float) $this->cantidad,
                'costo_unitario' => (float) $this->costo_unitario,
                'tipo_operacion' => $this->tipo_operacion,
                'motivo' => $this->motivo ?: null,
                'fecha_movimiento' => $this->fecha_movimiento,
                'user_id' => auth()->id() ?? 1,
            ];

            if ($this->tipo_movimiento === 'ingreso') {
                $service->registrarIngreso($params);
                session()->flash('success', 'Ingreso registrado correctamente. Stock actualizado.');
            } else {
                $service->registrarSalida($params);
                session()->flash('success', 'Salida registrada correctamente. Stock descontado.');
            }

            $this->showModal = false;
            $this->resetForm();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    protected function resetForm(): void
    {
        $this->tipo_movimiento = 'ingreso';
        $this->producto_id = '';
        $this->almacen_id = '';
        $this->cantidad = '';
        $this->costo_unitario = '0';
        $this->tipo_operacion = '02';
        $this->motivo = '';
        $this->fecha_movimiento = now()->toDateString();
        $this->buscarProducto = '';
        $this->productosEncontrados = [];
        $this->productoSeleccionado = '';
    }

    public function render()
    {
        $query = KardexMovimiento::query()
            ->where('empresa_id', $this->empresaId)
            ->with(['producto:id,codigo_interno,nombre', 'almacen:id,nombre', 'usuario:id,name']);

        if ($this->buscar) {
            $query->whereHas('producto', function ($q) {
                $q->where('nombre', 'LIKE', "%{$this->buscar}%")
                    ->orWhere('codigo_interno', 'LIKE', "%{$this->buscar}%");
            });
        }

        if ($this->filtroTipo) {
            $query->where('tipo_movimiento', $this->filtroTipo);
        }

        if ($this->filtroAlmacen) {
            $query->where('almacen_id', $this->filtroAlmacen);
        }

        if ($this->fechaDesde && $this->fechaHasta) {
            $query->whereBetween('fecha_movimiento', [$this->fechaDesde, $this->fechaHasta]);
        }

        return view('livewire.movimientos-inventario', [
            'movimientos' => $query->orderByDesc('fecha_movimiento')->orderByDesc('id')->paginate(25),
            'almacenes' => Almacen::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }
}
