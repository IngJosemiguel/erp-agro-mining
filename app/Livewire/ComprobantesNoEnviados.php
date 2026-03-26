<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ComprobantesNoEnviados extends Component
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
        // CPE no enviados/aceptados = estado NOT IN (aceptado, anulado)
        $query = DB::table('ventas')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
            ->where('ventas.empresa_id', 1)
            ->whereNotIn('ventas.estado', ['aceptado', 'anulado'])
            ->whereNotIn('ventas.tipo_documento', ['07', '08']) // Excluir NC/ND
            ->select('ventas.*', 'clientes.razon_social as cliente');

        if ($this->buscar) {
            $query->where(fn($q) => $q
                ->where('ventas.serie', 'LIKE', "%{$this->buscar}%")
                ->orWhere('clientes.razon_social', 'LIKE', "%{$this->buscar}%"));
        }

        $total = DB::table('ventas')->where('empresa_id', $this->empresaId)
            ->whereNotIn('estado', ['aceptado', 'anulado'])->count();

        return view('livewire.comprobantes-no-enviados', [
            'comprobantes' => $query->orderByDesc('ventas.created_at')->paginate(20),
            'totalPendientes' => $total,
        ]);
    }
}
