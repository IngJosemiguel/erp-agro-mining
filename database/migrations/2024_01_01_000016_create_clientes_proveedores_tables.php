<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // =============================================
        // TIPOS DE CLIENTE
        // =============================================
        Schema::create('tipos_cliente', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre');
            $table->decimal('descuento_default', 5, 2)->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // =============================================
        // ZONAS (distribución geográfica)
        // =============================================
        Schema::create('zonas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // =============================================
        // CLIENTES
        // =============================================
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('tipo_cliente_id')->nullable()->constrained('tipos_cliente')->nullOnDelete();
            $table->foreignId('zona_id')->nullable()->constrained('zonas')->nullOnDelete();
            $table->foreignId('lista_precio_id')->nullable()->constrained('listas_precio')->nullOnDelete();

            // Identificación
            $table->enum('tipo_documento', ['DNI', 'RUC', 'CE', 'PASAPORTE', 'OTROS'])->default('DNI');
            $table->string('numero_documento', 20);
            $table->string('razon_social')->comment('Nombre o Razón Social');
            $table->string('nombre_comercial')->nullable();

            // Contacto
            $table->string('direccion')->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->string('email')->nullable();

            // Crédito
            $table->decimal('limite_credito', 12, 2)->default(0);
            $table->integer('dias_credito')->default(0);

            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'tipo_documento', 'numero_documento'], 'cliente_documento_unico');
            $table->index(['razon_social']);
            $table->index(['numero_documento']);
        });

        // =============================================
        // PROVEEDORES
        // =============================================
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            $table->enum('tipo_documento', ['RUC', 'DNI', 'CE', 'OTROS'])->default('RUC');
            $table->string('numero_documento', 20);
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();

            $table->string('direccion')->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('contacto_nombre')->nullable();
            $table->string('contacto_telefono', 20)->nullable();

            // Bancarios
            $table->string('banco')->nullable();
            $table->string('cuenta_bancaria', 30)->nullable();
            $table->string('cuenta_cci', 30)->nullable();
            $table->string('cuenta_detraccion', 30)->nullable();

            $table->integer('dias_credito')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'tipo_documento', 'numero_documento'], 'proveedor_documento_unico');
            $table->index(['razon_social']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proveedores');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('zonas');
        Schema::dropIfExists('tipos_cliente');
    }
};
