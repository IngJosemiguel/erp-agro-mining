<?php

namespace App\Services;

use App\Models\InventarioStock;
use App\Models\InventarioLote;
use App\Models\KardexMovimiento;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;

class InventarioService
{
    /**
     * Registrar un INGRESO de mercadería (compra, devolución, ajuste positivo).
     * Actualiza stock, recalcula costo promedio y registra en Kardex.
     */
    public function registrarIngreso(array $params): KardexMovimiento
    {
        return DB::transaction(function () use ($params) {
            $inventario = InventarioStock::lockForUpdate()
                ->firstOrCreate(
                    [
                        'producto_id' => $params['producto_id'],
                        'almacen_id' => $params['almacen_id'],
                    ],
                    [
                        'stock_actual' => 0,
                        'stock_comprometido' => 0,
                        'costo_promedio' => 0,
                        'ultimo_costo' => 0,
                    ]
                );

            // Recalcular costo promedio ponderado
            $nuevoCostoPromedio = $inventario->recalcularCostoPromedio(
                $params['cantidad'],
                $params['costo_unitario']
            );

            // Actualizar stock
            $inventario->stock_actual += $params['cantidad'];
            $inventario->costo_promedio = $nuevoCostoPromedio;
            $inventario->ultimo_costo = $params['costo_unitario'];
            $inventario->save();

            // Si tiene control de lotes, actualizar stock por lote
            if (isset($params['lote_id']) && $params['lote_id']) {
                $inventarioLote = InventarioLote::firstOrCreate(
                    [
                        'producto_id' => $params['producto_id'],
                        'almacen_id' => $params['almacen_id'],
                        'lote_id' => $params['lote_id'],
                    ],
                    ['stock_actual' => 0, 'costo_unitario' => 0]
                );

                $inventarioLote->stock_actual += $params['cantidad'];
                $inventarioLote->costo_unitario = $params['costo_unitario'];
                $inventarioLote->save();
            }

            // Registrar movimiento en Kardex
            return KardexMovimiento::create([
                'empresa_id' => $params['empresa_id'],
                'producto_id' => $params['producto_id'],
                'almacen_id' => $params['almacen_id'],
                'lote_id' => $params['lote_id'] ?? null,
                'serie_id' => $params['serie_id'] ?? null,
                'user_id' => $params['user_id'] ?? auth()->id(),
                'tipo_movimiento' => 'ingreso',
                'tipo_operacion' => $params['tipo_operacion'],
                'motivo' => $params['motivo'] ?? null,
                'cantidad' => $params['cantidad'],
                'costo_unitario' => $params['costo_unitario'],
                'costo_total' => round($params['cantidad'] * $params['costo_unitario'], 4),
                'saldo_cantidad' => $inventario->stock_actual,
                'saldo_valorizado' => round($inventario->stock_actual * $nuevoCostoPromedio, 4),
                'documento_tipo' => $params['documento_tipo'] ?? null,
                'documento_serie' => $params['documento_serie'] ?? null,
                'documento_numero' => $params['documento_numero'] ?? null,
                'documento_fecha' => $params['documento_fecha'] ?? null,
                'documentable_type' => $params['documentable_type'] ?? null,
                'documentable_id' => $params['documentable_id'] ?? null,
                'fecha_movimiento' => $params['fecha_movimiento'] ?? now()->toDateString(),
            ]);
        });
    }

    /**
     * Registrar una SALIDA de mercadería (venta, traslado, ajuste negativo).
     * Valida stock suficiente, descuenta y registra en Kardex.
     */
    public function registrarSalida(array $params): KardexMovimiento
    {
        return DB::transaction(function () use ($params) {
            $inventario = InventarioStock::lockForUpdate()
                ->where('producto_id', $params['producto_id'])
                ->where('almacen_id', $params['almacen_id'])
                ->firstOrFail();

            // Validar stock disponible
            $stockDisponible = $inventario->stock_actual - $inventario->stock_comprometido;
            if ($stockDisponible < $params['cantidad']) {
                throw new \App\Exceptions\StockInsuficienteException(
                    "Stock insuficiente para producto ID {$params['producto_id']}. "
                    . "Disponible: {$stockDisponible}, Requerido: {$params['cantidad']}"
                );
            }

            // Descontar stock
            $costoSalida = $inventario->costo_promedio;
            $inventario->stock_actual -= $params['cantidad'];
            $inventario->save();

            // Si tiene lote, descontar de lote
            if (isset($params['lote_id']) && $params['lote_id']) {
                $inventarioLote = InventarioLote::lockForUpdate()
                    ->where('producto_id', $params['producto_id'])
                    ->where('almacen_id', $params['almacen_id'])
                    ->where('lote_id', $params['lote_id'])
                    ->firstOrFail();

                if ($inventarioLote->stock_actual < $params['cantidad']) {
                    throw new \App\Exceptions\StockInsuficienteException(
                        "Stock insuficiente en lote {$params['lote_id']} del producto {$params['producto_id']}."
                    );
                }

                $costoSalida = $inventarioLote->costo_unitario;
                $inventarioLote->stock_actual -= $params['cantidad'];
                $inventarioLote->save();
            }

            // Registrar movimiento en Kardex
            return KardexMovimiento::create([
                'empresa_id' => $params['empresa_id'],
                'producto_id' => $params['producto_id'],
                'almacen_id' => $params['almacen_id'],
                'lote_id' => $params['lote_id'] ?? null,
                'serie_id' => $params['serie_id'] ?? null,
                'user_id' => $params['user_id'] ?? auth()->id(),
                'tipo_movimiento' => 'salida',
                'tipo_operacion' => $params['tipo_operacion'],
                'motivo' => $params['motivo'] ?? null,
                'cantidad' => $params['cantidad'],
                'costo_unitario' => $costoSalida,
                'costo_total' => round($params['cantidad'] * $costoSalida, 4),
                'saldo_cantidad' => $inventario->stock_actual,
                'saldo_valorizado' => round($inventario->stock_actual * $inventario->costo_promedio, 4),
                'documento_tipo' => $params['documento_tipo'] ?? null,
                'documento_serie' => $params['documento_serie'] ?? null,
                'documento_numero' => $params['documento_numero'] ?? null,
                'documento_fecha' => $params['documento_fecha'] ?? null,
                'documentable_type' => $params['documentable_type'] ?? null,
                'documentable_id' => $params['documentable_id'] ?? null,
                'fecha_movimiento' => $params['fecha_movimiento'] ?? now()->toDateString(),
            ]);
        });
    }

    /**
     * Reservar stock para una venta sin aún descontarlo.
     * Usado cuando la venta no ha sido despachada todavía.
     */
    public function reservarStock(int $productoId, int $almacenId, float $cantidad): void
    {
        DB::transaction(function () use ($productoId, $almacenId, $cantidad) {
            $inventario = InventarioStock::lockForUpdate()
                ->where('producto_id', $productoId)
                ->where('almacen_id', $almacenId)
                ->firstOrFail();

            $disponible = $inventario->stock_actual - $inventario->stock_comprometido;
            if ($disponible < $cantidad) {
                throw new \App\Exceptions\StockInsuficienteException(
                    "No se puede reservar {$cantidad} unidades. Disponible: {$disponible}"
                );
            }

            $inventario->increment('stock_comprometido', $cantidad);
        });
    }

    /**
     * Liberar stock comprometido (ej: cuando se anula una venta).
     */
    public function liberarStock(int $productoId, int $almacenId, float $cantidad): void
    {
        DB::transaction(function () use ($productoId, $almacenId, $cantidad) {
            InventarioStock::lockForUpdate()
                ->where('producto_id', $productoId)
                ->where('almacen_id', $almacenId)
                ->decrement('stock_comprometido', $cantidad);
        });
    }

    /**
     * Consultar stock actual de un producto en un almacén específico.
     */
    public function consultarStock(int $productoId, int $almacenId): ?InventarioStock
    {
        return InventarioStock::where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->first();
    }

    /**
     * Consultar stock total de un producto en todos los almacenes.
     */
    public function consultarStockTotal(int $productoId): float
    {
        return (float) InventarioStock::where('producto_id', $productoId)
            ->sum('stock_actual');
    }
}
