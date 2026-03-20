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
        // Permisos granulares usados para controlar cada accion del sistema.
        $permissions = [
            'ver-dtes', 'crear-dte', 'editar-dte', 'eliminar-dte',
            'firmar-dte', 'enviar-dte', 'anular-dte', 'ver-reportes',
            'gestionar-usuarios'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Roles base disponibles en una instalacion inicial.
        $admin = Role::create(['name' => 'admin']);
        $contador = Role::create(['name' => 'contador']);
        $emisor = Role::create(['name' => 'emisor']);

        // El administrador recibe acceso total.
        $admin->givePermissionTo(Permission::all());
        // El contador puede operar documentos y revisar reportes.
        $contador->givePermissionTo(['ver-dtes', 'crear-dte', 'firmar-dte', 'enviar-dte', 'anular-dte', 'ver-reportes']);
        // El emisor se centra en la emision y movimiento del documento.
        $emisor->givePermissionTo(['ver-dtes', 'crear-dte', 'firmar-dte', 'enviar-dte']);
    }
}
