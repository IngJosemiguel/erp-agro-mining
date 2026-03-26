<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class UbicacionAlmacen extends Model
{
    protected $table = 'ubicaciones_almacen';
    protected $fillable = ['almacen_id', 'codigo', 'descripcion', 'activo'];
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
    public function almacen()
    {
        return $this->belongsTo(Almacen::class);
    }
}
