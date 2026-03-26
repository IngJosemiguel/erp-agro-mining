<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Agrega empresa_id, sucursal_id, almacen_id y rol a la tabla users.
     * Necesario para que el trait TieneContextoEmpresa funcione correctamente.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->default(1)->after('id');
            $table->unsignedBigInteger('sucursal_id')->default(1)->after('empresa_id');
            $table->unsignedBigInteger('almacen_id')->default(1)->after('sucursal_id');
            $table->string('rol')->default('admin')->after('almacen_id'); // admin, cajero, almacenero, supervisor
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['empresa_id', 'sucursal_id', 'almacen_id', 'rol']);
        });
    }
};
