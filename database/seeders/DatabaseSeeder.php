<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Asegurar la existencia de roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);

        // 2. Crear usuario Super Admin
        $user = User::firstOrCreate(
            ['email' => 'admin@bodegapos.ve'],
            [
                'name' => 'Administrador Principal',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $user->assignRole($superAdmin);

        // 3. Crear un cajero de prueba
        $cajero = User::firstOrCreate(
            ['email' => 'caja1@bodegapos.ve'],
            [
                'name' => 'Cajero Principal',
                'password' => Hash::make('password'),
                'is_active' => true,
            ]
        );
        $cajero->assignRole($cashier);
        
        $this->command->info('✅ Usuarios y roles iniciales creados.');
    }
}
