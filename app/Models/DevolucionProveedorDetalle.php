<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevolucionProveedorDetalle extends Model
{
    protected $table = 'devolucion_proveedor_detalles';

    protected $fillable = [
        'devolucion_proveedor_id',
        'producto_id',
        'cantidad',
        'costo_unitario',
        'total',
        'motivo_item',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:4',
            'costo_unitario' => 'decimal:4',
            'total' => 'decimal:2',
        ];
    }

    public function devolucion(): BelongsTo
    {
        return $this->belongsTo(DevolucionProveedor::class, 'devolucion_proveedor_id');
    }
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
