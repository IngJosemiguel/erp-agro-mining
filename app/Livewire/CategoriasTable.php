<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Categoria;

class CategoriasTable extends Component
{
    use WithPagination;

    public string $buscar = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    // Form fields
    public string $nombre = '';
    public string $descripcion = '';
    public ?int $parent_id = null;
    public int $orden = 0;
    public bool $activo = true;

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $c = Categoria::findOrFail($id);
        $this->editingId = $id;
        $this->editMode = true;
        $this->nombre = $c->nombre;
        $this->descripcion = $c->descripcion ?? '';
        $this->parent_id = $c->parent_id;
        $this->orden = $c->orden ?? 0;
        $this->activo = $c->activo;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:150',
        ]);

        $data = [
            'nombre' => $this->nombre,
            'slug' => \Str::slug($this->nombre),
            'descripcion' => $this->descripcion ?: null,
            'parent_id' => $this->parent_id ?: null,
            'orden' => $this->orden,
            'activo' => $this->activo,
        ];

        if ($this->editMode && $this->editingId) {
            Categoria::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Categoría actualizada correctamente.');
        } else {
            Categoria::create($data);
            session()->flash('success', 'Categoría creada correctamente.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActivo(int $id): void
    {
        $c = Categoria::findOrFail($id);
        $c->update(['activo' => !$c->activo]);
    }

    public function delete(int $id): void
    {
        $cat = Categoria::findOrFail($id);
        if ($cat->productos()->count() > 0) {
            session()->flash('error', 'No se puede eliminar: tiene productos asociados.');
            return;
        }
        $cat->delete();
        session()->flash('success', 'Categoría eliminada.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->parent_id = null;
        $this->orden = 0;
        $this->activo = true;
    }

    public function render()
    {
        $query = Categoria::query()->with('parent:id,nombre')->withCount('productos');

        if ($this->buscar) {
            $query->where('nombre', 'LIKE', "%{$this->buscar}%");
        }

        return view('livewire.categorias-table', [
            'categorias' => $query->orderBy('orden')->orderBy('nombre')->paginate(25),
            'categoriasPadre' => Categoria::whereNull('parent_id')->where('activo', true)->orderBy('nombre')->get(),
        ]);
    }
}
