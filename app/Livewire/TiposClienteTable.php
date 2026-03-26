<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TipoCliente;
use App\Traits\TieneContextoEmpresa;

class TiposClienteTable extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    public string $nombre = '';
    public string $descuento_default = '0';
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
        $t = TipoCliente::findOrFail($id);
        $this->editingId = $id;
        $this->editMode = true;
        $this->nombre = $t->nombre;
        $this->descuento_default = (string) $t->descuento_default;
        $this->activo = $t->activo;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:100',
            'descuento_default' => 'required|numeric|min:0|max:100',
        ]);

        $data = [
            'empresa_id' => $this->empresaId,
            'nombre' => $this->nombre,
            'descuento_default' => $this->descuento_default,
            'activo' => $this->activo,
        ];

        if ($this->editMode && $this->editingId) {
            TipoCliente::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Tipo de cliente actualizado.');
        } else {
            TipoCliente::create($data);
            session()->flash('success', 'Tipo de cliente creado.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActivo(int $id): void
    {
        $t = TipoCliente::findOrFail($id);
        $t->update(['activo' => !$t->activo]);
    }

    public function delete(int $id): void
    {
        TipoCliente::findOrFail($id)->delete();
        session()->flash('success', 'Tipo de cliente eliminado.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->descuento_default = '0';
        $this->activo = true;
    }

    public function render()
    {
        $query = TipoCliente::query()->where('empresa_id', $this->empresaId);
        if ($this->buscar) {
            $query->where('nombre', 'LIKE', "%{$this->buscar}%");
        }

        return view('livewire.tipos-cliente-table', [
            'tipos' => $query->orderBy('nombre')->paginate(25),
        ]);
    }
}
