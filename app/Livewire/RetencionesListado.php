<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class RetencionesListado extends Component
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
        $query = DB::table('retenciones')
            ->leftJoin('proveedores', 'retenciones.proveedor_id', '=', 'proveedores.id')
            ->where('retenciones.empresa_id', 1)
            ->select('retenciones.*', 'proveedores.razon_social as proveedor', 'proveedores.numero_documento as ruc');
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('retenciones.serie', 'LIKE', "%{$this->buscar}%")->orWhere('proveedores.razon_social', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('retenciones.estado', $this->filtroEstado);
        }
        $totalRetenido = DB::table('retenciones')->where('empresa_id', $this->empresaId)->whereNotIn('estado', ['anulado'])->sum('monto_retencion');
        $cantEmitidas = DB::table('retenciones')->where('empresa_id', $this->empresaId)->whereNotIn('estado', ['anulado', 'borrador'])->count();
        return view('livewire.retenciones-listado', [
            'retenciones' => $query->orderByDesc('retenciones.created_at')->paginate(20),
            'totalRetenido' => $totalRetenido,
            'cantEmitidas' => $cantEmitidas,
        ]);
    }
}
