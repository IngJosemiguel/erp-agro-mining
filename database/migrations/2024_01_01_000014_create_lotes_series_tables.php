<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // =============================================
        // LOTES: Control de vencimiento (Agro)
        // =============================================
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('numero_lote', 50);
            $table->date('fecha_fabricacion')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->string('registro_sanitario', 50)->nullable()->comment('SENASA/DIGESA para agroquímicos');
            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['producto_id', 'numero_lote']);
            $table->index(['fecha_vencimiento']);
            $table->index(['producto_id', 'activo']);
        });

        // =============================================
        // SERIES: Control de maquinaria/equipos (Minería)
        // =============================================
        Schema::create('series', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->string('numero_serie', 80);
            $table->enum('estado', [
                'disponible',
                'vendido',
                'en_garantia',
                'en_mantenimiento',
                'dado_de_baja',
            ])->default('disponible');
            $table->date('fecha_ingreso')->nullable();
            $table->string('proveedor_origen')->nullable()->comment('Proveedor del que se compró');
            $table->string('documento_compra_ref')->nullable()->comment('Factura/guía de compra');
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->unique(['producto_id', 'numero_serie']);
            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('series');
        Schema::dropIfExists('lotes');
    }
};
