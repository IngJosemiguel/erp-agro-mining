<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Traslado;
use App\Models\TrasladoDetalle;
use App\Models\Almacen;
use App\Models\Producto;
use App\Services\InventarioService;
use App\Traits\TieneContextoEmpresa;
use Illuminate\Support\Facades\DB;

class TrasladosInventario extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroEstado = '';

    // Modal
    public bool $showModal = false;
    public string $almacen_origen_id = '';
    public string $almacen_destino_id = '';
    public string $fecha = '';
    public string $motivo = '';
    public string $observaciones = '';
    public array $items = [];
    public string $buscarProducto = '';
    public array $productosEncontrados = [];

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fecha = now()->toDateString();
    }
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function updatedBuscarProducto(): void
    {
        if (strlen($this->buscarProducto) >= 2) {
            $this->productosEncontrados = Producto::where('empresa_id', $this->empresaId)->where('activo', true)
                ->buscar($this->buscarProducto)->select('id', 'codigo_interno', 'nombre', 'precio_compra')
                ->limit(8)->get()->toArray();
        } else {
            $this->productosEncontrados = [];
        }
    }

    public function agregarProducto(int $id): void
    {
        $p = Producto::find($id);
        if ($p) {
            $this->items[] = ['producto_id' => $p->id, 'codigo' => $p->codigo_interno, 'nombre' => $p->nombre, 'cantidad' => 1, 'costo' => (float) $p->precio_compra];
            $this->buscarProducto = '';
            $this->productosEncontrados = [];
        }
    }

    public function eliminarItem(int $i): void
    {
        unset($this->items[$i]);
        $this->items = array_values($this->items);
    }

    public function openModal(): void
    {
        $this->reset(['almacen_origen_id', 'almacen_destino_id', 'motivo', 'observaciones', 'items', 'buscarProducto', 'productosEncontrados']);
        $this->fecha = now()->toDateString();
        $this->showModal = true;
    }

    public function guardar(): void
    {
        $this->validate([
            'almacen_origen_id' => 'required|exists:almacenes,id',
            'almacen_destino_id' => 'required|exists:almacenes,id|different:almacen_origen_id',
            'fecha' => 'required|date',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::transaction(function () {
                $traslado = Traslado::create([
                    'empresa_id' => $this->empresaId,
                    'almacen_origen_id' => (int) $this->almacen_origen_id,
                    'almacen_destino_id' => (int) $this->almacen_destino_id,
                    'user_id' => auth()->id() ?? 1,
                    'codigo' => Traslado::generarCodigo(),
                    'fecha' => $this->fecha,
                    'estado' => 'completado',
                    'motivo' => $this->motivo ?: null,
                    'observaciones' => $this->observaciones ?: null,
                ]);

                $inv = app(InventarioService::class);
                foreach ($this->items as $item) {
                    TrasladoDetalle::create([
                        'traslado_id' => $traslado->id,
                        'producto_id' => $item['producto_id'],
                        'cantidad' => $item['cantidad'],
                        'costo_unitario' => $item['costo'],
                    ]);
                    // Salida del origen
                    $inv->registrarSalida([
                        'empresa_id' => $this->empresaId,
                        'producto_id' => $item['producto_id'],
                        'almacen_id' => (int) $this->almacen_origen_id,
                        'cantidad' => (float) $item['cantidad'],
                        'costo_unitario' => (float) $item['costo'],
                        'tipo_operacion' => '11',
                        'motivo' => "Traslado {$traslado->codigo} → Salida",
                        'documentable_type' => Traslado::class,
                        'documentable_id' => $traslado->id,
                        'fecha_movimiento' => $this->fecha,
                        'user_id' => auth()->id() ?? 1,
                    ]);
                    // Ingreso al destino
                    $inv->registrarIngreso([
                        'empresa_id' => $this->empresaId,
                        'producto_id' => $item['producto_id'],
                        'almacen_id' => (int) $this->almacen_destino_id,
                        'cantidad' => (float) $item['cantidad'],
                        'costo_unitario' => (float) $item['costo'],
                        'tipo_operacion' => '11',
                        'motivo' => "Traslado {$traslado->codigo} → Ingreso",
                        'documentable_type' => Traslado::class,
                        'documentable_id' => $traslado->id,
                        'fecha_movimiento' => $this->fecha,
                        'user_id' => auth()->id() ?? 1,
                    ]);
                }
            });
            session()->flash('success', 'Traslado registrado y stock actualizado.');
            $this->showModal = false;
        } catch (\Exception $e) {
            session()->flash('error', 'Error: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $query = Traslado::where('empresa_id', $this->empresaId)
            ->with(['almacenOrigen:id,nombre', 'almacenDestino:id,nombre', 'usuario:id,name'])->withCount('detalles');
        if ($this->buscar) {
            $query->where('codigo', 'LIKE', "%{$this->buscar}%");
        }
        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }

        return view('livewire.traslados-inventario', [
            'traslados' => $query->orderByDesc('fecha')->orderByDesc('id')->paginate(20),
            'almacenes' => Almacen::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }
}
