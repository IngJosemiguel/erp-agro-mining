<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea el usuario administrador inicial del sistema.
     *
     * Ejecutar con: php artisan db:seed --class=AdminUserSeeder
     */
    public function run(): void
    {
        // Verificar que no exista ya el admin
        if (DB::table('users')->where('email', 'admin@erpagromine.com')->exists()) {
            $this->command->info('⚠️  Usuario admin ya existe. Saltando...');
            return;
        }

        DB::table('users')->insert([
            'name' => 'Administrador',
            'email' => 'admin@erpagromine.com',
            'password' => Hash::make('Admin1234!'),
            'empresa_id' => 1,
            'sucursal_id' => 1,
            'almacen_id' => 1,
            'rol' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ Usuario administrador creado:');
        $this->command->line('   Email:    admin@erpagromine.com');
        $this->command->line('   Password: Admin1234!');
        $this->command->warn('   ⚠️  Cambia la contraseña después del primer login.');
    }
}
