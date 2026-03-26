<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmpresaDemoSeeder extends Seeder
{
    /**
     * Crear empresa, sucursal, almacén, usuario admin y series de comprobante por default.
     */
    public function run(): void
    {
        // Empresa
        $empresaId = DB::table('empresas')->insertGetId([
            'razon_social' => 'AGRO MINING SOLUTIONS S.A.C.',
            'nombre_comercial' => 'AgroMine',
            'ruc' => '20612345678',
            'direccion_fiscal' => 'Av. Principal 123, Lima',
            'ubigeo' => '150101',
            'departamento' => 'Lima',
            'provincia' => 'Lima',
            'distrito' => 'Lima',
            'sunat_entorno' => 'beta',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Sucursal principal
        $sucursalId = DB::table('sucursales')->insertGetId([
            'empresa_id' => $empresaId,
            'nombre' => 'Sede Principal',
            'direccion' => 'Av. Principal 123, Lima',
            'ubigeo' => '150101',
            'codigo_establecimiento' => '0000',
            'es_principal' => true,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Almacén principal
        DB::table('almacenes')->insert([
            'sucursal_id' => $sucursalId,
            'nombre' => 'Almacén Central',
            'direccion' => 'Av. Principal 123, Lima',
            'ubigeo' => '150101',
            'tipo' => 'principal',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Usuario administrador
        DB::table('users')->updateOrInsert(
            ['email' => 'admin@agromine.pe'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Admin2024$'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Series de comprobante por defecto
        $series = [
            ['tipo_documento' => '01', 'serie' => 'F001'], // Factura
            ['tipo_documento' => '03', 'serie' => 'B001'], // Boleta
            ['tipo_documento' => '07', 'serie' => 'FC01'], // Nota Crédito
            ['tipo_documento' => '08', 'serie' => 'FD01'], // Nota Débito
            ['tipo_documento' => '09', 'serie' => 'T001'], // Guía Remisión
            ['tipo_documento' => '00', 'serie' => 'NV01'], // Nota de Venta (interna)
        ];

        foreach ($series as $s) {
            DB::table('series_comprobante')->updateOrInsert(
                [
                    'empresa_id' => $empresaId,
                    'tipo_documento' => $s['tipo_documento'],
                    'serie' => $s['serie'],
                ],
                [
                    'sucursal_id' => $sucursalId,
                    'correlativo_actual' => 0,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Caja POS
        DB::table('cajas')->insert([
            'sucursal_id' => $sucursalId,
            'nombre' => 'Caja Principal',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Lista de Precio por defecto
        DB::table('listas_precio')->insert([
            'empresa_id' => $empresaId,
            'nombre' => 'Precio General',
            'moneda' => 'PEN',
            'es_default' => true,
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Tipo de cliente por defecto
        DB::table('tipos_cliente')->insert([
            ['empresa_id' => $empresaId, 'nombre' => 'General', 'descuento_default' => 0, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['empresa_id' => $empresaId, 'nombre' => 'Mayorista', 'descuento_default' => 5, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
            ['empresa_id' => $empresaId, 'nombre' => 'Distribuidor', 'descuento_default' => 10, 'activo' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Cliente genérico (Varios - para boletas sin datos)
        DB::table('clientes')->insert([
            'empresa_id' => $empresaId,
            'tipo_documento' => 'DNI',
            'numero_documento' => '00000000',
            'razon_social' => 'CLIENTES VARIOS',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
