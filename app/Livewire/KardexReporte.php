<?php

namespace App\Livewire;

use Livewire\Component;
use App\Traits\TieneContextoEmpresa;
use App\Models\KardexMovimiento;
use App\Models\Producto;
use App\Models\Almacen;

class KardexReporte extends Component
{
    use TieneContextoEmpresa;

    public string $producto_id = '';
    public string $almacen_id = '';
    public string $fechaDesde = '';
    public string $fechaHasta = '';
    public bool $consultado = false;

    // Búsqueda
    public string $buscarProducto = '';
    public array $productosEncontrados = [];
    public string $productoSeleccionadoTexto = '';

    public function mount(): void
    {
        $this->inicializarContexto();
        $this->fechaDesde = now()->startOfMonth()->toDateString();
        $this->fechaHasta = now()->toDateString();
    }

    public function updatedBuscarProducto(): void
    {
        if (strlen($this->buscarProducto) >= 2) {
            $this->productosEncontrados = Producto::where('empresa_id', $this->empresaId)
                ->where('activo', true)
                ->buscar($this->buscarProducto)
                ->select('id', 'codigo_interno', 'nombre')
                ->limit(10)
                ->get()
                ->toArray();
        } else {
            $this->productosEncontrados = [];
        }
    }

    public function seleccionarProducto(int $id): void
    {
        $producto = Producto::find($id);
        if ($producto) {
            $this->producto_id = (string) $producto->id;
            $this->productoSeleccionadoTexto = "[{$producto->codigo_interno}] {$producto->nombre}";
            $this->buscarProducto = '';
            $this->productosEncontrados = [];
        }
    }

    public function consultar(): void
    {
        $this->validate([
            'producto_id' => 'required|exists:productos,id',
            'almacen_id' => 'required|exists:almacenes,id',
            'fechaDesde' => 'required|date',
            'fechaHasta' => 'required|date|after_or_equal:fechaDesde',
        ]);

        $this->consultado = true;
    }

    public function limpiar(): void
    {
        $this->producto_id = '';
        $this->almacen_id = '';
        $this->fechaDesde = now()->startOfMonth()->toDateString();
        $this->fechaHasta = now()->toDateString();
        $this->consultado = false;
        $this->productoSeleccionadoTexto = '';
    }

    public function render()
    {
        $movimientos = collect();
        $resumen = ['total_ingresos' => 0, 'total_salidas' => 0, 'saldo_final' => 0, 'valor_final' => 0];

        if ($this->consultado && $this->producto_id && $this->almacen_id) {
            $movimientos = KardexMovimiento::kardexValorizado(
                (int) $this->producto_id,
                (int) $this->almacen_id,
                $this->fechaDesde,
                $this->fechaHasta,
            )->get();

            $resumen = [
                'total_ingresos' => $movimientos->where('tipo_movimiento', 'ingreso')->sum('cantidad'),
                'total_salidas' => $movimientos->where('tipo_movimiento', 'salida')->sum('cantidad'),
                'saldo_final' => $movimientos->last()?->saldo_cantidad ?? 0,
                'valor_final' => $movimientos->last()?->saldo_valorizado ?? 0,
            ];
        }

        return view('livewire.kardex-reporte', [
            'movimientos' => $movimientos,
            'resumen' => $resumen,
            'almacenes' => Almacen::where('activo', true)->orderBy('nombre')->get(),
        ]);
    }
}
