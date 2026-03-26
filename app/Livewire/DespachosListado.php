<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class DespachosListado extends Component
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
        // Despachos no tienen cliente_id directamente — join via venta
        $query = DB::table('despachos')
            ->leftJoin('ventas', 'despachos.venta_id', '=', 'ventas.id')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->where('despachos.empresa_id', 1)
            ->select(
                'despachos.*',
                'clientes.razon_social as cliente',
                'ventas.serie as venta_serie',
                'ventas.correlativo as venta_correlativo'
            );

        if ($this->buscar) {
            $query->where(fn($q) => $q
                ->where('despachos.codigo', 'LIKE', "%{$this->buscar}%")
                ->orWhere('clientes.razon_social', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('despachos.estado', $this->filtroEstado);
        }

        $totalDespachos = DB::table('despachos')->where('empresa_id', $this->empresaId)->count();
        $enTransito = DB::table('despachos')->where('empresa_id', $this->empresaId)->where('estado', 'pendiente')->count();

        return view('livewire.despachos-listado', [
            'despachos' => $query->orderByDesc('despachos.created_at')->paginate(20),
            'totalDespachos' => $totalDespachos,
            'enTransito' => $enTransito,
        ]);
    }
}
