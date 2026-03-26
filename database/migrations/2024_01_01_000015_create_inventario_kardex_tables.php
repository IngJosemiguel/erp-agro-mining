<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // =============================================
        // INVENTARIO: Stock actual por producto/almacén
        // =============================================
        Schema::create('inventario_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->foreignId('ubicacion_id')->nullable()->constrained('ubicaciones_almacen')->nullOnDelete();
            $table->decimal('stock_actual', 14, 4)->default(0);
            $table->decimal('stock_comprometido', 14, 4)->default(0)->comment('Reservado por ventas aún no despachadas');
            $table->decimal('stock_disponible', 14, 4)->storedAs('stock_actual - stock_comprometido');
            $table->decimal('costo_promedio', 14, 6)->default(0)->comment('Costo promedio ponderado para valorización');
            $table->decimal('ultimo_costo', 14, 6)->default(0)->comment('Último costo de compra');
            $table->timestamps();

            $table->unique(['producto_id', 'almacen_id']);
            $table->index(['almacen_id', 'stock_actual']);
        });

        // =============================================
        // INVENTARIO POR LOTE: Stock por lote (Agro)
        // =============================================
        Schema::create('inventario_lote', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->foreignId('lote_id')->constrained('lotes')->cascadeOnDelete();
            $table->decimal('stock_actual', 14, 4)->default(0);
            $table->decimal('costo_unitario', 14, 6)->default(0);
            $table->timestamps();

            $table->unique(['producto_id', 'almacen_id', 'lote_id']);
        });

        // =============================================
        // KARDEX: Registro de movimientos (Formato SUNAT 13.1)
        // =============================================
        Schema::create('kardex_movimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('lote_id')->nullable()->constrained('lotes')->nullOnDelete();
            $table->foreignId('serie_id')->nullable()->constrained('series')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Tipo y motivo del movimiento
            $table->enum('tipo_movimiento', ['ingreso', 'salida'])->comment('Dirección del movimiento');
            $table->string('tipo_operacion', 2)->comment('Catálogo SUNAT tabla 12: 01=Venta, 02=Compra, 09=Salida interna, etc.');
            $table->string('motivo', 100)->nullable();

            // Cantidades
            $table->decimal('cantidad', 14, 4);
            $table->decimal('costo_unitario', 14, 6)->default(0);
            $table->decimal('costo_total', 14, 4)->default(0);

            // Saldos post-movimiento (VITALES para Formato 13.1)
            $table->decimal('saldo_cantidad', 14, 4)->comment('Stock que queda en este almacén');
            $table->decimal('saldo_valorizado', 14, 4)->comment('Valor monetario del saldo');

            // Referencia documental
            $table->string('documento_tipo', 2)->nullable()->comment('01=Factura, 03=Boleta, 09=Guía, etc.');
            $table->string('documento_serie', 10)->nullable();
            $table->string('documento_numero', 20)->nullable();
            $table->date('documento_fecha')->nullable();

            // Referencia cruzada
            $table->nullableMorphs('documentable'); // Relación polimórfica a Venta, Compra, Traslado, etc.

            $table->date('fecha_movimiento');
            $table->timestamps();

            // Índices de producción para Kardex y reportes
            $table->index(['empresa_id', 'producto_id', 'fecha_movimiento'], 'kardex_empresa_producto_fecha');
            $table->index(['almacen_id', 'producto_id', 'fecha_movimiento'], 'kardex_almacen_producto_fecha');
            $table->index(['producto_id', 'almacen_id', 'created_at'], 'kardex_saldo_lookup');
            $table->index(['fecha_movimiento', 'tipo_movimiento']);
        });

        // =============================================
        // TRASLADOS entre almacenes/sucursales
        // =============================================
        Schema::create('traslados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->string('codigo', 20)->unique();
            $table->foreignId('almacen_origen_id')->constrained('almacenes');
            $table->foreignId('almacen_destino_id')->constrained('almacenes');
            $table->foreignId('user_id')->constrained('users');
            $table->enum('estado', ['borrador', 'en_transito', 'recibido', 'anulado'])->default('borrador');
            $table->text('motivo')->nullable();
            $table->string('guia_remision_serie', 10)->nullable();
            $table->string('guia_remision_numero', 20)->nullable();
            $table->datetime('fecha_envio')->nullable();
            $table->datetime('fecha_recepcion')->nullable();
            $table->foreignId('recibido_por')->nullable()->constrained('users');
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('traslado_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('traslado_id')->constrained('traslados')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');
            $table->foreignId('serie_id')->nullable()->constrained('series');
            $table->decimal('cantidad_enviada', 14, 4);
            $table->decimal('cantidad_recibida', 14, 4)->nullable();
            $table->decimal('costo_unitario', 14, 6)->default(0);
            $table->text('observacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traslado_detalles');
        Schema::dropIfExists('traslados');
        Schema::dropIfExists('kardex_movimientos');
        Schema::dropIfExists('inventario_lote');
        Schema::dropIfExists('inventario_stock');
    }
};
