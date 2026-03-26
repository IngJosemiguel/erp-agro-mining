<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DespachoDetalle extends Model
{
    protected $table = 'despacho_detalles';
    protected $fillable = ['despacho_id', 'producto_id', 'lote_id', 'serie_id', 'cantidad_solicitada', 'cantidad_despachada'];
    public function despacho()
    {
        return $this->belongsTo(Despacho::class);
    }
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
