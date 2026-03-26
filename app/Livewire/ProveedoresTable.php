<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Proveedor;
use App\Traits\TieneContextoEmpresa;

class ProveedoresTable extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    // Form fields
    public string $tipo_documento = 'RUC';
    public string $numero_documento = '';
    public string $razon_social = '';
    public string $nombre_comercial = '';
    public string $direccion = '';
    public string $telefono = '';
    public string $email = '';
    public string $contacto_nombre = '';
    public string $contacto_telefono = '';
    public string $banco = '';
    public string $cuenta_bancaria = '';
    public string $cuenta_cci = '';
    public string $cuenta_detraccion = '';
    public int $dias_credito = 0;
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
        $p = Proveedor::findOrFail($id);
        $this->editingId = $id;
        $this->editMode = true;
        $this->tipo_documento = $p->tipo_documento;
        $this->numero_documento = $p->numero_documento;
        $this->razon_social = $p->razon_social;
        $this->nombre_comercial = $p->nombre_comercial ?? '';
        $this->direccion = $p->direccion ?? '';
        $this->telefono = $p->telefono ?? '';
        $this->email = $p->email ?? '';
        $this->contacto_nombre = $p->contacto_nombre ?? '';
        $this->contacto_telefono = $p->contacto_telefono ?? '';
        $this->banco = $p->banco ?? '';
        $this->cuenta_bancaria = $p->cuenta_bancaria ?? '';
        $this->cuenta_cci = $p->cuenta_cci ?? '';
        $this->cuenta_detraccion = $p->cuenta_detraccion ?? '';
        $this->dias_credito = $p->dias_credito ?? 0;
        $this->activo = $p->activo;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'tipo_documento' => 'required|in:RUC,DNI,CE',
            'numero_documento' => 'required|string|max:20',
            'razon_social' => 'required|string|max:255',
        ]);

        $data = [
            'empresa_id' => $this->empresaId,
            'tipo_documento' => $this->tipo_documento,
            'numero_documento' => $this->numero_documento,
            'razon_social' => $this->razon_social,
            'nombre_comercial' => $this->nombre_comercial ?: null,
            'direccion' => $this->direccion ?: null,
            'telefono' => $this->telefono ?: null,
            'email' => $this->email ?: null,
            'contacto_nombre' => $this->contacto_nombre ?: null,
            'contacto_telefono' => $this->contacto_telefono ?: null,
            'banco' => $this->banco ?: null,
            'cuenta_bancaria' => $this->cuenta_bancaria ?: null,
            'cuenta_cci' => $this->cuenta_cci ?: null,
            'cuenta_detraccion' => $this->cuenta_detraccion ?: null,
            'dias_credito' => $this->dias_credito,
            'activo' => $this->activo,
        ];

        if ($this->editMode && $this->editingId) {
            Proveedor::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Proveedor actualizado.');
        } else {
            Proveedor::create($data);
            session()->flash('success', 'Proveedor registrado.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function toggleActivo(int $id): void
    {
        $p = Proveedor::findOrFail($id);
        $p->update(['activo' => !$p->activo]);
    }

    public function delete(int $id): void
    {
        Proveedor::findOrFail($id)->delete();
        session()->flash('success', 'Proveedor eliminado.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->tipo_documento = 'RUC';
        $this->numero_documento = '';
        $this->razon_social = '';
        $this->nombre_comercial = '';
        $this->direccion = '';
        $this->telefono = '';
        $this->email = '';
        $this->contacto_nombre = '';
        $this->contacto_telefono = '';
        $this->banco = '';
        $this->cuenta_bancaria = '';
        $this->cuenta_cci = '';
        $this->cuenta_detraccion = '';
        $this->dias_credito = 0;
        $this->activo = true;
    }

    public function render()
    {
        $query = Proveedor::query()->where('empresa_id', $this->empresaId);

        if ($this->buscar) {
            $query->buscar($this->buscar);
        }

        return view('livewire.proveedores-table', [
            'proveedores' => $query->orderBy('razon_social')->paginate(25),
        ]);
    }
}
