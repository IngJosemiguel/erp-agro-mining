<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('almacenes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->string('nombre');
            $table->string('direccion')->nullable();
            $table->string('ubigeo', 6)->nullable();
            $table->enum('tipo', ['principal', 'secundario', 'transito', 'produccion'])->default('principal');
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['sucursal_id', 'activo']);
        });

        Schema::create('ubicaciones_almacen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('almacen_id')->constrained('almacenes')->cascadeOnDelete();
            $table->string('codigo', 20)->comment('Ej: A-10-B (Pasillo-Estante-Nivel)');
            $table->string('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['almacen_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicaciones_almacen');
        Schema::dropIfExists('almacenes');
    }
};
