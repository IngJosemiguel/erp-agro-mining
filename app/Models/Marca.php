<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Marca extends Model
{
    use SoftDeletes;
    protected $fillable = ['nombre', 'slug', 'logo_path', 'activo'];
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
}
