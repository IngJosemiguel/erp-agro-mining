<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TipoCliente extends Model
{
    protected $table = 'tipos_cliente';
    protected $fillable = ['empresa_id', 'nombre', 'descuento_default', 'activo'];
    protected function casts(): array
    {
        return ['activo' => 'boolean', 'descuento_default' => 'decimal:2'];
    }
}
