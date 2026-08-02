<?php
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Asegurar que el permiso existe
$permission = Permission::firstOrCreate(['name' => 'switch_panels', 'guard_name' => 'web']);

// Asignar al Rol super_admin
$superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);
$superAdminRole->givePermissionTo($permission);

// Asignar al Rol admin
$adminRole = Role::firstOrCreate(['name' => 'admin']);
$adminRole->givePermissionTo($permission);

// Asignar DIRECTAMENTE a todos los usuarios que sean super admin o admin
$users = User::all();
foreach($users as $user) {
    if ($user->hasRole('super_admin') || $user->hasRole('admin')) {
        $user->givePermissionTo($permission);
    }
}

// Limpiar cache de Laravel
app()->make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

echo "Roles actualizados, permisos directos asignados y cache limpiada.\n";
