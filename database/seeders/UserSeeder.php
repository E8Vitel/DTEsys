<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Crea usuarios base para pruebas funcionales del sistema.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Usuario con acceso administrativo total.
        $admin = User::create([
            'name' => 'Administrador Principal',
            'email' => 'admin@dtesys.cl',
            'password' => Hash::make('password123'),
            'rut' => '11111111-1',
            'cargo' => 'Gerente de Sistemas',
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $admin->assignRole($adminRole);

        // Usuario orientado a operacion y control documental.
        $contador = User::create([
            'name' => 'Contador Ejemplo',
            'email' => 'contador@dtesys.cl',
            'password' => Hash::make('password123'),
            'rut' => '22222222-2',
            'cargo' => 'Contador Senior',
        ]);

        $contadorRole = Role::where('name', 'contador')->first();
        $contador->assignRole($contadorRole);

        // Usuario basico de emision.
        User::create([
            'name' => 'Emisor Test',
            'email' => 'emisor@dtesys.cl',
            'password' => Hash::make('password123'),
            'rut' => '33333333-3',
            'cargo' => 'Vendedor',
        ])->assignRole('emisor');
    }
}
