<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InventarioStock;
use App\Models\Almacen;

class ReporteInventario extends Component
{
    use WithPagination;

    public string $buscar = '';
    public string $filtroAlmacen = '';
    public bool $soloConStock = false;
    public bool $soloStockBajo = false;

    public function updatingBuscar(): void
    {
        $this->resetPage();
    }
    public function updatingFiltroAlmacen(): void
    {
        $this->resetPage();
    }
    public function updatingSoloConStock(): void
    {
        $this->resetPage();
    }
    public function updatingSoloStockBajo(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = InventarioStock::query()
            ->with([
                'producto:id,codigo_interno,nombre,stock_minimo,stock_maximo,precio_venta,unidad_medida_id,categoria_id',
                'producto.unidadMedida:id,abreviatura',
                'producto.categoria:id,nombre',
                'almacen:id,nombre',
            ]);

        if ($this->buscar) {
            $query->whereHas('producto', function ($q) {
                $q->where('nombre', 'LIKE', "%{$this->buscar}%")
                    ->orWhere('codigo_interno', 'LIKE', "%{$this->buscar}%");
            });
        }

        if ($this->filtroAlmacen) {
            $query->where('almacen_id', $this->filtroAlmacen);
        }

        if ($this->soloConStock) {
            $query->where('stock_actual', '>', 0);
        }

        if ($this->soloStockBajo) {
            $query->whereHas('producto', function ($q) {
                $q->whereColumn('inventario_stock.stock_actual', '<=', 'productos.stock_minimo');
            });
        }

        $stock = $query->orderBy('stock_actual', 'asc')->paginate(30);

        // Estadísticas rápidas
        $totalItems = InventarioStock::count();
        $totalValor = InventarioStock::selectRaw('SUM(stock_actual * costo_promedio) as total')->value('total') ?? 0;
        $sinStock = InventarioStock::where('stock_actual', '<=', 0)->count();

        return view('livewire.reporte-inventario', [
            'inventario' => $stock,
            'almacenes' => Almacen::where('activo', true)->orderBy('nombre')->get(),
            'totalItems' => $totalItems,
            'totalValor' => $totalValor,
            'sinStock' => $sinStock,
        ]);
    }
}
