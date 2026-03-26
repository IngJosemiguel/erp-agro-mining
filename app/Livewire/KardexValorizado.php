<?php
namespace App\Livewire;
use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class KardexValorizado extends Component
{
    use WithPagination, TieneContextoEmpresa;
    public string $buscar = '';
    public string $filtroAlmacen = '';
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
        $query = DB::table('kardex_movimientos')
            ->leftJoin('productos', 'kardex_movimientos.producto_id', '=', 'productos.id')
            ->leftJoin('almacenes', 'kardex_movimientos.almacen_id', '=', 'almacenes.id')
            ->where('kardex_movimientos.empresa_id', 1)
            ->whereBetween('kardex_movimientos.fecha_movimiento', [$this->fechaDesde, $this->fechaHasta])
            ->select('kardex_movimientos.*', 'productos.nombre as producto', 'productos.codigo_interno as codigo_prod', 'almacenes.nombre as almacen');
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('productos.nombre', 'LIKE', "%{$this->buscar}%")->orWhere('productos.codigo_interno', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroAlmacen) {
            $query->where('kardex_movimientos.almacen_id', $this->filtroAlmacen);
        }
        $totalIngresos = DB::table('kardex_movimientos')->where('empresa_id', $this->empresaId)->whereBetween('fecha_movimiento', [$this->fechaDesde, $this->fechaHasta])->where('tipo_movimiento', 'entrada')->sum('costo_total');
        $totalSalidas = DB::table('kardex_movimientos')->where('empresa_id', $this->empresaId)->whereBetween('fecha_movimiento', [$this->fechaDesde, $this->fechaHasta])->where('tipo_movimiento', 'salida')->sum('costo_total');
        return view('livewire.kardex-valorizado', [
            'movimientos' => $query->orderByDesc('kardex_movimientos.fecha_movimiento')->paginate(25),
            'totalIngresos' => $totalIngresos,
            'totalSalidas' => $totalSalidas,
            'almacenes' => DB::table('almacenes')->where('activo', true)->select('id', 'nombre')->get(),
        ]);
    }
}
