<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoExistencia extends Model
{
    protected $table = 'tipos_existencia';
    protected $fillable = ['codigo_sunat', 'descripcion', 'activo'];
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
