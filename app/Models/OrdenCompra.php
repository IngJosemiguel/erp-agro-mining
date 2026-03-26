<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdenCompra extends Model
{
    use SoftDeletes;

    protected $table = 'ordenes_compra';

    protected $fillable = [
        'empresa_id',
        'proveedor_id',
        'user_id',
        'codigo',
        'fecha_emision',
        'fecha_entrega_estimada',
        'moneda',
        'subtotal',
        'igv',
        'total',
        'estado',
        'condiciones',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_entrega_estimada' => 'date',
            'subtotal' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function detalles(): HasMany
    {
        return $this->hasMany(OrdenCompraDetalle::class);
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'borrador' => 'badge-neutral',
            'enviada' => 'badge-primary',
            'parcial' => 'badge-warning',
            'completa' => 'badge-success',
            'anulada' => 'badge-danger',
            default => 'badge-neutral',
        };
    }

    public function getEstadoTextoAttribute(): string
    {
        return strtoupper($this->estado);
    }

    public static function generarCodigo(): string
    {
        $anio = now()->format('Y');
        $ultimo = self::where('codigo', 'LIKE', "OC-{$anio}-%")->max('codigo');
        $numero = $ultimo ? ((int) substr($ultimo, -5)) + 1 : 1;
        return "OC-{$anio}-" . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
