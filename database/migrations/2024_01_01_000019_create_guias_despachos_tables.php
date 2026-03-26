<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // =============================================
        // GUÍAS DE REMISIÓN
        // =============================================
        Schema::create('guias_remision', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('user_id')->constrained('users');

            $table->enum('tipo_guia', ['remitente', 'transportista']);
            $table->string('serie', 10);
            $table->string('correlativo', 20);

            // Traslado
            $table->string('motivo_traslado', 2)->comment('Catálogo 20 SUNAT: 01=Venta, 04=Traslado entre estab., etc.');
            $table->string('descripcion_motivo')->nullable();
            $table->decimal('peso_bruto', 12, 4)->nullable();
            $table->string('unidad_peso', 5)->default('KGM');
            $table->integer('numero_bultos')->nullable();

            // Origen / Destino
            $table->string('ubigeo_partida', 6);
            $table->string('direccion_partida');
            $table->string('ubigeo_llegada', 6);
            $table->string('direccion_llegada');

            // Transportista (si aplica)
            $table->string('transportista_ruc', 11)->nullable();
            $table->string('transportista_razon_social')->nullable();
            $table->string('transportista_registro_mtc', 20)->nullable();

            // Conductor
            $table->string('conductor_dni', 8)->nullable();
            $table->string('conductor_nombres')->nullable();
            $table->string('conductor_apellidos')->nullable();
            $table->string('conductor_licencia', 15)->nullable();

            // Vehículo
            $table->string('vehiculo_placa', 10)->nullable();
            $table->string('vehiculo_placa_semiremolque', 10)->nullable();

            // Destinatario
            $table->string('destinatario_tipo_documento', 1)->nullable();
            $table->string('destinatario_numero_documento', 20)->nullable();
            $table->string('destinatario_razon_social')->nullable();

            $table->date('fecha_emision');
            $table->date('fecha_traslado');

            $table->enum('estado', [
                'borrador',
                'emitida',
                'enviada_sunat',
                'aceptada',
                'rechazada',
                'anulada',
            ])->default('borrador');

            // SUNAT tracking
            $table->string('sunat_ticket')->nullable();
            $table->text('sunat_mensaje_respuesta')->nullable();
            $table->string('xml_path')->nullable();
            $table->string('cdr_path')->nullable();
            $table->string('pdf_path')->nullable();

            // Documento de referencia
            $table->nullableMorphs('documentable');
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'tipo_guia', 'serie', 'correlativo'], 'guia_unica');
        });

        Schema::create('guia_remision_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guia_remision_id')->constrained('guias_remision')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');
            $table->foreignId('serie_id')->nullable()->constrained('series');
            $table->string('descripcion');
            $table->string('unidad_medida', 5)->default('NIU');
            $table->decimal('cantidad', 14, 4);
            $table->decimal('peso', 12, 4)->nullable();
            $table->timestamps();
        });

        // =============================================
        // DESPACHOS
        // =============================================
        Schema::create('despachos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('sucursal_id')->constrained('sucursales');
            $table->foreignId('almacen_id')->constrained('almacenes');
            $table->foreignId('venta_id')->constrained('ventas');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('guia_remision_id')->nullable()->constrained('guias_remision');

            $table->string('codigo', 20)->unique();
            $table->enum('estado', ['pendiente', 'parcial', 'despachado', 'anulado'])->default('pendiente');
            $table->datetime('fecha_despacho')->nullable();
            $table->string('transportista')->nullable();
            $table->string('placa', 10)->nullable();
            $table->text('observaciones')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('despacho_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('despacho_id')->constrained('despachos')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('lote_id')->nullable()->constrained('lotes');
            $table->foreignId('serie_id')->nullable()->constrained('series');
            $table->decimal('cantidad_solicitada', 14, 4);
            $table->decimal('cantidad_despachada', 14, 4)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('despacho_detalles');
        Schema::dropIfExists('despachos');
        Schema::dropIfExists('guia_remision_detalles');
        Schema::dropIfExists('guias_remision');
    }
};
