<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends Model
{
    use SoftDeletes;

    protected $fillable = ['nombre', 'slug', 'descripcion', 'parent_id', 'orden', 'activo'];
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'parent_id');
    }
    public function hijos(): HasMany
    {
        return $this->hasMany(Categoria::class, 'parent_id')->orderBy('orden');
    }
    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
}
