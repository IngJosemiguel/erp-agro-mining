<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // =============================================
        // SERIES DE COMPROBANTE (Correlativos SUNAT)
        // =============================================
        Schema::create('series_comprobante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();

            $table->string('tipo_documento', 2)->comment('01=Factura, 03=Boleta, 07=NC, 08=ND, 09=Guía, 20=Retención, 40=Percepción');
            $table->string('serie', 4)->comment('F001, B001, T001, etc.');
            $table->integer('correlativo_actual')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['empresa_id', 'tipo_documento', 'serie']);
        });

        // =============================================
        // VENTAS / COMPROBANTES DE PAGO
        // =============================================
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('user_id')->constrained('users')->comment('Vendedor');

            // Tipo de documento
            $table->string('tipo_documento', 2)->comment('01=Factura, 03=Boleta, 00=Nota de Venta');
            $table->string('serie', 10);
            $table->string('correlativo', 20);

            // Estado
            $table->enum('estado', [
                'borrador',
                'emitido',         // Emitido internamente
                'enviado_sunat',   // XML enviado al OSE
                'aceptado',        // CDR recibido OK
                'rechazado',       // CDR con error
                'anulado',
                'por_rectificar',  // CPE pendiente de rectificación
            ])->default('borrador');

            // Fechas
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento')->nullable();
            $table->time('hora_emision')->nullable();

            // Moneda
            $table->string('moneda', 3)->default('PEN');
            $table->decimal('tipo_cambio', 8, 4)->default(1);

            // Montos
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento_global', 14, 2)->default(0);
            $table->decimal('base_imponible', 14, 2)->default(0);
            $table->decimal('igv', 14, 2)->default(0);
            $table->decimal('isc', 14, 2)->default(0);
            $table->decimal('otros_cargos', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('total_letras', 14, 2)->default(0);

            // Pago
            $table->enum('forma_pago', ['contado', 'credito'])->default('contado');
            $table->decimal('monto_pagado', 14, 2)->default(0);
            $table->decimal('monto_pendiente', 14, 2)->default(0);

            // SUNAT Tracking
            $table->string('sunat_ticket')->nullable();
            $table->string('sunat_codigo_respuesta', 10)->nullable();
            $table->text('sunat_mensaje_respuesta')->nullable();
            $table->string('sunat_hash', 100)->nullable();
            $table->string('cdr_path')->nullable()->comment('Ruta al XML del CDR');
            $table->string('xml_path')->nullable()->comment('Ruta al XML firmado enviado');
            $table->string('pdf_path')->nullable();

            // Relación inversa a nota de crédito/débito
            $table->foreignId('venta_referencia_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->string('tipo_nota', 2)->nullable()->comment('Para NC/ND: código del tipo de nota.');
            $table->string('motivo_nota')->nullable();

            // Cotización de origen
            $table->foreignId('cotizacion_id')->nullable();

            $table->text('observaciones')->nullable();
            $table->string('placa_vehiculo', 10)->nullable()->comment('Para guías de despacho');

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->unique(['empresa_id', 'tipo_documento', 'serie', 'correlativo'], 'venta_comprobante_unico');
            $table->index(['empresa_id', 'estado']);
            $table->index(['fecha_emision']);
            $table->index(['cliente_id']);
        });

        // =============================================
        // DETALLE DE VENTAS
        // =============================================
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');
            $table->foreignId('serie_id')->nullable()->constrained('series');

            $table->integer('orden')->default(1);
            $table->string('codigo_producto', 30);
            $table->string('descripcion');
            $table->string('unidad_medida', 5)->default('NIU');

            $table->decimal('cantidad', 14, 4);
            $table->decimal('precio_unitario', 14, 4);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('porcentaje_descuento', 5, 2)->default(0);
            $table->decimal('valor_venta', 14, 2)->comment('Base imponible de la línea');
            $table->decimal('igv', 14, 2)->default(0);
            $table->decimal('total', 14, 2);

            $table->string('tipo_afectacion_igv', 2)->default('10')->comment('Catálogo 07: 10=Gravado, 20=Exonerado, 30=Inafecto');

            $table->timestamps();
        });

        // =============================================
        // CUOTAS DE PAGO (Ventas a crédito)
        // =============================================
        Schema::create('venta_cuotas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->cascadeOnDelete();
            $table->integer('numero_cuota');
            $table->decimal('monto', 14, 2);
            $table->date('fecha_vencimiento');
            $table->decimal('monto_pagado', 14, 2)->default(0);
            $table->enum('estado', ['pendiente', 'pagado', 'vencido', 'parcial'])->default('pendiente');
            $table->timestamps();
        });

        // =============================================
        // COTIZACIONES
        // =============================================
        Schema::create('cotizaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('cliente_id')->constrained('clientes');
            $table->foreignId('user_id')->constrained('users');

            $table->string('codigo', 20)->unique();
            $table->date('fecha_emision');
            $table->date('fecha_validez');
            $table->string('moneda', 3)->default('PEN');

            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('igv', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            $table->enum('estado', ['borrador', 'enviada', 'aceptada', 'rechazada', 'vencida', 'facturada'])->default('borrador');
            $table->text('condiciones')->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('cotizacion_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cotizacion_id')->constrained('cotizaciones')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->string('descripcion');
            $table->decimal('cantidad', 14, 4);
            $table->decimal('precio_unitario', 14, 4);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cotizacion_detalles');
        Schema::dropIfExists('cotizaciones');
        Schema::dropIfExists('venta_cuotas');
        Schema::dropIfExists('venta_detalles');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('series_comprobante');
    }
};
