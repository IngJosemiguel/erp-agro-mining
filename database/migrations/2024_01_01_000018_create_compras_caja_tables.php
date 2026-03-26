<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // =============================================
        // COMPRAS
        // =============================================
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('user_id')->constrained('users');

            // Documento del proveedor
            $table->string('tipo_documento', 2)->comment('01=Factura, 03=Boleta del proveedor');
            $table->string('serie', 10);
            $table->string('correlativo', 20);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();

            $table->string('moneda', 3)->default('PEN');
            $table->decimal('tipo_cambio', 8, 4)->default(1);

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento_global', 14, 2)->default(0);
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('igv', 14, 2)->default(0);
            $table->decimal('isc', 14, 2)->default(0);
            $table->decimal('otros_tributos', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->enum('forma_pago', ['contado', 'credito'])->default('contado');
            $table->decimal('monto_pagado', 14, 2)->default(0);

            $table->enum('estado', ['borrador', 'registrada', 'ingresada_almacen', 'anulada'])->default('borrador');

            // Percepciones / Retenciones / Detracciones
            $table->boolean('tiene_percepcion')->default(false);
            $table->decimal('monto_percepcion', 14, 2)->default(0);
            $table->boolean('tiene_retencion')->default(false);
            $table->decimal('monto_retencion', 14, 2)->default(0);
            $table->boolean('tiene_detraccion')->default(false);
            $table->decimal('porcentaje_detraccion', 5, 2)->default(0);
            $table->decimal('monto_detraccion', 14, 2)->default(0);
            $table->string('numero_detraccion', 30)->nullable();

            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'proveedor_id', 'tipo_documento', 'serie', 'correlativo'], 'compra_doc_unico');
            $table->index(['fecha_emision']);
        });

        Schema::create('compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');

            $table->string('descripcion');
            $table->string('unidad_medida', 5)->default('NIU');
            $table->decimal('cantidad', 14, 4);
            $table->decimal('precio_unitario', 14, 4);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('valor_compra', 14, 2);
            $table->decimal('igv', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->timestamps();
        });

        // =============================================
        // ORDENES DE COMPRA
        // =============================================
        Schema::create('ordenes_compra', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('proveedor_id')->constrained('proveedores');
            $table->foreignId('user_id')->constrained('users');

            $table->string('codigo', 20)->unique();
            $table->date('fecha_emision');
            $table->date('fecha_entrega_estimada')->nullable();
            $table->string('moneda', 3)->default('PEN');

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('igv', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->enum('estado', ['borrador', 'enviada', 'parcial', 'completa', 'anulada'])->default('borrador');
            $table->text('condiciones')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orden_compra_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orden_compra_id')->constrained('ordenes_compra')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('descripcion');
            $table->decimal('cantidad', 14, 4);
            $table->decimal('cantidad_recibida', 14, 4)->default(0);
            $table->decimal('precio_unitario', 14, 4);
            $table->decimal('total', 14, 2);
            $table->timestamps();
        });

        // =============================================
        // GASTOS DIVERSOS
        // =============================================
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores');
            $table->foreignId('user_id')->constrained('users');

            $table->string('tipo_documento', 2)->nullable();
            $table->string('serie', 10)->nullable();
            $table->string('correlativo', 20)->nullable();
            $table->date('fecha');
            $table->string('concepto');
            $table->string('categoria_gasto')->nullable();

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('igv', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->enum('estado', ['registrado', 'pagado', 'anulado'])->default('registrado');
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        // =============================================
        // CAJA POS
        // =============================================
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->string('nombre');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('caja_aperturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_id')->constrained('cajas');
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('monto_apertura', 14, 2);
            $table->decimal('monto_cierre', 14, 2)->nullable();
            $table->decimal('monto_efectivo_real', 14, 2)->nullable()->comment('Efectivo contado al cierre');
            $table->decimal('monto_diferencia', 14, 2)->nullable();
            $table->datetime('fecha_apertura');
            $table->datetime('fecha_cierre')->nullable();
            $table->enum('estado', ['abierta', 'cerrada'])->default('abierta');
            $table->text('observaciones_cierre')->nullable();
            $table->timestamps();

            $table->index(['caja_id', 'estado']);
        });

        Schema::create('caja_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('caja_apertura_id')->constrained('caja_aperturas');
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->enum('concepto', [
                'venta_efectivo',
                'venta_tarjeta',
                'venta_transferencia',
                'venta_yape_plin',
                'devolucion',
                'gasto_caja_chica',
                'ingreso_otro',
                'egreso_otro',
            ]);
            $table->decimal('monto', 14, 2);
            $table->string('referencia')->nullable()->comment('Número de comprobante asociado');
            $table->nullableMorphs('movible');
            $table->text('descripcion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('caja_movimientos');
        Schema::dropIfExists('caja_aperturas');
        Schema::dropIfExists('cajas');
        Schema::dropIfExists('gastos');
        Schema::dropIfExists('orden_compra_detalles');
        Schema::dropIfExists('ordenes_compra');
        Schema::dropIfExists('compra_detalles');
        Schema::dropIfExists('compras');
    }
};
