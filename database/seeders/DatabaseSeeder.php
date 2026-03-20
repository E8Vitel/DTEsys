<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Ejecuta la carga inicial completa del sistema.
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // El orden asegura que existan roles y usuarios antes de crear documentos.
        $this->call([
            PermissionSeeder::class,
            UserSeeder::class,
            TipoDteSeeder::class,
            DocumentoDteSeeder::class,
        ]);
    }
}
