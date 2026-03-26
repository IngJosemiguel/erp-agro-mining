<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditoBancario extends Model
{
    use SoftDeletes;
    protected $table = 'creditos_bancarios';
    protected $fillable = ['empresa_id', 'user_id', 'entidad_bancaria', 'numero_credito', 'fecha_desembolso', 'fecha_vencimiento', 'monto_credito', 'tasa_interes', 'cuotas', 'cuota_mensual', 'monto_pagado', 'saldo_pendiente', 'moneda', 'estado', 'destino', 'observaciones'];
    protected function casts(): array
    {
        return ['fecha_desembolso' => 'date', 'fecha_vencimiento' => 'date', 'monto_credito' => 'decimal:2', 'monto_pagado' => 'decimal:2', 'saldo_pendiente' => 'decimal:2', 'cuota_mensual' => 'decimal:2', 'tasa_interes' => 'decimal:4'];
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) { 'vigente' => 'badge-primary', 'pagado' => 'badge-success', 'vencido' => 'badge-danger', 'refinanciado' => 'badge-warning', default => 'badge-neutral'};
    }
    public function getEstadoTextoAttribute(): string
    {
        return strtoupper($this->estado);
    }
}
