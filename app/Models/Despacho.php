<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Despacho extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'almacen_id',
        'venta_id',
        'user_id',
        'guia_remision_id',
        'codigo',
        'estado',
        'fecha_despacho',
        'transportista',
        'placa',
        'observaciones',
    ];
    protected function casts(): array
    {
        return ['fecha_despacho' => 'datetime'];
    }
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    public function detalles()
    {
        return $this->hasMany(DespachoDetalle::class);
    }
}
