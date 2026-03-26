<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use App\Models\Almacen;

class UbicacionesAlmacen extends Component
{
    use WithPagination;
    public string $buscar = '';
    public string $filtroAlmacen = '';
    public bool $showModal = false;
    public ?int $editId = null;
    public string $almacen_id = '';
    public string $codigo = '';
    public string $descripcion = '';
    public bool $activo = true;
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function openModal(int $id = 0): void
    {
        $this->reset(['almacen_id', 'codigo', 'descripcion', 'activo']);
        $this->activo = true;
        $this->editId = $id ?: null;
        if ($id && $u = DB::table('ubicaciones_almacen')->find($id)) {
            $this->almacen_id = (string) $u->almacen_id;
            $this->codigo = $u->codigo;
            $this->descripcion = $u->descripcion ?? '';
            $this->activo = (bool) $u->activo;
        }
        $this->showModal = true;
    }

    public function guardar(): void
    {
        $this->validate(['almacen_id' => 'required', 'codigo' => 'required|string|min:1']);
        $data = ['almacen_id' => (int) $this->almacen_id, 'codigo' => strtoupper($this->codigo), 'descripcion' => $this->descripcion ?: null, 'activo' => $this->activo, 'updated_at' => now()];
        if ($this->editId) {
            DB::table('ubicaciones_almacen')->where('id', $this->editId)->update($data);
        } else {
            DB::table('ubicaciones_almacen')->insert(array_merge($data, ['created_at' => now()]));
        }
        session()->flash('success', 'Ubicación guardada.');
        $this->showModal = false;
    }

    public function eliminar(int $id): void
    {
        DB::table('ubicaciones_almacen')->where('id', $id)->delete();
        session()->flash('success', 'Ubicación eliminada.');
    }

    public function render()
    {
        $query = DB::table('ubicaciones_almacen')->leftJoin('almacenes', 'ubicaciones_almacen.almacen_id', '=', 'almacenes.id')->select('ubicaciones_almacen.*', 'almacenes.nombre as almacen');
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('ubicaciones_almacen.codigo', 'LIKE', "%{$this->buscar}%")->orWhere('ubicaciones_almacen.descripcion', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroAlmacen) {
            $query->where('ubicaciones_almacen.almacen_id', $this->filtroAlmacen);
        }
        return view('livewire.ubicaciones-almacen', [
            'ubicaciones' => $query->orderBy('almacenes.nombre')->orderBy('ubicaciones_almacen.codigo')->paginate(25),
            'almacenes' => Almacen::where('activo', true)->orderBy('nombre')->get(),
            'total' => DB::table('ubicaciones_almacen')->where('activo', true)->count(),
        ]);
    }
}
