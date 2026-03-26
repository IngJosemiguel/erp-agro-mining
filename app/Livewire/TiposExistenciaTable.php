<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TipoExistencia;

class TiposExistenciaTable extends Component
{
    use WithPagination;

    public string $buscar = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    public string $codigo_sunat = '';
    public string $descripcion = '';
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
        $t = TipoExistencia::findOrFail($id);
        $this->editingId = $id;
        $this->editMode = true;
        $this->codigo_sunat = $t->codigo_sunat;
        $this->descripcion = $t->descripcion;
        $this->activo = $t->activo;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'codigo_sunat' => 'required|string|max:10',
            'descripcion' => 'required|string|max:200',
        ]);

        $data = [
            'codigo_sunat' => $this->codigo_sunat,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ];

        if ($this->editMode && $this->editingId) {
            TipoExistencia::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Tipo de existencia actualizado.');
        } else {
            TipoExistencia::create($data);
            session()->flash('success', 'Tipo de existencia creado.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActivo(int $id): void
    {
        $t = TipoExistencia::findOrFail($id);
        $t->update(['activo' => !$t->activo]);
    }

    public function delete(int $id): void
    {
        TipoExistencia::findOrFail($id)->delete();
        session()->flash('success', 'Tipo de existencia eliminado.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->codigo_sunat = '';
        $this->descripcion = '';
        $this->activo = true;
    }

    public function render()
    {
        $query = TipoExistencia::query();
        if ($this->buscar) {
            $query->where(function ($q) {
                $q->where('descripcion', 'LIKE', "%{$this->buscar}%")
                    ->orWhere('codigo_sunat', 'LIKE', "%{$this->buscar}%");
            });
        }

        return view('livewire.tipos-existencia-table', [
            'tipos' => $query->orderBy('codigo_sunat')->paginate(25),
        ]);
    }
}
