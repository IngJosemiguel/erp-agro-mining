<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevolucionProveedor extends Model
{
    use SoftDeletes;

    protected $table = 'devoluciones_proveedor';

    protected $fillable = [
        'empresa_id',
        'proveedor_id',
        'almacen_id',
        'compra_id',
        'user_id',
        'codigo',
        'fecha',
        'motivo',
        'total',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class);
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function detalles(): HasMany
    {
        return $this->hasMany(DevolucionProveedorDetalle::class);
    }

    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) {
            'pendiente' => 'badge-neutral',
            'aprobada' => 'badge-warning',
            'completada' => 'badge-success',
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
        $ultimo = self::where('codigo', 'LIKE', "DEV-{$anio}-%")->max('codigo');
        $numero = $ultimo ? ((int) substr($ultimo, -5)) + 1 : 1;
        return "DEV-{$anio}-" . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }
}
