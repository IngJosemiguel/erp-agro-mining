<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Zona extends Model
{
    protected $fillable = ['empresa_id', 'nombre', 'descripcion', 'activo'];
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
