<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Laboratorio extends Model
{
    use SoftDeletes;
    protected $fillable = ['nombre', 'ruc', 'pais', 'activo'];
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
