<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrasladoDetalle extends Model
{
    protected $table = 'traslado_detalles';
    protected $fillable = ['traslado_id', 'producto_id', 'cantidad', 'costo_unitario'];
    protected function casts(): array
    {
        return ['cantidad' => 'decimal:4', 'costo_unitario' => 'decimal:4'];
    }
    public function traslado(): BelongsTo
    {
        return $this->belongsTo(Traslado::class);
    }
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
