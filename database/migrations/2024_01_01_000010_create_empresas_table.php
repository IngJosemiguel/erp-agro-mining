<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social');
            $table->string('nombre_comercial')->nullable();
            $table->string('ruc', 11)->unique();
            $table->string('direccion_fiscal');
            $table->string('ubigeo', 6)->nullable();
            $table->string('departamento')->nullable();
            $table->string('provincia')->nullable();
            $table->string('distrito')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('web')->nullable();
            $table->string('logo_path')->nullable();

            // SUNAT Config
            $table->string('sunat_usuario_sol', 20)->nullable();
            $table->string('sunat_clave_sol')->nullable();
            $table->string('sunat_certificado_path')->nullable();
            $table->string('sunat_certificado_password')->nullable();
            $table->enum('sunat_entorno', ['beta', 'produccion'])->default('beta');
            $table->string('sunat_client_id')->nullable();
            $table->string('sunat_client_secret')->nullable();

            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
