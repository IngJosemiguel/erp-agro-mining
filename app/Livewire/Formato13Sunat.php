<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class Formato13Sunat extends Component
{
    use WithPagination;
    public string $mesAnio = '';
    public string $filtroAlmacen = '';
    public function mount(): void
    {
        $this->mesAnio = now()->format('Y-m');
    }

    public function render()
    {
        [$anio, $mes] = explode('-', $this->mesAnio);
        $inicio = "{$anio}-{$mes}-01";
        $fin = date('Y-m-t', strtotime($inicio));

        $query = DB::table('inventario_stock')
            ->leftJoin('productos', 'inventario_stock.producto_id', '=', 'productos.id')
            ->leftJoin('almacenes', 'inventario_stock.almacen_id', '=', 'almacenes.id')
            ->leftJoin('unidades_medida', DB::raw('1'), '=', DB::raw('1')) // sin FK directa
            ->where('inventario_stock.stock_actual', '>', 0)
            ->select('inventario_stock.*', 'productos.nombre as producto', 'productos.codigo_interno', 'productos.codigo_sunat', 'almacenes.nombre as almacen');
        if ($this->filtroAlmacen) {
            $query->where('inventario_stock.almacen_id', $this->filtroAlmacen);
        }

        $totalRegistros = DB::table('inventario_stock')->where('stock_actual', '>', 0)->count();
        $totalValorizado = DB::table('inventario_stock')->selectRaw('SUM(stock_actual * costo_promedio) as val')->value('val') ?? 0;

        return view('livewire.formato-13-sunat', [
            'stocks' => $query->orderBy('almacenes.nombre')->orderBy('productos.nombre')->paginate(30),
            'totalRegistros' => $totalRegistros,
            'totalValorizado' => $totalValorizado,
            'almacenes' => DB::table('almacenes')->where('activo', true)->select('id', 'nombre')->get(),
            'mesActual' => \Carbon\Carbon::parse($inicio)->isoFormat('MMMM YYYY'),
        ]);
    }
}
