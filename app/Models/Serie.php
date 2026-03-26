<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Serie extends Model
{
    protected $fillable = [
        'producto_id',
        'numero_serie',
        'estado',
        'fecha_ingreso',
        'proveedor_origen',
        'documento_compra_ref',
        'notas',
    ];

    protected function casts(): array
    {
        return ['fecha_ingreso' => 'date'];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function estaDisponible(): bool
    {
        return $this->estado === 'disponible';
    }
    public function marcarVendida(): void
    {
        $this->update(['estado' => 'vendido']);
    }
    public function marcarDisponible(): void
    {
        $this->update(['estado' => 'disponible']);
    }
}
