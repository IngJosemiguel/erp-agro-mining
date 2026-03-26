<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivoFijo;
use App\Traits\TieneContextoEmpresa;

class ActivosFijos extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public string $buscar = '';
    public string $filtroEstado = '';
    public string $filtroCategoria = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $nombre = '';
    public string $categoria = '';
    public string $marca = '';
    public string $modelo = '';
    public string $numero_serie = '';
    public string $fecha_adquisicion = '';
    public string $valor_adquisicion = '0';
    public string $depreciacion_anual = '10';
    public string $ubicacion = '';
    public string $responsable = '';
    public string $observaciones = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fecha_adquisicion = now()->toDateString();
    }
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function openModal(?int $id = null): void
    {
        $this->resetForm();
        if ($id) {
            $a = ActivoFijo::find($id);
            if ($a) {
                $this->editingId = $a->id;
                $this->nombre = $a->nombre;
                $this->categoria = $a->categoria ?? '';
                $this->marca = $a->marca ?? '';
                $this->modelo = $a->modelo ?? '';
                $this->numero_serie = $a->numero_serie ?? '';
                $this->fecha_adquisicion = $a->fecha_adquisicion->toDateString();
                $this->valor_adquisicion = (string) $a->valor_adquisicion;
                $this->depreciacion_anual = (string) $a->depreciacion_anual;
                $this->ubicacion = $a->ubicacion ?? '';
                $this->responsable = $a->responsable ?? '';
                $this->observaciones = $a->observaciones ?? '';
            }
        }
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'nombre' => 'required|string|max:255',
            'fecha_adquisicion' => 'required|date',
            'valor_adquisicion' => 'required|numeric|min:0.01',
        ]);

        $data = [
            'empresa_id' => $this->empresaId,
            'sucursal_id' => $this->sucursalId,
            'user_id' => auth()->id() ?? 1,
            'nombre' => $this->nombre,
            'categoria' => $this->categoria ?: null,
            'marca' => $this->marca ?: null,
            'modelo' => $this->modelo ?: null,
            'numero_serie' => $this->numero_serie ?: null,
            'fecha_adquisicion' => $this->fecha_adquisicion,
            'valor_adquisicion' => (float) $this->valor_adquisicion,
            'depreciacion_anual' => (float) $this->depreciacion_anual,
            'ubicacion' => $this->ubicacion ?: null,
            'responsable' => $this->responsable ?: null,
            'observaciones' => $this->observaciones ?: null,
        ];

        if ($this->editingId) {
            ActivoFijo::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Activo fijo actualizado.');
        } else {
            $data['codigo'] = ActivoFijo::generarCodigo();
            ActivoFijo::create($data);
            session()->flash('success', 'Activo fijo registrado.');
        }
        $this->showModal = false;
    }

    public function cambiarEstado(int $id, string $estado): void
    {
        $a = ActivoFijo::find($id);
        if ($a) {
            $a->update(['estado' => $estado]);
            session()->flash('success', "Activo marcado como " . str_replace('_', ' ', $estado) . ".");
        }
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->nombre = '';
        $this->categoria = '';
        $this->marca = '';
        $this->modelo = '';
        $this->numero_serie = '';
        $this->fecha_adquisicion = now()->toDateString();
        $this->valor_adquisicion = '0';
        $this->depreciacion_anual = '10';
        $this->ubicacion = '';
        $this->responsable = '';
        $this->observaciones = '';
    }

    public function render()
    {
        $query = ActivoFijo::where('empresa_id', $this->empresaId);
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('nombre', 'LIKE', "%{$this->buscar}%")->orWhere('codigo', 'LIKE', "%{$this->buscar}%")->orWhere('numero_serie', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }
        if ($this->filtroCategoria) {
            $query->where('categoria', $this->filtroCategoria);
        }

        $totalValor = ActivoFijo::where('empresa_id', $this->empresaId)->where('estado', 'activo')->sum('valor_adquisicion');
        $cantActivos = ActivoFijo::where('empresa_id', $this->empresaId)->where('estado', 'activo')->count();

        return view('livewire.activos-fijos', [
            'activos' => $query->orderByDesc('created_at')->paginate(20),
            'categoriasAF' => ActivoFijo::$categorias,
            'totalValor' => $totalValor,
            'cantActivos' => $cantActivos,
        ]);
    }
}
