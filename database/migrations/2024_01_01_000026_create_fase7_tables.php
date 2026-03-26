<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── RETENCIONES ──────────────────────────────────────────────────
        if (!Schema::hasTable('retenciones')) {
            Schema::create('retenciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->default(1);
                $table->unsignedBigInteger('proveedor_id');
                $table->unsignedBigInteger('user_id');
                $table->string('serie', 10);
                $table->string('correlativo', 20);
                $table->date('fecha_emision');
                $table->decimal('base_retencion', 14, 2)->default(0);
                $table->decimal('tasa_retencion', 5, 2)->default(3.00);
                $table->decimal('monto_retencion', 14, 2)->default(0);
                $table->enum('moneda', ['PEN', 'USD'])->default('PEN');
                $table->decimal('tipo_cambio', 8, 4)->default(1.0000);
                $table->enum('estado', ['borrador', 'emitido', 'enviado_sunat', 'aceptado', 'rechazado', 'anulado'])->default('borrador');
                $table->string('sunat_ticket', 255)->nullable();
                $table->text('sunat_mensaje_respuesta')->nullable();
                $table->string('xml_path', 255)->nullable();
                $table->string('cdr_path', 255)->nullable();
                $table->string('pdf_path', 255)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── PERCEPCIONES ─────────────────────────────────────────────────
        if (!Schema::hasTable('percepciones')) {
            Schema::create('percepciones', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->default(1);
                $table->unsignedBigInteger('cliente_id');
                $table->unsignedBigInteger('user_id');
                $table->string('serie', 10);
                $table->string('correlativo', 20);
                $table->date('fecha_emision');
                $table->decimal('base_percepcion', 14, 2)->default(0);
                $table->decimal('tasa_percepcion', 5, 2)->default(2.00);
                $table->decimal('monto_percepcion', 14, 2)->default(0);
                $table->enum('moneda', ['PEN', 'USD'])->default('PEN');
                $table->enum('estado', ['borrador', 'emitido', 'enviado_sunat', 'aceptado', 'rechazado', 'anulado'])->default('borrador');
                $table->string('sunat_ticket', 255)->nullable();
                $table->text('sunat_mensaje_respuesta')->nullable();
                $table->string('xml_path', 255)->nullable();
                $table->string('pdf_path', 255)->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── ÓRDENES DE PEDIDO ────────────────────────────────────────────
        if (!Schema::hasTable('ordenes_pedido')) {
            Schema::create('ordenes_pedido', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->default(1);
                $table->unsignedBigInteger('cliente_id');
                $table->unsignedBigInteger('user_id');
                $table->string('codigo', 20);
                $table->date('fecha');
                $table->date('fecha_entrega')->nullable();
                $table->decimal('subtotal', 14, 2)->default(0);
                $table->decimal('igv', 14, 2)->default(0);
                $table->decimal('total', 14, 2)->default(0);
                $table->enum('estado', ['pendiente', 'aprobado', 'parcial', 'completado', 'anulado'])->default('pendiente');
                $table->text('condiciones')->nullable();
                $table->text('observaciones')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });

            Schema::create('orden_pedido_detalles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('orden_pedido_id');
                $table->unsignedBigInteger('producto_id');
                $table->decimal('cantidad', 12, 4);
                $table->decimal('precio_unitario', 14, 4);
                $table->decimal('descuento', 14, 2)->default(0);
                $table->decimal('total', 14, 2);
                $table->timestamps();
            });
        }

        // ── CAJA-CHICA (petty cash) ───────────────────────────────────────
        if (!Schema::hasTable('caja_chica')) {
            Schema::create('caja_chica', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('empresa_id')->default(1);
                $table->unsignedBigInteger('user_id');
                $table->string('concepto', 255);
                $table->enum('tipo', ['ingreso', 'egreso']);
                $table->decimal('monto', 14, 2);
                $table->date('fecha');
                $table->string('comprobante', 100)->nullable();
                $table->text('descripcion')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('retenciones');
        Schema::dropIfExists('percepciones');
        Schema::dropIfExists('orden_pedido_detalles');
        Schema::dropIfExists('ordenes_pedido');
        Schema::dropIfExists('caja_chica');
    }
};
