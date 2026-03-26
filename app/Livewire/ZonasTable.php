<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Zona;
use App\Traits\TieneContextoEmpresa;

class ZonasTable extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    public string $nombre = '';
    public string $descripcion = '';
    public bool $activo = true;

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function mount(): void
    {
        $this->inicializarContexto();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $z = Zona::findOrFail($id);
        $this->editingId = $id;
        $this->editMode = true;
        $this->nombre = $z->nombre;
        $this->descripcion = $z->descripcion ?? '';
        $this->activo = $z->activo;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate(['nombre' => 'required|string|max:100']);

        $data = [
            'empresa_id' => $this->empresaId,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion ?: null,
            'activo' => $this->activo,
        ];

        if ($this->editMode && $this->editingId) {
            Zona::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Zona actualizada.');
        } else {
            Zona::create($data);
            session()->flash('success', 'Zona creada.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActivo(int $id): void
    {
        $z = Zona::findOrFail($id);
        $z->update(['activo' => !$z->activo]);
    }

    public function delete(int $id): void
    {
        Zona::findOrFail($id)->delete();
        session()->flash('success', 'Zona eliminada.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->descripcion = '';
        $this->activo = true;
    }

    public function render()
    {
        $query = Zona::query()->where('empresa_id', $this->empresaId);
        if ($this->buscar) {
            $query->where('nombre', 'LIKE', "%{$this->buscar}%");
        }

        return view('livewire.zonas-table', [
            'zonas' => $query->orderBy('nombre')->paginate(25),
        ]);
    }
}
