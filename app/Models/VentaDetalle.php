<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class VentaDetalle extends Model
{
    protected $table = 'venta_detalles';
    protected $fillable = [
        'venta_id',
        'producto_id',
        'lote_id',
        'serie_id',
        'orden',
        'codigo_producto',
        'descripcion',
        'unidad_medida',
        'cantidad',
        'precio_unitario',
        'descuento',
        'porcentaje_descuento',
        'valor_venta',
        'igv',
        'total',
        'tipo_afectacion_igv',
    ];
    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:4',
            'precio_unitario' => 'decimal:4',
            'descuento' => 'decimal:2',
            'valor_venta' => 'decimal:2',
            'igv' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }
    public function serie()
    {
        return $this->belongsTo(Serie::class);
    }
}
