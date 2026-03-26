<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Compra extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'almacen_id',
        'proveedor_id',
        'user_id',
        'tipo_documento',
        'serie',
        'correlativo',
        'fecha_emision',
        'fecha_vencimiento',
        'moneda',
        'tipo_cambio',
        'subtotal',
        'descuento_global',
        'base_imponible',
        'igv',
        'isc',
        'otros_tributos',
        'total',
        'forma_pago',
        'monto_pagado',
        'estado',
        'tiene_percepcion',
        'monto_percepcion',
        'tiene_retencion',
        'monto_retencion',
        'tiene_detraccion',
        'porcentaje_detraccion',
        'monto_detraccion',
        'numero_detraccion',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'subtotal' => 'decimal:2',
            'descuento_global' => 'decimal:2',
            'base_imponible' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
            'monto_pagado' => 'decimal:2',
            'tipo_cambio' => 'decimal:4',
            'tiene_percepcion' => 'boolean',
            'tiene_retencion' => 'boolean',
            'tiene_detraccion' => 'boolean',
        ];
    }

    // Relaciones
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function detalles(): HasMany
    {
        return $this->hasMany(CompraDetalle::class);
    }

    // Helpers
    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'borrador' => 'badge-neutral',
            'registrada' => 'badge-primary',
            'ingresada_almacen' => 'badge-success',
            'anulada' => 'badge-danger',
            default => 'badge-neutral',
        };
    }

    public function getEstadoTextoAttribute(): string
    {
        return match ($this->estado) {
            'borrador' => 'BORRADOR',
            'registrada' => 'REGISTRADA',
            'ingresada_almacen' => 'INGRESADA',
            'anulada' => 'ANULADA',
            default => strtoupper($this->estado),
        };
    }

    public function getTipoDocumentoTextoAttribute(): string
    {
        return match ($this->tipo_documento) {
            '01' => 'Factura',
            '03' => 'Boleta',
            default => $this->tipo_documento,
        };
    }
}
