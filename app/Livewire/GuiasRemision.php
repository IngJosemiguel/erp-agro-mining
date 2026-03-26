<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class GuiasRemision extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public function mount(): void
    {
        $this->inicializarContexto();
    }

    public string $buscar = '';
    public string $filtroEstado = '';
    public string $filtroTipo = '';
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = DB::table('guias_remision')
            ->where('empresa_id', $this->empresaId)
            ->select('guias_remision.*');
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('serie', 'LIKE', "%{$this->buscar}%")->orWhere('correlativo', 'LIKE', "%{$this->buscar}%")->orWhere('destinatario_razon_social', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('estado', $this->filtroEstado);
        }
        if ($this->filtroTipo) {
            $query->where('tipo_guia', $this->filtroTipo);
        }
        $totalGuias = DB::table('guias_remision')->where('empresa_id', $this->empresaId)->count();
        $aceptadas = DB::table('guias_remision')->where('empresa_id', $this->empresaId)->where('estado', 'aceptada')->count();
        $pendientes = DB::table('guias_remision')->where('empresa_id', $this->empresaId)->whereIn('estado', ['borrador', 'emitida'])->count();
        return view('livewire.guias-remision', [
            'guias' => $query->orderByDesc('created_at')->paginate(20),
            'totalGuias' => $totalGuias,
            'aceptadas' => $aceptadas,
            'pendientes' => $pendientes,
        ]);
    }
}
