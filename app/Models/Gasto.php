<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gasto extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'proveedor_id',
        'user_id',
        'tipo_documento',
        'serie',
        'correlativo',
        'fecha',
        'concepto',
        'categoria_gasto',
        'subtotal',
        'igv',
        'total',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
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

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'registrado' => 'badge-neutral',
            'pagado' => 'badge-success',
            'anulado' => 'badge-danger',
            default => 'badge-neutral',
        };
    }

    public function getEstadoTextoAttribute(): string
    {
        return strtoupper($this->estado);
    }

    public static array $categorias = [
        'servicios' => 'Servicios',
        'suministros' => 'Suministros',
        'transporte' => 'Transporte',
        'mantenimiento' => 'Mantenimiento',
        'alquiler' => 'Alquiler',
        'combustible' => 'Combustible',
        'seguros' => 'Seguros',
        'impuestos' => 'Impuestos y tasas',
        'personal' => 'Personal / Planilla',
        'representacion' => 'Gastos de representación',
        'otros' => 'Otros gastos',
    ];
}
