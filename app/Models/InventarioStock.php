<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioStock extends Model
{
    use HasFactory;

    protected $table = 'inventario_stock';

    protected $fillable = [
        'producto_id',
        'almacen_id',
        'ubicacion_id',
        'stock_actual',
        'stock_comprometido',
        'costo_promedio',
        'ultimo_costo',
    ];

    protected function casts(): array
    {
        return [
            'stock_actual' => 'decimal:4',
            'stock_comprometido' => 'decimal:4',
            'costo_promedio' => 'decimal:6',
            'ultimo_costo' => 'decimal:6',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(UbicacionAlmacen::class);
    }

    // ─── Helpers ──────────────────────────────────────────────
    public function getStockDisponibleAttribute(): float
    {
        return (float) ($this->stock_actual - $this->stock_comprometido);
    }

    public function tieneStockSuficiente(float $cantidad): bool
    {
        return $this->stock_disponible >= $cantidad;
    }

    /**
     * Recalcula costo promedio ponderado al ingresar mercadería.
     */
    public function recalcularCostoPromedio(float $cantidadIngresada, float $costoUnitarioNuevo): float
    {
        $valorActual = $this->stock_actual * $this->costo_promedio;
        $valorNuevo = $cantidadIngresada * $costoUnitarioNuevo;
        $stockTotal = $this->stock_actual + $cantidadIngresada;

        if ($stockTotal <= 0) {
            return $costoUnitarioNuevo;
        }

        return round(($valorActual + $valorNuevo) / $stockTotal, 6);
    }
}
