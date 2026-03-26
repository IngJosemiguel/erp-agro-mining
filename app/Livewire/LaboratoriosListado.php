<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Producto;
use App\Models\Laboratorio;

class LaboratoriosListado extends Component
{
    use WithPagination;
    public string $buscar = '';
    public bool $showModal = false;
    public ?int $editId = null;
    public string $nombre = '';
    public string $ruc = '';
    public string $pais = 'Perú';
    public bool $activo = true;
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function openModal(int $id = 0): void
    {
        $this->reset(['nombre', 'ruc', 'pais', 'activo']);
        $this->activo = true;
        $this->pais = 'Perú';
        $this->editId = $id ?: null;
        if ($id) {
            $l = Laboratorio::find($id);
            if ($l) {
                $this->nombre = $l->nombre;
                $this->ruc = $l->ruc ?? '';
                $this->pais = $l->pais ?? 'Perú';
                $this->activo = (bool) $l->activo;
            }
        }
        $this->showModal = true;
    }

    public function guardar(): void
    {
        $this->validate(['nombre' => 'required|string|min:2']);
        $data = ['nombre' => $this->nombre, 'ruc' => $this->ruc ?: null, 'pais' => $this->pais, 'activo' => $this->activo];
        $this->editId ? Laboratorio::find($this->editId)->update($data) : Laboratorio::create($data);
        session()->flash('success', $this->editId ? 'Laboratorio actualizado.' : 'Laboratorio creado.');
        $this->showModal = false;
    }

    public function eliminar(int $id): void
    {
        Laboratorio::find($id)?->delete();
        session()->flash('success', 'Laboratorio eliminado.');
    }

    public function render()
    {
        $query = Laboratorio::query();
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('nombre', 'LIKE', "%{$this->buscar}%")->orWhere('ruc', 'LIKE', "%{$this->buscar}%"));
        }
        $labs = $query->where('activo', true)->orderBy('nombre')->paginate(20);
        // Agregar count de productos manualmente
        $labs->getCollection()->transform(function ($l) {
            $l->productos_count = \Illuminate\Support\Facades\DB::table('productos')->where('laboratorio_id', $l->id)->where('activo', true)->count();
            return $l;
        });
        return view('livewire.laboratorios-listado', ['laboratorios' => $labs]);
    }
}
