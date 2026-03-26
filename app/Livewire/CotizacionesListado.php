<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CotizacionesListado extends Component
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
        // Tabla 'cotizaciones' existe como tabla propia
        $query = DB::table('cotizaciones')
            ->leftJoin('clientes', 'cotizaciones.cliente_id', '=', 'clientes.id')
            ->where('cotizaciones.empresa_id', 1)
            ->select('cotizaciones.*', 'clientes.razon_social as cliente');

        if ($this->buscar) {
            $query->where(fn($q) => $q
                ->where('cotizaciones.codigo', 'LIKE', "%{$this->buscar}%")
                ->orWhere('clientes.razon_social', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('cotizaciones.estado', $this->filtroEstado);
        }

        $totalCot = DB::table('cotizaciones')->where('empresa_id', $this->empresaId)->sum('total');
        $canPendientes = DB::table('cotizaciones')->where('empresa_id', $this->empresaId)->where('estado', 'pendiente')->count();

        return view('livewire.cotizaciones-listado', [
            'cotizaciones' => $query->orderByDesc('cotizaciones.created_at')->paginate(20),
            'totalCot' => $totalCot,
            'canPendientes' => $canPendientes,
        ]);
    }
}
