<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Traslado extends Model
{
    use SoftDeletes;
    protected $fillable = ['empresa_id', 'almacen_origen_id', 'almacen_destino_id', 'user_id', 'codigo', 'fecha', 'estado', 'motivo', 'observaciones'];
    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }
    public function almacenOrigen(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_origen_id');
    }
    public function almacenDestino(): BelongsTo
    {
        return $this->belongsTo(Almacen::class, 'almacen_destino_id');
    }
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function detalles(): HasMany
    {
        return $this->hasMany(TrasladoDetalle::class);
    }
    public function getEstadoBadgeAttribute(): string
    {
        return match ($this->estado) { 'pendiente' => 'badge-neutral', 'en_transito' => 'badge-warning', 'completado' => 'badge-success', 'anulado' => 'badge-danger', default => 'badge-neutral'};
    }
    public function getEstadoTextoAttribute(): string
    {
        return strtoupper(str_replace('_', ' ', $this->estado));
    }
    public static function generarCodigo(): string
    {
        $anio = now()->format('Y');
        $u = self::where('codigo', 'LIKE', "TR-{$anio}-%")->max('codigo');
        $n = $u ? ((int) substr($u, -5)) + 1 : 1;
        return "TR-{$anio}-" . str_pad($n, 5, '0', STR_PAD_LEFT);
    }
}
