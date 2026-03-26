<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class LiquidacionesListado extends Component
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
        // Liquidaciones de compra = compras con tipo_documento '09' o similar
        $query = DB::table('compras')
            ->leftJoin('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
            ->where('compras.empresa_id', 1)
            ->where('compras.tipo_documento', 'LC') // Liquidación de Compra
            ->select('compras.*', 'proveedores.razon_social as proveedor');
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('compras.serie', 'LIKE', "%{$this->buscar}%")->orWhere('proveedores.razon_social', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('compras.estado', $this->filtroEstado);
        }
        $totalLC = DB::table('compras')->where('empresa_id', $this->empresaId)->where('tipo_documento', 'LC')->sum('total');
        $cantEmitidas = DB::table('compras')->where('empresa_id', $this->empresaId)->where('tipo_documento', 'LC')->count();
        return view('livewire.liquidaciones-listado', [
            'liquidaciones' => $query->orderByDesc('compras.created_at')->paginate(20),
            'totalLC' => $totalLC,
            'cantEmitidas' => $cantEmitidas,
        ]);
    }
}
