<?php
namespace App\Livewire;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class SeriesProducto extends Component
{
    use WithPagination;
    public string $buscar = '';
    public string $filtroEstado = '';

    public function render()
    {
        $query = DB::table('series')
            ->leftJoin('productos', 'series.producto_id', '=', 'productos.id')
            ->select('series.*', 'productos.nombre as producto', 'productos.codigo_interno as codigo_prod');
        if ($this->buscar) {
            $query->where(fn($q) => $q->where('series.numero_serie', 'LIKE', "%{$this->buscar}%")->orWhere('productos.nombre', 'LIKE', "%{$this->buscar}%"));
        }
        if ($this->filtroEstado) {
            $query->where('series.estado', $this->filtroEstado);
        }
        $disponibles = DB::table('series')->where('estado', 'disponible')->count();
        $vendidas = DB::table('series')->where('estado', 'vendido')->count();
        return view('livewire.series-producto', [
            'series' => $query->orderByDesc('series.created_at')->paginate(25),
            'disponibles' => $disponibles,
            'vendidas' => $vendidas,
        ]);
    }
}
