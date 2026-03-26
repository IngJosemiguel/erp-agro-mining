<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // =============================================
        // CATALOGO: Tipos de Existencia (Catálogo SUNAT N° 05)
        // =============================================
        Schema::create('tipos_existencia', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_sunat', 2)->unique()->comment('Catálogo SUNAT N° 05: 01=Mercadería, 02=Prod.Terminado, etc.');
            $table->string('descripcion');
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // =============================================
        // CATALOGO: Unidades de Medida (Catálogo SUNAT N° 03)
        // =============================================
        Schema::create('unidades_medida', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_sunat', 5)->unique()->comment('Catálogo SUNAT N° 03: NIU, KGM, LTR, etc.');
            $table->string('descripcion');
            $table->string('abreviatura', 10);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        // =============================================
        // CATALOGO: Categorías (jerárquicas)
        // =============================================
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('slug')->unique();
            $table->text('descripcion')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_id');
        });

        // =============================================
        // CATALOGO: Marcas
        // =============================================
        Schema::create('marcas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('slug')->unique();
            $table->string('logo_path')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // =============================================
        // CATALOGO: Laboratorios / Fabricantes
        // =============================================
        Schema::create('laboratorios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('ruc', 11)->nullable();
            $table->string('pais', 2)->default('PE');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('nombre');
        });

        // =============================================
        // TABLA MAESTRA: Productos / Servicios
        // =============================================
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();

            // Clasificación
            $table->foreignId('tipo_existencia_id')->constrained('tipos_existencia');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->foreignId('marca_id')->nullable()->constrained('marcas')->nullOnDelete();
            $table->foreignId('laboratorio_id')->nullable()->constrained('laboratorios')->nullOnDelete();
            $table->foreignId('unidad_medida_id')->constrained('unidades_medida');

            // Identificadores
            $table->string('codigo_interno', 30)->comment('SKU interno de la empresa');
            $table->string('codigo_barras', 50)->nullable();
            $table->string('codigo_sunat', 20)->nullable()->comment('Código de producto SUNAT si aplica');

            // Descripción
            $table->string('nombre');
            $table->string('nombre_corto', 80)->nullable()->comment('Para tickets POS');
            $table->text('descripcion')->nullable();

            // Tipo de producto
            $table->enum('tipo_producto', ['bien', 'servicio', 'combo'])->default('bien');

            // Control especial Agro/Minería
            $table->boolean('controla_lote')->default(false)->comment('Habilitar para agroquímicos, fertilizantes');
            $table->boolean('controla_serie')->default(false)->comment('Habilitar para maquinaria, equipos mineros');
            $table->boolean('controla_vencimiento')->default(false)->comment('Productos perecibles o con fecha límite');
            $table->boolean('es_gravado_igv')->default(true);

            // Precios base
            $table->decimal('precio_compra', 12, 4)->default(0);
            $table->decimal('precio_venta', 12, 4)->default(0);
            $table->decimal('precio_venta_minimo', 12, 4)->default(0)->comment('Precio mínimo autorizado');
            $table->decimal('precio_mayorista', 12, 4)->nullable();

            // Stock global mínimos
            $table->decimal('stock_minimo', 12, 4)->default(0);
            $table->decimal('stock_maximo', 12, 4)->default(0);

            // Imagen
            $table->string('imagen_path')->nullable();

            // Estado
            $table->boolean('activo')->default(true);
            $table->boolean('disponible_pos')->default(true)->comment('Visible en el Punto de Venta');
            $table->boolean('disponible_web')->default(false);

            $table->timestamps();
            $table->softDeletes();

            // Indices de producción
            $table->unique(['empresa_id', 'codigo_interno']);
            $table->index(['empresa_id', 'activo', 'tipo_producto']);
            $table->index(['codigo_barras']);
            $table->index(['nombre']);
            $table->index(['categoria_id', 'activo']);
        });

        // =============================================
        // Precios por lista (mayorista, minorista, especial)
        // =============================================
        Schema::create('listas_precio', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('moneda', 3)->default('PEN');
            $table->boolean('es_default')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::create('lista_precio_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lista_precio_id')->constrained('listas_precio')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->decimal('precio', 12, 4);
            $table->timestamps();

            $table->unique(['lista_precio_id', 'producto_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_precio_producto');
        Schema::dropIfExists('listas_precio');
        Schema::dropIfExists('productos');
        Schema::dropIfExists('laboratorios');
        Schema::dropIfExists('marcas');
        Schema::dropIfExists('categorias');
        Schema::dropIfExists('unidades_medida');
        Schema::dropIfExists('tipos_existencia');
    }
};
