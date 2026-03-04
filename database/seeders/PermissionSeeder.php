<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Seeder de permisos y roles según requisitos RBAC del proyecto
 * Permisos granulares listos para fase 4 (firmar, enviar, anular, etc.)
 */
class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Permisos oficiales
        $permissions = [
            'ver-dtes', 'crear-dte', 'editar-dte', 'eliminar-dte',
            'firmar-dte', 'enviar-dte', 'anular-dte', 'ver-reportes',
            'gestionar-usuarios'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Roles
        $admin = Role::create(['name' => 'admin']);
        $contador = Role::create(['name' => 'contador']);
        $emisor = Role::create(['name' => 'emisor']);

        $admin->givePermissionTo(Permission::all());
        $contador->givePermissionTo(['ver-dtes', 'crear-dte', 'firmar-dte', 'enviar-dte', 'anular-dte', 'ver-reportes']);
        $emisor->givePermissionTo(['ver-dtes', 'crear-dte', 'firmar-dte', 'enviar-dte']);
    }
}