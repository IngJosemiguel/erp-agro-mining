<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivoFijo extends Model
{
    use SoftDeletes;
    protected $table = 'activos_fijos';
    protected $fillable = ['empresa_id', 'sucursal_id', 'user_id', 'codigo', 'nombre', 'categoria', 'marca', 'modelo', 'numero_serie', 'fecha_adquisicion', 'valor_adquisicion', 'depreciacion_anual', 'valor_residual', 'depreciacion_acumulada', 'ubicacion', 'responsable', 'estado', 'observaciones'];
    protected function casts(): array
    {
        return ['fecha_adquisicion' => 'date', 'valor_adquisicion' => 'decimal:2', 'depreciacion_anual' => 'decimal:2', 'valor_residual' => 'decimal:2', 'depreciacion_acumulada' => 'decimal:2'];
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) { 'activo' => 'badge-success', 'en_mantenimiento' => 'badge-warning', 'dado_baja' => 'badge-danger', 'vendido' => 'badge-neutral', default => 'badge-neutral'};
    }
    public function getEstadoTextoAttribute(): string
    {
        return strtoupper(str_replace('_', ' ', $this->estado));
    }
    public function getValorNetoAttribute(): float
    {
        return (float) $this->valor_adquisicion - (float) $this->depreciacion_acumulada;
    }
    public static function generarCodigo(): string
    {
        $u = self::max('codigo');
        $n = $u ? ((int) substr($u, -5)) + 1 : 1;
        return "AF-" . str_pad($n, 5, '0', STR_PAD_LEFT);
    }
    public static array $categorias = ['maquinaria' => 'Maquinaria', 'vehiculos' => 'Vehículos', 'equipos' => 'Equipos', 'muebles' => 'Muebles y enseres', 'informatica' => 'Equipos de cómputo', 'terrenos' => 'Terrenos', 'edificaciones' => 'Edificaciones', 'herramientas' => 'Herramientas', 'otros' => 'Otros'];
}
