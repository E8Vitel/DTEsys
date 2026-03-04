<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory para tipos DTE oficiales SII
 */
class TipoDteFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo' => $this->faker->unique()->randomElement(['33','39','56','61']),
            'nombre' => $this->faker->randomElement(['Factura Electrónica','Boleta Electrónica','Nota de Débito','Nota de Crédito']),
        ];
    }
}