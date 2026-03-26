<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogoSunatSeeder extends Seeder
{
    /**
     * Poblar los catálogos oficiales de SUNAT necesarios para operar.
     */
    public function run(): void
    {
        // ======================================
        // Catálogo N° 05: Tipos de Existencia
        // ======================================
        $tiposExistencia = [
            ['codigo_sunat' => '01', 'descripcion' => 'Mercadería'],
            ['codigo_sunat' => '02', 'descripcion' => 'Producto terminado'],
            ['codigo_sunat' => '03', 'descripcion' => 'Materias primas y auxiliares'],
            ['codigo_sunat' => '04', 'descripcion' => 'Envases y embalajes'],
            ['codigo_sunat' => '05', 'descripcion' => 'Suministros diversos'],
            ['codigo_sunat' => '06', 'descripcion' => 'Repuestos'],
            ['codigo_sunat' => '07', 'descripcion' => 'Productos en proceso'],
            ['codigo_sunat' => '08', 'descripcion' => 'Subproductos, desechos y desperdicios'],
            ['codigo_sunat' => '09', 'descripcion' => 'Bienes del activo fijo'],
            ['codigo_sunat' => '10', 'descripcion' => 'Otros activos'],
            ['codigo_sunat' => '99', 'descripcion' => 'Otros'],
        ];

        foreach ($tiposExistencia as $tipo) {
            DB::table('tipos_existencia')->updateOrInsert(
                ['codigo_sunat' => $tipo['codigo_sunat']],
                array_merge($tipo, ['activo' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }

        // ======================================
        // Catálogo N° 03: Unidades de Medida
        // ======================================
        $unidades = [
            ['codigo_sunat' => 'NIU', 'descripcion' => 'Unidad (bienes)', 'abreviatura' => 'UND'],
            ['codigo_sunat' => 'ZZ', 'descripcion' => 'Unidad (servicios)', 'abreviatura' => 'UND'],
            ['codigo_sunat' => 'KGM', 'descripcion' => 'Kilogramo', 'abreviatura' => 'KG'],
            ['codigo_sunat' => 'LTR', 'descripcion' => 'Litro', 'abreviatura' => 'LT'],
            ['codigo_sunat' => 'MTR', 'descripcion' => 'Metro', 'abreviatura' => 'M'],
            ['codigo_sunat' => 'MTK', 'descripcion' => 'Metro cuadrado', 'abreviatura' => 'M2'],
            ['codigo_sunat' => 'MTQ', 'descripcion' => 'Metro cúbico', 'abreviatura' => 'M3'],
            ['codigo_sunat' => 'GRM', 'descripcion' => 'Gramo', 'abreviatura' => 'GR'],
            ['codigo_sunat' => 'TNE', 'descripcion' => 'Tonelada', 'abreviatura' => 'TN'],
            ['codigo_sunat' => 'GLL', 'descripcion' => 'Galón', 'abreviatura' => 'GAL'],
            ['codigo_sunat' => 'BX', 'descripcion' => 'Caja', 'abreviatura' => 'CJ'],
            ['codigo_sunat' => 'PK', 'descripcion' => 'Paquete', 'abreviatura' => 'PQ'],
            ['codigo_sunat' => 'DZN', 'descripcion' => 'Docena', 'abreviatura' => 'DOC'],
            ['codigo_sunat' => 'MLT', 'descripcion' => 'Mililitro', 'abreviatura' => 'ML'],
            ['codigo_sunat' => 'BG', 'descripcion' => 'Bolsa', 'abreviatura' => 'BLS'],
            ['codigo_sunat' => 'ST', 'descripcion' => 'Set', 'abreviatura' => 'SET'],
            ['codigo_sunat' => 'BO', 'descripcion' => 'Botella', 'abreviatura' => 'BOT'],
            ['codigo_sunat' => 'CY', 'descripcion' => 'Cilindro', 'abreviatura' => 'CIL'],
            ['codigo_sunat' => 'FOT', 'descripcion' => 'Pie', 'abreviatura' => 'PIE'],
            ['codigo_sunat' => 'PR', 'descripcion' => 'Par', 'abreviatura' => 'PAR'],
        ];

        foreach ($unidades as $unidad) {
            DB::table('unidades_medida')->updateOrInsert(
                ['codigo_sunat' => $unidad['codigo_sunat']],
                array_merge($unidad, ['activo' => true, 'created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
