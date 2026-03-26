<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ListaPrecio extends Model
{
    protected $table = 'listas_precio';
    protected $fillable = ['empresa_id', 'nombre', 'moneda', 'es_default', 'activo'];
    protected function casts(): array
    {
        return ['es_default' => 'boolean', 'activo' => 'boolean'];
    }
    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'lista_precio_producto')->withPivot('precio')->withTimestamps();
    }
}
