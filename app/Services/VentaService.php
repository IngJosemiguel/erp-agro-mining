<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\SerieComprobante;
use App\Models\Producto;
use App\Models\Serie;
use App\Exceptions\StockInsuficienteException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentaService
{
    public function __construct(
        protected InventarioService $inventarioService,
        protected ContabilidadAutomaticaService $contabilidadService,
    ) {
    }

    /**
     * Crear una nueva venta completa (Nota de Venta, Factura o Boleta).
     * Incluye: validación, correlativo, detalles, afectación de inventario.
     */
    public function crearVenta(array $data): Venta
    {
        return DB::transaction(function () use ($data) {
            // 1. Obtener correlativo
            $serieCorrelativo = $this->obtenerCorrelativo(
                $data['empresa_id'],
                $data['sucursal_id'],
                $data['tipo_documento']
            );

            // 2. Calcular totales
            $calculados = $this->calcularTotales($data['detalles'], $data['descuento_global'] ?? 0);

            // 3. Crear la venta principal
            $venta = Venta::create([
                'empresa_id' => $data['empresa_id'],
                'sucursal_id' => $data['sucursal_id'],
                'almacen_id' => $data['almacen_id'],
                'cliente_id' => $data['cliente_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'tipo_documento' => $data['tipo_documento'],
                'serie' => $serieCorrelativo['serie'],
                'correlativo' => $serieCorrelativo['correlativo'],
                'estado' => $data['tipo_documento'] === Venta::TIPO_NOTA_VENTA
                    ? Venta::ESTADO_EMITIDO
                    : Venta::ESTADO_EMITIDO,
                'fecha_emision' => $data['fecha_emision'] ?? now()->toDateString(),
                'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                'hora_emision' => now()->format('H:i:s'),
                'moneda' => $data['moneda'] ?? 'PEN',
                'tipo_cambio' => $data['tipo_cambio'] ?? 1,
                'subtotal' => $calculados['subtotal'],
                'descuento_global' => $data['descuento_global'] ?? 0,
                'base_imponible' => $calculados['base_imponible'],
                'igv' => $calculados['igv'],
                'total' => $calculados['total'],
                'forma_pago' => $data['forma_pago'] ?? 'contado',
                'monto_pagado' => ($data['forma_pago'] ?? 'contado') === 'contado' ? $calculados['total'] : 0,
                'monto_pendiente' => ($data['forma_pago'] ?? 'contado') === 'credito' ? $calculados['total'] : 0,
                'cotizacion_id' => $data['cotizacion_id'] ?? null,
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            // 4. Pre-cargar todos los productos del pedido en una sola query (evita N+1)
            $productoIds = array_column($data['detalles'] ?? $data['items'] ?? [], 'producto_id');
            $productosMap = Producto::whereIn('id', $productoIds)
                ->with('unidadMedida:id,codigo_sunat,abreviatura')
                ->get()
                ->keyBy('id');

            // 5. Crear detalles y afectar inventario
            foreach ($data['detalles'] as $index => $detalle) {
                $producto = $productosMap->get($detalle['producto_id'])
                    ?? throw new \InvalidArgumentException("Producto ID {$detalle['producto_id']} no encontrado.");

                $valorVenta = round($detalle['cantidad'] * $detalle['precio_unitario'] - ($detalle['descuento'] ?? 0), 2);
                $igvLinea = $producto->es_gravado_igv ? round($valorVenta * 0.18, 2) : 0;

                $ventaDetalle = VentaDetalle::create([
                    'venta_id' => $venta->id,
                    'producto_id' => $detalle['producto_id'],
                    'lote_id' => $detalle['lote_id'] ?? null,
                    'serie_id' => $detalle['serie_id'] ?? null,
                    'orden' => $index + 1,
                    'codigo_producto' => $producto->codigo_interno,
                    'descripcion' => $producto->nombre,
                    'unidad_medida' => $producto->unidadMedida->codigo_sunat ?? 'NIU',
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'descuento' => $detalle['descuento'] ?? 0,
                    'porcentaje_descuento' => $detalle['porcentaje_descuento'] ?? 0,
                    'valor_venta' => $valorVenta,
                    'igv' => $igvLinea,
                    'total' => $valorVenta + $igvLinea,
                    'tipo_afectacion_igv' => $producto->es_gravado_igv ? '10' : '20',
                ]);

                // 5. Afectar inventario (solo para bienes, no servicios)
                if ($producto->esBien()) {
                    $tipoOperacion = '01'; // Venta

                    $this->inventarioService->registrarSalida([
                        'empresa_id' => $data['empresa_id'],
                        'producto_id' => $detalle['producto_id'],
                        'almacen_id' => $data['almacen_id'],
                        'lote_id' => $detalle['lote_id'] ?? null,
                        'serie_id' => $detalle['serie_id'] ?? null,
                        'tipo_operacion' => $tipoOperacion,
                        'motivo' => 'Venta ' . $venta->numero_completo,
                        'cantidad' => $detalle['cantidad'],
                        'documento_tipo' => $data['tipo_documento'],
                        'documento_serie' => $serieCorrelativo['serie'],
                        'documento_numero' => $serieCorrelativo['correlativo'],
                        'documento_fecha' => $data['fecha_emision'] ?? now()->toDateString(),
                        'documentable_type' => Venta::class,
                        'documentable_id' => $venta->id,
                    ]);

                    // Si el producto controla series, marcar como vendida
                    if ($producto->requiereSerie() && isset($detalle['serie_id'])) {
                        Serie::findOrFail($detalle['serie_id'])->marcarVendida();
                    }
                }
            }

            // 6. Si es crédito, crear cuotas
            if (($data['forma_pago'] ?? 'contado') === 'credito' && !empty($data['cuotas'])) {
                foreach ($data['cuotas'] as $cuota) {
                    $venta->cuotas()->create([
                        'numero_cuota' => $cuota['numero_cuota'],
                        'monto' => $cuota['monto'],
                        'fecha_vencimiento' => $cuota['fecha_vencimiento'],
                    ]);
                }
            }

            // 7. Automatización Contable — generar asiento validado
            try {
                $formaPago = $data['forma_pago'] ?? 'contado';
                if ($formaPago === 'credito') {
                    $this->contabilidadService->registrarVentaCredito($data['empresa_id'], $venta);
                } else {
                    $this->contabilidadService->registrarVentaContado($data['empresa_id'], $venta);
                }
            } catch (\Exception $e) {
                Log::warning('Contabilidad automática: no se pudo generar asiento de venta', [
                    'venta_id' => $venta->id,
                    'error' => $e->getMessage()
                ]);
            }

            return $venta->load('detalles', 'cliente', 'cuotas');
        });
    }

    /**
     * Anular una venta y revertir toda la afectación de inventario.
     */
    public function anularVenta(int $ventaId, string $motivo): Venta
    {
        return DB::transaction(function () use ($ventaId, $motivo) {
            $venta = Venta::with('detalles.producto')->lockForUpdate()->findOrFail($ventaId);

            if (!$venta->puedeAnularse()) {
                throw new \Exception("Esta venta no puede anularse en su estado actual: {$venta->estado}");
            }

            // Revertir inventario: generar ingresos por cada línea
            foreach ($venta->detalles as $detalle) {
                if ($detalle->producto->esBien()) {
                    $this->inventarioService->registrarIngreso([
                        'empresa_id' => $venta->empresa_id,
                        'producto_id' => $detalle->producto_id,
                        'almacen_id' => $venta->almacen_id,
                        'lote_id' => $detalle->lote_id,
                        'serie_id' => $detalle->serie_id,
                        'cantidad' => $detalle->cantidad,
                        'costo_unitario' => $detalle->valor_venta / $detalle->cantidad, // Precio de costo
                        'tipo_operacion' => '11', // Anulación
                        'motivo' => "Anulación de venta {$venta->numero_completo}: {$motivo}",
                    ]);

                    // Si controlaba serie, liberar
                    if ($detalle->serie_id) {
                        Serie::find($detalle->serie_id)?->marcarDisponible();
                    }
                }
            }

            $venta->update([
                'estado' => Venta::ESTADO_ANULADO,
                'observaciones' => ($venta->observaciones ? $venta->observaciones . ' | ' : '') . "ANULADO: {$motivo}",
            ]);

            // Reversa contable automática
            try {
                $this->contabilidadService->registrarReversa($venta->empresa_id, 'ventas', $venta->id, $motivo);
            } catch (\Exception $e) {
                Log::warning('Contabilidad automática: no se pudo generar reversa', [
                    'venta_id' => $venta->id,
                    'error' => $e->getMessage()
                ]);
            }

            return $venta->fresh();
        });
    }

    /**
     * Obtener el siguiente correlativo para una serie.
     */
    protected function obtenerCorrelativo(int $empresaId, int $sucursalId, string $tipoDocumento): array
    {
        $serieComprobante = SerieComprobante::lockForUpdate()
            ->where('empresa_id', $empresaId)
            ->where('sucursal_id', $sucursalId)
            ->where('tipo_documento', $tipoDocumento)
            ->where('activo', true)
            ->firstOrFail();

        $correlativo = $serieComprobante->siguienteCorrelativo();

        return [
            'serie' => $serieComprobante->serie,
            'correlativo' => str_pad($correlativo, 8, '0', STR_PAD_LEFT),
        ];
    }

    /**
     * Calcular sub/total/IGV de las líneas de detalle.
     */
    protected function calcularTotales(array $detalles, float $descuentoGlobal = 0): array
    {
        $subtotal = 0;
        $igvTotal = 0;

        foreach ($detalles as $detalle) {
            $producto = Producto::find($detalle['producto_id']);
            $valorLinea = round($detalle['cantidad'] * $detalle['precio_unitario'] - ($detalle['descuento'] ?? 0), 2);

            $subtotal += $valorLinea;

            if ($producto && $producto->es_gravado_igv) {
                $igvTotal += round($valorLinea * 0.18, 2);
            }
        }

        $baseImponible = $subtotal - $descuentoGlobal;
        $igvFinal = round($baseImponible > 0 ? ($igvTotal * ($baseImponible / $subtotal)) : 0, 2);

        return [
            'subtotal' => round($subtotal, 2),
            'base_imponible' => round($baseImponible, 2),
            'igv' => $igvFinal,
            'total' => round($baseImponible + $igvFinal, 2),
        ];
    }
}
