<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Cliente;
use App\Models\TipoCliente;
use App\Models\Zona;
use App\Traits\TieneContextoEmpresa;

class ClientesTable extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $tipoClienteFiltro = '';
    public string $zonaFiltro = '';

    public bool $showModal = false;
    public bool $editMode = false;
    public ?int $editingId = null;

    // Form
    public string $tipo_documento = 'DNI';
    public string $numero_documento = '';
    public string $razon_social = '';
    public string $nombre_comercial = '';
    public string $direccion = '';
    public string $telefono = '';
    public string $celular = '';
    public string $email = '';
    public string $limite_credito = '0';
    public int $dias_credito = 0;
    public int $tipo_cliente_id = 0;
    public int $zona_id = 0;

    public function updatingBuscar()
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
        $c = Cliente::findOrFail($id);
        $this->editingId = $id;
        $this->editMode = true;
        $this->tipo_documento = $c->tipo_documento;
        $this->numero_documento = $c->numero_documento;
        $this->razon_social = $c->razon_social;
        $this->nombre_comercial = $c->nombre_comercial ?? '';
        $this->direccion = $c->direccion ?? '';
        $this->telefono = $c->telefono ?? '';
        $this->celular = $c->celular ?? '';
        $this->email = $c->email ?? '';
        $this->limite_credito = (string) $c->limite_credito;
        $this->dias_credito = $c->dias_credito ?? 0;
        $this->tipo_cliente_id = $c->tipo_cliente_id ?? 0;
        $this->zona_id = $c->zona_id ?? 0;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'tipo_documento' => 'required|in:DNI,RUC,CE,PASAPORTE,OTROS',
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
            'celular' => $this->celular ?: null,
            'email' => $this->email ?: null,
            'limite_credito' => $this->limite_credito ?: 0,
            'dias_credito' => $this->dias_credito,
            'tipo_cliente_id' => $this->tipo_cliente_id ?: null,
            'zona_id' => $this->zona_id ?: null,
        ];

        if ($this->editMode && $this->editingId) {
            Cliente::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Cliente actualizado.');
        } else {
            Cliente::create($data);
            session()->flash('success', 'Cliente creado.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Cliente::findOrFail($id)->delete();
        session()->flash('success', 'Cliente eliminado.');
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->tipo_documento = 'DNI';
        $this->numero_documento = '';
        $this->razon_social = '';
        $this->nombre_comercial = '';
        $this->direccion = '';
        $this->telefono = '';
        $this->celular = '';
        $this->email = '';
        $this->limite_credito = '0';
        $this->dias_credito = 0;
        $this->tipo_cliente_id = 0;
        $this->zona_id = 0;
    }

    public function render()
    {
        $query = Cliente::query()
            ->where('empresa_id', $this->empresaId)
            ->with(['tipoCliente:id,nombre', 'zona:id,nombre']);

        if ($this->buscar) {
            $query->buscar($this->buscar);
        }
        if ($this->tipoClienteFiltro) {
            $query->where('tipo_cliente_id', $this->tipoClienteFiltro);
        }
        if ($this->zonaFiltro) {
            $query->where('zona_id', $this->zonaFiltro);
        }

        return view('livewire.clientes-table', [
            'clientes' => $query->orderBy('razon_social')->paginate(25),
            'tiposCliente' => TipoCliente::where('activo', true)->get(),
            'zonas' => Zona::where('activo', true)->get(),
        ]);
    }
}
