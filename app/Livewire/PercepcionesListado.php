<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class PercepcionesListado extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public function mount(): void
    {
        $this->inicializarContexto();
    }

    public string $buscar = '';
    public string $filtroEstado = '';
    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = DB::table('percepciones')
            ->leftJoin('clientes', 'percepciones.cliente_id', '=', 'clientes.id')
            ->where('percepciones.empresa_id', 1)
            ->select('percepciones.*', 'clientes.razon_social as cliente', 'clientes.numero_documento as ruc');
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('percepciones.serie', 'LIKE', "%{$this->buscar}%")->orWhere('clientes.razon_social', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('percepciones.estado', $this->filtroEstado);
        }
        $totalPercibido = DB::table('percepciones')->where('empresa_id', $this->empresaId)->whereNotIn('estado', ['anulado'])->sum('monto_percepcion');
        $cantEmitidas = DB::table('percepciones')->where('empresa_id', $this->empresaId)->whereNotIn('estado', ['anulado', 'borrador'])->count();
        return view('livewire.percepciones-listado', [
            'percepciones' => $query->orderByDesc('percepciones.created_at')->paginate(20),
            'totalPercibido' => $totalPercibido,
            'cantEmitidas' => $cantEmitidas,
        ]);
    }
}
