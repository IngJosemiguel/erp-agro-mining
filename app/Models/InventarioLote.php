<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioLote extends Model
{
    protected $table = 'inventario_lote';
    protected $fillable = ['producto_id', 'almacen_id', 'lote_id', 'stock_actual', 'costo_unitario'];
    protected function casts(): array
    {
        return ['stock_actual' => 'decimal:4', 'costo_unitario' => 'decimal:6'];
    }
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
    public function almacen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class);
    }
    public function lote(): BelongsTo
    {
        return $this->belongsTo(Lote::class);
    }
}
