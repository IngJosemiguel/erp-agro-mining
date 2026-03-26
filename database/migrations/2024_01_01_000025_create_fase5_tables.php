<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // TRASLADOS ENTRE ALMACENES
        if (!Schema::hasTable('traslados')) {
            Schema::create('traslados', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas');
                $table->foreignId('almacen_origen_id')->constrained('almacenes');
                $table->foreignId('almacen_destino_id')->constrained('almacenes');
                $table->foreignId('user_id')->constrained('users');
                $table->string('codigo', 20)->unique();
                $table->date('fecha');
                $table->enum('estado', ['pendiente', 'en_transito', 'completado', 'anulado'])->default('pendiente');
                $table->text('motivo')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('traslado_detalles')) {
            Schema::create('traslado_detalles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('traslado_id')->constrained('traslados')->cascadeOnDelete();
                $table->foreignId('producto_id')->constrained('productos');
                $table->decimal('cantidad', 14, 4);
                $table->decimal('costo_unitario', 14, 4)->default(0);
                $table->timestamps();
            });
        }

        // DEVOLUCIONES A PROVEEDOR
        if (!Schema::hasTable('devoluciones_proveedor')) {
            Schema::create('devoluciones_proveedor', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas');
                $table->foreignId('proveedor_id')->constrained('proveedores');
                $table->foreignId('almacen_id')->constrained('almacenes');
                $table->foreignId('compra_id')->nullable()->constrained('compras');
                $table->foreignId('user_id')->constrained('users');
                $table->string('codigo', 20)->unique();
                $table->date('fecha');
                $table->text('motivo');
                $table->decimal('total', 14, 2)->default(0);
                $table->enum('estado', ['pendiente', 'aprobada', 'completada', 'anulada'])->default('pendiente');
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('devolucion_proveedor_detalles')) {
            Schema::create('devolucion_proveedor_detalles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('devolucion_proveedor_id')->constrained('devoluciones_proveedor')->cascadeOnDelete();
                $table->foreignId('producto_id')->constrained('productos');
                $table->decimal('cantidad', 14, 4);
                $table->decimal('costo_unitario', 14, 4)->default(0);
                $table->decimal('total', 14, 2)->default(0);
                $table->text('motivo_item')->nullable();
                $table->timestamps();
            });
        }

        // CREDITOS BANCARIOS
        if (!Schema::hasTable('creditos_bancarios')) {
            Schema::create('creditos_bancarios', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas');
                $table->foreignId('user_id')->constrained('users');
                $table->string('entidad_bancaria');
                $table->string('numero_credito', 50);
                $table->date('fecha_desembolso');
                $table->date('fecha_vencimiento')->nullable();
                $table->decimal('monto_credito', 14, 2);
                $table->decimal('tasa_interes', 8, 4)->default(0);
                $table->integer('cuotas')->default(1);
                $table->decimal('cuota_mensual', 14, 2)->default(0);
                $table->decimal('monto_pagado', 14, 2)->default(0);
                $table->decimal('saldo_pendiente', 14, 2)->default(0);
                $table->string('moneda', 3)->default('PEN');
                $table->enum('estado', ['vigente', 'pagado', 'vencido', 'refinanciado'])->default('vigente');
                $table->text('destino')->nullable()->comment('Para qué se usó el crédito');
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ACTIVOS FIJOS
        if (!Schema::hasTable('activos_fijos')) {
            Schema::create('activos_fijos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('empresa_id')->constrained('empresas');
                $table->foreignId('sucursal_id')->constrained('sucursales');
                $table->foreignId('user_id')->constrained('users');
                $table->string('codigo', 20)->unique();
                $table->string('nombre');
                $table->string('categoria')->nullable();
                $table->string('marca')->nullable();
                $table->string('modelo')->nullable();
                $table->string('numero_serie')->nullable();
                $table->date('fecha_adquisicion');
                $table->decimal('valor_adquisicion', 14, 2);
                $table->decimal('depreciacion_anual', 5, 2)->default(0)->comment('Porcentaje anual');
                $table->decimal('valor_residual', 14, 2)->default(0);
                $table->decimal('depreciacion_acumulada', 14, 2)->default(0);
                $table->string('ubicacion')->nullable();
                $table->string('responsable')->nullable();
                $table->enum('estado', ['activo', 'en_mantenimiento', 'dado_baja', 'vendido'])->default('activo');
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('activos_fijos');
        Schema::dropIfExists('creditos_bancarios');
        Schema::dropIfExists('devolucion_proveedor_detalles');
        Schema::dropIfExists('devoluciones_proveedor');
        Schema::dropIfExists('traslado_detalles');
        Schema::dropIfExists('traslados');
    }
};
