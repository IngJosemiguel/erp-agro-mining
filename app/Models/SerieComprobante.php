<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SerieComprobante extends Model
{
    protected $table = 'series_comprobante';
    protected $fillable = ['empresa_id', 'sucursal_id', 'tipo_documento', 'serie', 'correlativo_actual', 'activo'];
    protected function casts(): array
    {
        return ['activo' => 'boolean'];
    }
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    /**
     * Obtener y auto-incrementar el siguiente correlativo atómicamente.
     */
    public function siguienteCorrelativo(): int
    {
        $this->increment('correlativo_actual');
        return $this->correlativo_actual;
    }
}
