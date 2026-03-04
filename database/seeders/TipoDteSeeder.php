<?php

namespace Database\Seeders;

use App\Models\TipoDte;
use Illuminate\Database\Seeder;

/**
 * Seeder con códigos oficiales SII
 */
class TipoDteSeeder extends Seeder
{
    public function run(): void
    {
        TipoDte::insert([
            ['codigo' => '33', 'nombre' => 'Factura Electrónica'],
            ['codigo' => '39', 'nombre' => 'Boleta Electrónica'],
            ['codigo' => '56', 'nombre' => 'Nota de Débito'],
            ['codigo' => '61', 'nombre' => 'Nota de Crédito'],
        ]);
    }
}