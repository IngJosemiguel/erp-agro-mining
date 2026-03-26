<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Marca;
use App\Traits\TieneContextoEmpresa;

class ProductosTable extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $categoriaFiltro = '';
    public string $marcaFiltro = '';
    public string $tipoFiltro = '';
    public string $ordenar = 'nombre';
    public string $direccion = 'asc';
    public int $perPage = 25;

    // Modal
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    // Form fields
    public string $nombre = '';
    public string $codigo_interno = '';
    public string $codigo_barras = '';
    public string $tipo_producto = 'bien';
    public string $precio_venta = '';
    public string $precio_compra = '';
    public string $stock_minimo = '0';
    public int $categoria_id = 0;
    public int $marca_id = 0;
    public int $unidad_medida_id = 1;
    public int $tipo_existencia_id = 1;
    public bool $es_gravado_igv = true;
    public bool $controla_lote = false;
    public bool $controla_serie = false;
    public bool $disponible_pos = true;
    public bool $activo = true;

    protected $queryString = [
        'buscar' => ['except' => ''],
        'categoriaFiltro' => ['except' => ''],
        'marcaFiltro' => ['except' => ''],
    ];

    public function updatingBuscar()
    {
        $this->resetPage();
    }
    public function updatingCategoriaFiltro()
    {
        $this->resetPage();
    }
    public function updatingMarcaFiltro()
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->ordenar === $field) {
            $this->direccion = $this->direccion === 'asc' ? 'desc' : 'asc';
        } else {
            $this->ordenar = $field;
            $this->direccion = 'asc';
        }
    }

    public function mount(): void
    {
        $this->inicializarContexto();
    }

    public function openCreateModal(): void
    {
        $this->resetFormFields();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $producto = Producto::findOrFail($id);
        $this->editingId = $id;
        $this->editMode = true;
        $this->nombre = $producto->nombre;
        $this->codigo_interno = $producto->codigo_interno;
        $this->codigo_barras = $producto->codigo_barras ?? '';
        $this->tipo_producto = $producto->tipo_producto;
        $this->precio_venta = (string) $producto->precio_venta;
        $this->precio_compra = (string) ($producto->precio_compra ?? '');
        $this->stock_minimo = (string) ($producto->stock_minimo ?? '0');
        $this->categoria_id = $producto->categoria_id ?? 0;
        $this->marca_id = $producto->marca_id ?? 0;
        $this->unidad_medida_id = $producto->unidad_medida_id;
        $this->tipo_existencia_id = $producto->tipo_existencia_id;
        $this->es_gravado_igv = $producto->es_gravado_igv;
        $this->controla_lote = $producto->controla_lote;
        $this->controla_serie = $producto->controla_serie;
        $this->disponible_pos = $producto->disponible_pos;
        $this->activo = $producto->activo;
        $this->showModal = true;
    }

    public function save(): void
    {
        $rules = [
            'nombre' => 'required|string|max:255',
            'codigo_interno' => 'required|string|max:30',
            'tipo_producto' => 'required|in:bien,servicio,combo',
            'precio_venta' => 'required|numeric|min:0',
            'unidad_medida_id' => 'required|exists:unidades_medida,id',
            'tipo_existencia_id' => 'required|exists:tipos_existencia,id',
        ];

        $validated = $this->validate($rules);

        $data = [
            'empresa_id' => $this->empresaId,
            'nombre' => $this->nombre,
            'codigo_interno' => $this->codigo_interno,
            'codigo_barras' => $this->codigo_barras ?: null,
            'tipo_producto' => $this->tipo_producto,
            'precio_venta' => $this->precio_venta,
            'precio_compra' => $this->precio_compra ?: null,
            'stock_minimo' => $this->stock_minimo ?: 0,
            'categoria_id' => $this->categoria_id ?: null,
            'marca_id' => $this->marca_id ?: null,
            'unidad_medida_id' => $this->unidad_medida_id,
            'tipo_existencia_id' => $this->tipo_existencia_id,
            'es_gravado_igv' => $this->es_gravado_igv,
            'controla_lote' => $this->controla_lote,
            'controla_serie' => $this->controla_serie,
            'disponible_pos' => $this->disponible_pos,
            'activo' => $this->activo,
        ];

        if ($this->editMode && $this->editingId) {
            Producto::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Producto actualizado exitosamente.');
        } else {
            Producto::create($data);
            session()->flash('success', 'Producto creado exitosamente.');
        }

        $this->showModal = false;
        $this->resetFormFields();
    }

    public function delete(int $id): void
    {
        $producto = Producto::findOrFail($id);
        $stockTotal = $producto->inventarioStock()->sum('stock_actual');

        if ($stockTotal > 0) {
            session()->flash('error', "No se puede eliminar: tiene {$stockTotal} unidades en stock.");
            return;
        }

        $producto->delete();
        session()->flash('success', 'Producto eliminado.');
    }

    protected function resetFormFields(): void
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->codigo_interno = '';
        $this->codigo_barras = '';
        $this->tipo_producto = 'bien';
        $this->precio_venta = '';
        $this->precio_compra = '';
        $this->stock_minimo = '0';
        $this->categoria_id = 0;
        $this->marca_id = 0;
        $this->unidad_medida_id = 1;
        $this->tipo_existencia_id = 1;
        $this->es_gravado_igv = true;
        $this->controla_lote = false;
        $this->controla_serie = false;
        $this->disponible_pos = true;
        $this->activo = true;
    }

    public function render()
    {
        $query = Producto::query()
            ->deEmpresa($this->empresaId)
            ->with(['categoria:id,nombre', 'marca:id,nombre', 'unidadMedida:id,abreviatura'])
            ->withSum('inventarioStock as stock_total', 'stock_actual');

        if ($this->buscar) {
            $query->buscar($this->buscar);
        }

        if ($this->categoriaFiltro) {
            $query->where('categoria_id', $this->categoriaFiltro);
        }

        if ($this->marcaFiltro) {
            $query->where('marca_id', $this->marcaFiltro);
        }

        if ($this->tipoFiltro) {
            $query->where('tipo_producto', $this->tipoFiltro);
        }

        $productos = $query->orderBy($this->ordenar, $this->direccion)
            ->paginate($this->perPage);

        return view('livewire.productos-table', [
            'productos' => $productos,
            'categorias' => Categoria::where('activo', true)->orderBy('nombre')->get(),
            'marcas' => Marca::orderBy('nombre')->get(),
        ]);
    }
}
