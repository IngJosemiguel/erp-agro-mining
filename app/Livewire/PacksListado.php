<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\UnidadMedida;
use App\Models\TipoExistencia;
use App\Traits\TieneContextoEmpresa;

class PacksListado extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    // Campos del pack
    public string $nombre = '';
    public string $codigo_interno = '';
    public string $precio_venta = '';
    public bool $es_gravado_igv = true;
    public bool $disponible_pos = true;
    public bool $activo = true;
    public int $unidad_medida_id = 1;
    public int $tipo_existencia_id = 1;

    // Items del pack (productos que lo componen)
    public array $items = [];
    public string $buscarItem = '';
    public array $itemsResultados = [];

    public function updatingBuscar()
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->inicializarContexto();
    }

    public function updatedBuscarItem(): void
    {
        if (strlen($this->buscarItem) < 2) {
            $this->itemsResultados = [];
            return;
        }
        $this->itemsResultados = Producto::deEmpresa($this->empresaId)
            ->activos()
            ->where('tipo_producto', '!=', 'combo')
            ->where(
                fn($q) => $q
                    ->where('nombre', 'LIKE', "%{$this->buscarItem}%")
                    ->orWhere('codigo_interno', 'LIKE', "%{$this->buscarItem}%")
            )
            ->select('id', 'nombre', 'codigo_interno', 'precio_venta')
            ->limit(8)
            ->get()
            ->toArray();
    }

    public function agregarItem(int $productoId): void
    {
        $prod = collect($this->itemsResultados)->firstWhere('id', $productoId);
        if (!$prod)
            return;

        $idx = collect($this->items)->search(fn($i) => $i['id'] == $productoId);
        if ($idx !== false) {
            $this->items[$idx]['cantidad']++;
        } else {
            $this->items[] = [
                'id' => $prod['id'],
                'nombre' => $prod['nombre'],
                'codigo' => $prod['codigo_interno'],
                'cantidad' => 1,
                'precio' => (float) $prod['precio_venta'],
            ];
        }
        $this->buscarItem = '';
        $this->itemsResultados = [];
        $this->calcularPrecio();
    }

    public function quitarItem(int $idx): void
    {
        array_splice($this->items, $idx, 1);
        $this->calcularPrecio();
    }

    public function calcularPrecio(): void
    {
        $this->precio_venta = (string) round(
            collect($this->items)->sum(fn($i) => $i['cantidad'] * $i['precio']),
            2
        );
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $pack = Producto::with('inventarioStock')->findOrFail($id);
        $this->editingId = $id;
        $this->editMode = true;
        $this->nombre = $pack->nombre;
        $this->codigo_interno = $pack->codigo_interno;
        $this->precio_venta = (string) $pack->precio_venta;
        $this->es_gravado_igv = $pack->es_gravado_igv;
        $this->disponible_pos = $pack->disponible_pos;
        $this->activo = $pack->activo;
        $this->unidad_medida_id = $pack->unidad_medida_id;
        $this->tipo_existencia_id = $pack->tipo_existencia_id;
        $this->items = [];   // En esta versión no se persisten los items en BD separada
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'codigo_interno' => 'required|string|max:30',
            'precio_venta' => 'required|numeric|min:0',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
        ]);

        $data = [
            'empresa_id' => $this->empresaId,
            'nombre' => $this->nombre,
            'codigo_interno' => $this->codigo_interno,
            'tipo_producto' => 'combo',
            'precio_venta' => $this->precio_venta,
            'precio_compra' => $this->precio_venta,
            'es_gravado_igv' => $this->es_gravado_igv,
            'disponible_pos' => $this->disponible_pos,
            'activo' => $this->activo,
            'unidad_medida_id' => $this->unidad_medida_id,
            'tipo_existencia_id' => $this->tipo_existencia_id,
        ];

        if ($this->editMode && $this->editingId) {
            Producto::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Pack actualizado correctamente.');
        } else {
            Producto::create($data);
            session()->flash('success', 'Pack creado correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActivo(int $id): void
    {
        $pack = Producto::findOrFail($id);
        $pack->update(['activo' => !$pack->activo]);
    }

    public function delete(int $id): void
    {
        Producto::findOrFail($id)->delete();
        session()->flash('success', 'Pack eliminado.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->codigo_interno = '';
        $this->precio_venta = '';
        $this->es_gravado_igv = true;
        $this->disponible_pos = true;
        $this->activo = true;
        $this->unidad_medida_id = 1;
        $this->tipo_existencia_id = 1;
        $this->items = [];
        $this->buscarItem = '';
        $this->itemsResultados = [];
    }

    public function render()
    {
        $packs = Producto::deEmpresa($this->empresaId)
            ->where('tipo_producto', 'combo')
            ->with(['categoria:id,nombre', 'unidadMedida:id,abreviatura'])
            ->withSum('inventarioStock as stock_total', 'stock_actual')
            ->when($this->buscar, fn($q) => $q->buscar($this->buscar))
            ->orderBy('nombre')
            ->paginate(20);

        return view('livewire.packs-listado', [
            'packs' => $packs,
            'unidadesMedida' => UnidadMedida::where('activo', true)->get(),
            'tiposExistencia' => TipoExistencia::where('activo', true)->get(),
        ]);
    }
}
