<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnidadMedida extends Model
{
    protected $table = 'unidades_medida';
    protected $fillable = ['codigo_sunat', 'descripcion', 'abreviatura', 'activo'];
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
