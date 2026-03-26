<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empresa extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'razon_social',
        'nombre_comercial',
        'ruc',
        'direccion_fiscal',
        'ubigeo',
        'departamento',
        'provincia',
        'distrito',
        'telefono',
        'email',
        'web',
        'logo_path',
        'sunat_usuario_sol',
        'sunat_clave_sol',
        'sunat_certificado_path',
        'sunat_certificado_password',
        'sunat_entorno',
        'sunat_client_id',
        'sunat_client_secret',
        'activo',
    ];

    protected $hidden = [
        'sunat_clave_sol',
        'sunat_certificado_password',
        'sunat_client_secret',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'sunat_clave_sol' => 'encrypted',
            'sunat_certificado_password' => 'encrypted',
            'sunat_client_secret' => 'encrypted',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────
    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class);
    }

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }

    public function clientes(): HasMany
    {
        return $this->hasMany(Cliente::class);
    }

    public function proveedores(): HasMany
    {
        return $this->hasMany(Proveedor::class);
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class);
    }

    public function compras(): HasMany
    {
        return $this->hasMany(Compra::class);
    }

    public function seriesComprobante(): HasMany
    {
        return $this->hasMany(SerieComprobante::class);
    }

    // ─── Helpers ──────────────────────────────────────────────
    public function isProduccion(): bool
    {
        return $this->sunat_entorno === 'produccion';
    }

    public function getSunatWsdlUrl(): string
    {
        return $this->isProduccion()
            ? 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'
            : 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
    }
}
