<?php
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

// Crear el permiso si no existe
$permission = Permission::firstOrCreate([
    'name' => 'switch_panels',
    'guard_name' => 'web',
]);

// Asignárselo al super_admin por defecto
$superAdmin = Role::where('name', 'super_admin')->first();
if ($superAdmin) {
    $superAdmin->givePermissionTo($permission);
}

// También podemos dárselo temporalmente a 'admin' para que no quede trancado, pero la premisa es que el super_admin controla.
$admin = Role::where('name', 'admin')->first();
if ($admin) {
    $admin->givePermissionTo($permission);
}

echo "Permiso 'switch_panels' creado exitosamente.\n";
