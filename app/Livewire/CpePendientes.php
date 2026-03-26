<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CpePendientes extends Component
{
    use WithPagination, TieneContextoEmpresa;

    public function mount(): void
    {
        $this->inicializarContexto();
    }

    public string $buscar = '';

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        // Rechazados por SUNAT = estado 'rechazado' o 'enviado_sunat' sin respuesta
        $query = DB::table('ventas')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->where('ventas.empresa_id', 1)
            ->whereIn('ventas.estado', ['rechazado', 'por_rectificar', 'enviado_sunat'])
            ->select('ventas.*', 'clientes.razon_social as cliente');

        if ($this->buscar) {
            $query->where(fn($q) => $q
                ->where('ventas.serie', 'LIKE', "%{$this->buscar}%")
                ->orWhere('clientes.razon_social', 'LIKE', "%{$this->buscar}%"));
        }

        $rechazados = DB::table('ventas')->where('empresa_id', $this->empresaId)->where('estado', 'rechazado')->count();
        $porRectificar = DB::table('ventas')->where('empresa_id', $this->empresaId)->where('estado', 'por_rectificar')->count();

        return view('livewire.cpe-pendientes', [
            'cpes' => $query->orderByDesc('ventas.created_at')->paginate(20),
            'errores' => $porRectificar,
            'rechazados' => $rechazados,
        ]);
    }
}
