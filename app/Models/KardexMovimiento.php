<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class KardexMovimiento extends Model
{
    use HasFactory;

    protected $table = 'kardex_movimientos';

    protected $fillable = [
        'empresa_id',
        'producto_id',
        'almacen_id',
        'lote_id',
        'serie_id',
        'user_id',
        'tipo_movimiento',
        'tipo_operacion',
        'motivo',
        'cantidad',
        'costo_unitario',
        'costo_total',
        'saldo_cantidad',
        'saldo_valorizado',
        'documento_tipo',
        'documento_serie',
        'documento_numero',
        'documento_fecha',
        'documentable_type',
        'documentable_id',
        'fecha_movimiento',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:4',
            'costo_unitario' => 'decimal:6',
            'costo_total' => 'decimal:4',
            'saldo_cantidad' => 'decimal:4',
            'saldo_valorizado' => 'decimal:4',
            'fecha_movimiento' => 'date',
            'documento_fecha' => 'date',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }

    public function serie(): BelongsTo
    {
        return $this->belongsTo(Serie::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeDeProducto(Builder $query, int $productoId): Builder
    {
        return $query->where('producto_id', $productoId);
    }

    public function scopeDeAlmacen(Builder $query, int $almacenId): Builder
    {
        return $query->where('almacen_id', $almacenId);
    }

    public function scopeEntreFechas(Builder $query, string $desde, string $hasta): Builder
    {
        return $query->whereBetween('fecha_movimiento', [$desde, $hasta]);
    }

    public function scopeIngresos(Builder $query): Builder
    {
        return $query->where('tipo_movimiento', 'ingreso');
    }

    public function scopeSalidas(Builder $query): Builder
    {
        return $query->where('tipo_movimiento', 'salida');
    }

    public function scopeDeEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    /**
     * Para el Formato SUNAT 13.1: obtiene todos los movimientos
     * de un producto en un periodo dado, ordenados cronológicamente.
     */
    public function scopeKardexValorizado(Builder $query, int $productoId, int $almacenId, string $desde, string $hasta): Builder
    {
        return $query->where('producto_id', $productoId)
            ->where('almacen_id', $almacenId)
            ->whereBetween('fecha_movimiento', [$desde, $hasta])
            ->orderBy('fecha_movimiento')
            ->orderBy('id');
    }
}
