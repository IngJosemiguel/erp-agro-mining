<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'tipo_existencia_id',
        'categoria_id',
        'marca_id',
        'laboratorio_id',
        'unidad_medida_id',
        'codigo_interno',
        'codigo_barras',
        'codigo_sunat',
        'nombre',
        'nombre_corto',
        'descripcion',
        'tipo_producto',
        'controla_lote',
        'controla_serie',
        'controla_vencimiento',
        'es_gravado_igv',
        'precio_compra',
        'precio_venta',
        'precio_venta_minimo',
        'precio_mayorista',
        'stock_minimo',
        'stock_maximo',
        'imagen_path',
        'activo',
        'disponible_pos',
        'disponible_web',
    ];

    protected function casts(): array
    {
        return [
            'controla_lote' => 'boolean',
            'controla_serie' => 'boolean',
            'controla_vencimiento' => 'boolean',
            'es_gravado_igv' => 'boolean',
            'activo' => 'boolean',
            'disponible_pos' => 'boolean',
            'disponible_web' => 'boolean',
            'precio_compra' => 'decimal:4',
            'precio_venta' => 'decimal:4',
            'precio_venta_minimo' => 'decimal:4',
            'precio_mayorista' => 'decimal:4',
            'stock_minimo' => 'decimal:4',
            'stock_maximo' => 'decimal:4',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function tipoExistencia(): BelongsTo
    {
        return $this->belongsTo(TipoExistencia::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function marca(): BelongsTo
    {
        return $this->belongsTo(Marca::class);
    }

    public function laboratorio(): BelongsTo
    {
        return $this->belongsTo(Laboratorio::class);
    }

    public function unidadMedida(): BelongsTo
    {
        return $this->belongsTo(UnidadMedida::class);
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function series(): HasMany
    {
        return $this->hasMany(Serie::class);
    }

    public function inventarioStock(): HasMany
    {
        return $this->hasMany(InventarioStock::class);
    }

    public function kardexMovimientos(): HasMany
    {
        return $this->hasMany(KardexMovimiento::class);
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    public function scopeDisponiblePos(Builder $query): Builder
    {
        return $query->where('disponible_pos', true)->where('activo', true);
    }

    public function scopeBuscar(Builder $query, string $termino): Builder
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('nombre', 'LIKE', "%{$termino}%")
                ->orWhere('codigo_interno', 'LIKE', "%{$termino}%")
                ->orWhere('codigo_barras', $termino);
        });
    }

    public function scopeDeEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopePorVencer(Builder $query, int $dias = 30): Builder
    {
        return $query->where('controla_vencimiento', true)
            ->whereHas('lotes', function ($q) use ($dias) {
                $q->where('activo', true)
                    ->where('fecha_vencimiento', '<=', now()->addDays($dias))
                    ->where('fecha_vencimiento', '>=', now());
            });
    }

    public function scopeStockBajo(Builder $query): Builder
    {
        return $query->whereHas('inventarioStock', function ($q) {
            $q->whereRaw('stock_actual <= productos.stock_minimo');
        });
    }

    // ─── Accessors ────────────────────────────────────────────
    public function getStockTotalAttribute(): float
    {
        return (float) $this->inventarioStock()->sum('stock_actual');
    }

    public function getStockDisponibleTotalAttribute(): float
    {
        return (float) $this->inventarioStock()->sum(\DB::raw('stock_actual - stock_comprometido'));
    }

    // ─── Helpers ──────────────────────────────────────────────
    public function requiereLote(): bool
    {
        return $this->controla_lote;
    }

    public function requiereSerie(): bool
    {
        return $this->controla_serie;
    }

    public function esBien(): bool
    {
        return $this->tipo_producto === 'bien';
    }

    public function esServicio(): bool
    {
        return $this->tipo_producto === 'servicio';
    }
}
