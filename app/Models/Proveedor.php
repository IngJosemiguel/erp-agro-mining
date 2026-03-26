<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Proveedor extends Model
{
    use SoftDeletes;

    protected $table = 'proveedores';

    protected $fillable = [
        'empresa_id',
        'tipo_documento',
        'numero_documento',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'ubigeo',
        'telefono',
        'email',
        'contacto_nombre',
        'contacto_telefono',
        'banco',
        'cuenta_bancaria',
        'cuenta_cci',
        'cuenta_detraccion',
        'dias_credito',
        'activo',
    ];

    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('razon_social', 'LIKE', "%{$termino}%")
                ->orWhere('numero_documento', 'LIKE', "%{$termino}%");
        });
    }
}
