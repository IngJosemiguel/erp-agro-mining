<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cliente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'empresa_id',
        'tipo_cliente_id',
        'zona_id',
        'lista_precio_id',
        'tipo_documento',
        'numero_documento',
        'razon_social',
        'nombre_comercial',
        'direccion',
        'ubigeo',
        'telefono',
        'celular',
        'email',
        'limite_credito',
        'dias_credito',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'limite_credito' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
    public function tipoCliente(): BelongsTo
    {
        return $this->belongsTo(TipoCliente::class);
    }
    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }
    public function listaPrecio(): BelongsTo
    {
        return $this->belongsTo(ListaPrecio::class);
    }

    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('razon_social', 'LIKE', "%{$termino}%")
                ->orWhere('numero_documento', 'LIKE', "%{$termino}%");
        });
    }
}
