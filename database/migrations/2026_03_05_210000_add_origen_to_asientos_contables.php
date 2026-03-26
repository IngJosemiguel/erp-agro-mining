<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega columnas `origen` y `origen_id` a asientos_contables
 * para trazabilidad automática de qué módulo generó cada asiento.
 * Además agrega `centro_costo_id` a asiento_partidas.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('asientos_contables', function (Blueprint $table) {
            if (!Schema::hasColumn('asientos_contables', 'origen')) {
                $table->string('origen', 50)->nullable()->after('estado')
                    ->comment('Tabla origen: ventas, compras, gastos');
            }
            if (!Schema::hasColumn('asientos_contables', 'origen_id')) {
                $table->unsignedBigInteger('origen_id')->nullable()->after('origen')
                    ->comment('ID del registro origen');
            }
            // Agregar tipos faltantes
        });

        Schema::table('asiento_partidas', function (Blueprint $table) {
            if (!Schema::hasColumn('asiento_partidas', 'centro_costo_id')) {
                $table->unsignedBigInteger('centro_costo_id')->nullable()->after('centro_costo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('asientos_contables', function (Blueprint $table) {
            $table->dropColumn(['origen', 'origen_id']);
        });
        Schema::table('asiento_partidas', function (Blueprint $table) {
            $table->dropColumn('centro_costo_id');
        });
    }
};
