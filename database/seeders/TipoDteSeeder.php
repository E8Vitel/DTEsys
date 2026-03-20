<?php

namespace Database\Seeders;

use App\Models\TipoDte;
use Illuminate\Database\Seeder;

class TipoDteSeeder extends Seeder
{
    public function run(): void
    {
        // Tipos iniciales soportados por el sistema.
        TipoDte::insert([
            ['codigo' => '33', 'nombre' => 'Factura Electronica'],
            ['codigo' => '39', 'nombre' => 'Boleta Electronica'],
            ['codigo' => '52', 'nombre' => 'Guia de Despacho Electronica'],
            ['codigo' => '56', 'nombre' => 'Nota de Debito'],
            ['codigo' => '61', 'nombre' => 'Nota de Credito'],
        ]);
    }
}
