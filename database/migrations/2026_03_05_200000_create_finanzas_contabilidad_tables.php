<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // =============================================
        // PLAN DE CUENTAS (Contabilidad)
        // =============================================
        Schema::create('plan_cuentas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('codigo', 20)->comment('Ej: 1.1.1, 4.1.2.01');
            $table->string('nombre');
            $table->enum('tipo', ['activo', 'pasivo', 'patrimonio', 'ingreso', 'gasto', 'costo']); // Ecuación contable
            $table->enum('naturaleza', ['deudora', 'acreedora']);
            $table->enum('nivel', ['1', '2', '3', '4', '5'])->default('3'); // Nivel de agrupación
            $table->unsignedBigInteger('cuenta_padre_id')->nullable();
            $table->boolean('acepta_movimiento')->default(true)->comment('False si es cuenta de grupo/resumen');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['empresa_id', 'codigo'], 'plan_cuenta_unica');
            $table->index(['empresa_id', 'tipo']);
        });

        // =============================================
        // ASIENTOS CONTABLES (Diario)
        // =============================================
        Schema::create('asientos_contables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('user_id')->constrained('users');

            $table->string('numero_asiento', 20)->comment('Correlativo del ejercicio: 2024-0001');
            $table->date('fecha');
            $table->string('periodo', 7)->comment('YYYY-MM');
            $table->enum('tipo', [
                'apertura',       // Asiento de apertura del ejercicio
                'venta',          // Generado automáticamente desde ventas
                'compra',         // Generado automáticamente desde compras
                'gasto',          // Gasto registrado
                'caja',           // Movimiento de caja/banco
                'ajuste',         // Ajuste manual
                'cierre',         // Cierre del ejercicio
                'nómina',         // Planilla de sueldos
                'depreciacion',   // Depreciación activos fijos
                'otro',
            ])->default('otro');
            $table->string('glosa')->comment('Descripción del asiento');
            $table->decimal('total_debe', 18, 2)->default(0);
            $table->decimal('total_haber', 18, 2)->default(0);
            $table->enum('estado', ['borrador', 'validado', 'anulado'])->default('borrador');

            // Referencia al documento origen (polimórfico)
            $table->string('referencia_tipo')->nullable()->comment('App\\Models\\Venta, App\\Models\\Compra, etc.');
            $table->unsignedBigInteger('referencia_id')->nullable();
            $table->string('referencia_numero')->nullable()->comment('Número legible: F001-00001');

            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'numero_asiento'], 'asiento_numero_unico');
            $table->index(['empresa_id', 'periodo', 'tipo']);
            $table->index(['referencia_tipo', 'referencia_id']);
        });

        // =============================================
        // DETALLE DEL ASIENTO (Partidas)
        // =============================================
        Schema::create('asiento_partidas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asiento_id')->constrained('asientos_contables')->cascadeOnDelete();
            $table->foreignId('cuenta_id')->constrained('plan_cuentas');
            $table->integer('orden')->default(1);
            $table->string('glosa_partida')->nullable();
            $table->decimal('debe', 18, 2)->default(0);
            $table->decimal('haber', 18, 2)->default(0);
            $table->string('centro_costo')->nullable();
            $table->timestamps();

            $table->index(['asiento_id', 'cuenta_id']);
        });

        // =============================================
        // LIBRO DIARIO (Vista denormalizada para reportes)
        // =============================================
        // No es una tabla separada — se genera con JOINs desde asientos + partidas

        // =============================================
        // CENTROS DE COSTO
        // =============================================
        Schema::create('centros_costo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('codigo', 20)->unique();
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // =============================================
        // EJERCICIOS CONTABLES
        // =============================================
        Schema::create('ejercicios_contables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->year('anio');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->decimal('utilidad_ejercicio', 18, 2)->nullable();
            $table->timestamps();
            $table->unique(['empresa_id', 'anio']);
        });

        // =============================================
        // PERIODOS CONTABLES (Mensual dentro del ejercicio)
        // =============================================
        Schema::create('periodos_contables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ejercicio_id')->constrained('ejercicios_contables')->cascadeOnDelete();
            $table->string('periodo', 7)->comment('YYYY-MM');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->enum('estado', ['abierto', 'cerrado'])->default('abierto');
            $table->timestamps();
            $table->unique(['ejercicio_id', 'periodo']);
        });

        // =============================================
        // PRESUPUESTO / BUDGET
        // =============================================
        Schema::create('presupuestos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('cuenta_id')->constrained('plan_cuentas');
            $table->string('periodo', 7)->comment('YYYY-MM');
            $table->decimal('monto_presupuestado', 18, 2)->default(0);
            $table->decimal('monto_ejecutado', 18, 2)->default(0);
            $table->decimal('variacion', 18, 2)->virtualAs('monto_ejecutado - monto_presupuestado');
            $table->timestamps();
            $table->unique(['empresa_id', 'cuenta_id', 'periodo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presupuestos');
        Schema::dropIfExists('periodos_contables');
        Schema::dropIfExists('ejercicios_contables');
        Schema::dropIfExists('centros_costo');
        Schema::dropIfExists('asiento_partidas');
        Schema::dropIfExists('asientos_contables');
        Schema::dropIfExists('plan_cuentas');
    }
};
