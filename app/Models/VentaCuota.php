<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class VentaCuota extends Model
{
    protected $table = 'venta_cuotas';
    protected $fillable = ['venta_id', 'numero_cuota', 'monto', 'fecha_vencimiento', 'monto_pagado', 'estado'];
    protected function casts(): array
    {
        return ['monto' => 'decimal:2', 'monto_pagado' => 'decimal:2', 'fecha_vencimiento' => 'date'];
    }
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
