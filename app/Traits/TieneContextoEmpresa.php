<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

/**
 * TieneContextoEmpresa
 *
 * Trait central para todos los componentes Livewire y Controllers.
 * Resuelve empresa_id, sucursal_id y almacen_id desde el usuario
 * autenticado, eliminando todos los valores hardcodeados (= 1).
 *
 * ESCALABILIDAD: Con este trait, el sistema soporta N empresas/sucursales
 * sin tocar ningún componente individual.
 */
trait TieneContextoEmpresa
{
    public int $empresaId = 1;
    public int $sucursalId = 1;
    public int $almacenId = 1;

    /**
     * Inicializar contexto desde el usuario autenticado.
     * Llamar en mount() de cada Livewire component.
     */
    public function inicializarContexto(): void
    {
        $user = Auth::user();

        if (!$user) {
            return; // Valores por defecto ya están seteados
        }

        // Si el modelo User tiene empresa_id, sucursal_id, almacen_id
        $this->empresaId = $user->empresa_id ?? 1;
        $this->sucursalId = $user->sucursal_id ?? 1;
        $this->almacenId = $user->almacen_id ?? 1;
    }

    /**
     * Helper para obtener empresa_id desde auth (uso en Controllers)
     */
    public static function resolverEmpresaId(): int
    {
        return Auth::user()?->empresa_id ?? 1;
    }

    /**
     * Helper para obtener sucursal_id desde auth (uso en Controllers)
     */
    public static function resolverSucursalId(): int
    {
        return Auth::user()?->sucursal_id ?? 1;
    }

    /**
     * Helper para obtener almacen_id desde auth (uso en Controllers)
     */
    public static function resolverAlmacenId(): int
    {
        return Auth::user()?->almacen_id ?? 1;
    }
}
