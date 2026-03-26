<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Marca;

class MarcasTable extends Component
{
    use WithPagination;

    public string $buscar = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    public string $nombre = '';
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
        $m = Marca::findOrFail($id);
        $this->editingId = $id;
        $this->editMode = true;
        $this->nombre = $m->nombre;
        $this->activo = $m->activo;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['nombre' => 'required|string|max:100']);

        $data = [
            'nombre' => $this->nombre,
            'slug' => \Str::slug($this->nombre),
            'activo' => $this->activo,
        ];

        if ($this->editMode && $this->editingId) {
            Marca::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Marca actualizada.');
        } else {
            Marca::create($data);
            session()->flash('success', 'Marca creada.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActivo(int $id): void
    {
        $m = Marca::findOrFail($id);
        $m->update(['activo' => !$m->activo]);
    }

    public function delete(int $id): void
    {
        Marca::findOrFail($id)->delete();
        session()->flash('success', 'Marca eliminada.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->activo = true;
    }

    public function render()
    {
        $query = Marca::query();
        if ($this->buscar) {
            $query->where('nombre', 'LIKE', "%{$this->buscar}%");
        }

        return view('livewire.marcas-table', [
            'marcas' => $query->orderBy('nombre')->paginate(25),
        ]);
    }
}
