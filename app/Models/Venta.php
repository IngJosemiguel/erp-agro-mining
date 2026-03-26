<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Builder;

class Venta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'almacen_id',
        'cliente_id',
        'user_id',
        'tipo_documento',
        'serie',
        'correlativo',
        'estado',
        'fecha_emision',
        'fecha_vencimiento',
        'hora_emision',
        'moneda',
        'tipo_cambio',
        'subtotal',
        'descuento_global',
        'base_imponible',
        'igv',
        'isc',
        'otros_cargos',
        'total',
        'total_letras',
        'forma_pago',
        'monto_pagado',
        'monto_pendiente',
        'sunat_ticket',
        'sunat_codigo_respuesta',
        'sunat_mensaje_respuesta',
        'sunat_hash',
        'cdr_path',
        'xml_path',
        'pdf_path',
        'venta_referencia_id',
        'tipo_nota',
        'motivo_nota',
        'cotizacion_id',
        'observaciones',
        'placa_vehiculo',
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
            'tipo_cambio' => 'decimal:4',
            'monto_pagado' => 'decimal:2',
            'monto_pendiente' => 'decimal:2',
        ];
    }

    // ─── Constantes ───────────────────────────────────────────
    const TIPO_FACTURA = '01';
    const TIPO_BOLETA = '03';
    const TIPO_NOTA_VENTA = '00';
    const TIPO_NOTA_CREDITO = '07';
    const TIPO_NOTA_DEBITO = '08';

    const ESTADO_BORRADOR = 'borrador';
    const ESTADO_EMITIDO = 'emitido';
    const ESTADO_ENVIADO = 'enviado_sunat';
    const ESTADO_ACEPTADO = 'aceptado';
    const ESTADO_RECHAZADO = 'rechazado';
    const ESTADO_ANULADO = 'anulado';
    const ESTADO_POR_RECTIFICAR = 'por_rectificar';

    // ─── Relaciones ───────────────────────────────────────────
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function cuotas(): HasMany
    {
        return $this->hasMany(VentaCuota::class);
    }

    public function ventaReferencia(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_referencia_id');
    }

    public function notasCredito(): HasMany
    {
        return $this->hasMany(Venta::class, 'venta_referencia_id')
            ->where('tipo_documento', self::TIPO_NOTA_CREDITO);
    }

    public function notasDebito(): HasMany
    {
        return $this->hasMany(Venta::class, 'venta_referencia_id')
            ->where('tipo_documento', self::TIPO_NOTA_DEBITO);
    }

    public function kardexMovimientos(): MorphMany
    {
        return $this->morphMany(KardexMovimiento::class, 'documentable');
    }

    public function despachos(): HasMany
    {
        return $this->hasMany(Despacho::class);
    }

    // ─── Scopes ───────────────────────────────────────────────
    public function scopeDeEmpresa(Builder $query, int $empresaId): Builder
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopePendientesSunat(Builder $query): Builder
    {
        return $query->whereIn('estado', [self::ESTADO_EMITIDO, self::ESTADO_RECHAZADO, self::ESTADO_POR_RECTIFICAR])
            ->whereIn('tipo_documento', [self::TIPO_FACTURA, self::TIPO_BOLETA, self::TIPO_NOTA_CREDITO, self::TIPO_NOTA_DEBITO]);
    }

    public function scopeComprobantesElectronicos(Builder $query): Builder
    {
        return $query->whereIn('tipo_documento', [
            self::TIPO_FACTURA,
            self::TIPO_BOLETA,
            self::TIPO_NOTA_CREDITO,
            self::TIPO_NOTA_DEBITO,
        ]);
    }

    public function scopeNoEnviados(Builder $query): Builder
    {
        return $query->comprobantesElectronicos()->where('estado', self::ESTADO_EMITIDO);
    }

    public function scopePorRectificar(Builder $query): Builder
    {
        return $query->where('estado', self::ESTADO_POR_RECTIFICAR);
    }

    // ─── Helpers ──────────────────────────────────────────────
    public function esComprobanteSunat(): bool
    {
        return in_array($this->tipo_documento, [
            self::TIPO_FACTURA,
            self::TIPO_BOLETA,
            self::TIPO_NOTA_CREDITO,
            self::TIPO_NOTA_DEBITO,
        ]);
    }

    public function esNotaVentaInterna(): bool
    {
        return $this->tipo_documento === self::TIPO_NOTA_VENTA;
    }

    public function getNumeroCompletoAttribute(): string
    {
        return "{$this->serie}-{$this->correlativo}";
    }

    public function getNombreTipoDocumentoAttribute(): string
    {
        return match ($this->tipo_documento) {
            self::TIPO_FACTURA => 'Factura',
            self::TIPO_BOLETA => 'Boleta',
            self::TIPO_NOTA_VENTA => 'Nota de Venta',
            self::TIPO_NOTA_CREDITO => 'Nota de Crédito',
            self::TIPO_NOTA_DEBITO => 'Nota de Débito',
            default => 'Otro',
        };
    }

    public function puedeAnularse(): bool
    {
        return in_array($this->estado, [self::ESTADO_EMITIDO, self::ESTADO_ACEPTADO]);
    }
}
