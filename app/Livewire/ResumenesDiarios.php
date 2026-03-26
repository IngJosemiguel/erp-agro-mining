<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ResumenesDiarios extends Component
{
    use WithPagination, TieneContextoEmpresa;
    public string $fechaDesde = '';
    public string $fechaHasta = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fechaDesde = now()->startOfMonth()->toDateString();
        $this->fechaHasta = now()->toDateString();
    }

    public function render()
    {
        // Agrupa ventas por fecha de emisión — usa columna 'estado' real
        $resumenes = DB::table('ventas')
            ->where('empresa_id', $this->empresaId)
            ->whereBetween('fecha_emision', [$this->fechaDesde, $this->fechaHasta])
            ->select(
                DB::raw('fecha_emision as fecha'),
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as total'),
                DB::raw('SUM(igv) as total_igv'),
                DB::raw('estado')
            )
            ->groupBy('fecha_emision', 'estado')
            ->orderByDesc('fecha_emision')
            ->paginate(30);

        $totalesGlobales = DB::table('ventas')
            ->where('empresa_id', $this->empresaId)
            ->whereBetween('fecha_emision', [$this->fechaDesde, $this->fechaHasta])
            ->selectRaw('COUNT(*) as total_comprobantes, SUM(total) as suma_total')
            ->first();

        return view('livewire.resumenes-diarios', [
            'resumenes' => $resumenes,
            'totalesGlobales' => $totalesGlobales,
        ]);
    }
}
