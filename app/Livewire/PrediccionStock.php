<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Producto;
use App\Traits\TieneContextoEmpresa;

class PrediccionStock extends Component
{
    use TieneContextoEmpresa;
    public int $diasAnalisis = 30;    // ventana de análisis de consumo
    public int $diasProyeccion = 30;  // cuántos días hacia adelante proyectar

    /**
     * Calcula la predicción de agotamiento para todos los productos activos.
     * Algoritmo: promedio de salidas diarias en `diasAnalisis` días anteriores
     *            vs stock actual = días hasta agotamiento.
     */
    protected function calcularPredicciones(): \Illuminate\Support\Collection
    {
        $desde = now()->subDays($this->diasAnalisis)->toDateString();
        $hasta = now()->toDateString();

        // Salidas reales por producto en el período (kardex)
        $salidas = DB::table('kardex_movimientos')
            ->where('empresa_id', $this->empresaId)
            ->where('tipo_movimiento', 'salida')
            ->whereBetween('fecha_movimiento', [$desde, $hasta])
            ->groupBy('producto_id')
            ->select(
                'producto_id',
                DB::raw('SUM(cantidad) as total_salidas'),
                DB::raw('COUNT(DISTINCT DATE(fecha_movimiento)) as dias_con_movimiento')
            )
            ->get()
            ->keyBy('producto_id');

        // Stock actual por producto (sumando todos los almacenes)
        // inventario_stock NO tiene empresa_id → filtramos via JOIN con productos
        $stocks = DB::table('inventario_stock')
            ->join('productos', 'inventario_stock.producto_id', '=', 'productos.id')
            ->where('productos.empresa_id', $this->empresaId)
            ->whereNull('productos.deleted_at')
            ->groupBy('inventario_stock.producto_id')
            ->select(
                'inventario_stock.producto_id',
                DB::raw('SUM(inventario_stock.stock_actual) as stock_total')
            )
            ->get()
            ->keyBy('producto_id');

        // Productos activos
        $productos = Producto::where('empresa_id', $this->empresaId)
            ->where('activo', true)
            ->where('tipo_producto', '!=', 'servicio') // servicios no tienen stock
            ->select('id', 'nombre', 'codigo_interno', 'stock_minimo', 'unidad_medida_id')
            ->with('unidadMedida:id,abreviatura')
            ->get();

        $predicciones = $productos->map(function ($prod) use ($salidas, $stocks) {
            $salidaData = $salidas->get($prod->id);
            $stockData = $stocks->get($prod->id);

            $stockActual = (float) ($stockData?->stock_total ?? 0);
            $totalSalidas = (float) ($salidaData?->total_salidas ?? 0);
            $diasConMovimiento = (int) ($salidaData?->dias_con_movimiento ?? 0);

            // Consumo diario promedio (solo días con movimiento para no distorsionar)
            $consumoDiario = $diasConMovimiento > 0
                ? $totalSalidas / $diasConMovimiento
                : 0;

            // Días hasta agotamiento
            $diasHastaAgotamiento = $consumoDiario > 0
                ? (int) floor($stockActual / $consumoDiario)
                : null;  // null = sin movimiento reciente

            // Clasificación de urgencia
            $urgencia = match (true) {
                $stockActual <= 0 => 'agotado',
                $diasHastaAgotamiento !== null && $diasHastaAgotamiento <= 7 => 'critico',
                $diasHastaAgotamiento !== null && $diasHastaAgotamiento <= 15 => 'advertencia',
                $diasHastaAgotamiento !== null && $diasHastaAgotamiento <= 30 => 'atencion',
                default => 'ok',
            };

            return [
                'id' => $prod->id,
                'nombre' => $prod->nombre,
                'codigo' => $prod->codigo_interno,
                'unidad' => $prod->unidadMedida?->abreviatura ?? 'UN',
                'stock_actual' => $stockActual,
                'stock_minimo' => (float) $prod->stock_minimo,
                'consumo_diario' => round($consumoDiario, 3),
                'dias_hasta_agotamiento' => $diasHastaAgotamiento,
                'fecha_agotamiento' => $diasHastaAgotamiento !== null
                    ? now()->addDays($diasHastaAgotamiento)->format('d/m/Y')
                    : null,
                'urgencia' => $urgencia,
                'tiene_movimiento' => $consumoDiario > 0,
            ];
        })
            // Solo mostrar los que tienen movimiento o stock crítico
            ->filter(fn($p) => $p['urgencia'] !== 'ok' || $p['stock_actual'] <= $p['stock_minimo'])
            ->sortByDesc(fn($p) => match ($p['urgencia']) {
                'agotado' => 4,
                'critico' => 3,
                'advertencia' => 2,
                'atencion' => 1,
                default => 0,
            })
            ->values();

        return $predicciones;
    }

    public function render()
    {
        $predicciones = $this->calcularPredicciones();

        $resumen = [
            'agotados' => $predicciones->where('urgencia', 'agotado')->count(),
            'criticos' => $predicciones->where('urgencia', 'critico')->count(),
            'advertencia' => $predicciones->where('urgencia', 'advertencia')->count(),
            'atencion' => $predicciones->where('urgencia', 'atencion')->count(),
        ];

        return view('livewire.prediccion-stock', compact('predicciones', 'resumen'))
            ->layout('layouts.app', [
                'title' => 'Predicción de Stock IA',
                'breadcrumb' => [['label' => 'Dashboard', 'route' => 'dashboard'], ['label' => 'Predicción de Stock IA']],
            ]);
    }
}
