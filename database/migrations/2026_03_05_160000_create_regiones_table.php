<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('regiones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 2)->unique();
            $table->string('nombre')->unique();
            $table->timestamps();
        });

        $now = now();

        DB::table('regiones')->insert([
            ['codigo' => '15', 'nombre' => 'Arica y Parinacota', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '01', 'nombre' => 'Tarapaca', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '02', 'nombre' => 'Antofagasta', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '03', 'nombre' => 'Atacama', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '04', 'nombre' => 'Coquimbo', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '05', 'nombre' => 'Valparaiso', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '13', 'nombre' => 'Metropolitana de Santiago', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '06', 'nombre' => 'OHiggins', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '07', 'nombre' => 'Maule', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '16', 'nombre' => 'Nuble', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '08', 'nombre' => 'Biobio', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '09', 'nombre' => 'La Araucania', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '14', 'nombre' => 'Los Rios', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '10', 'nombre' => 'Los Lagos', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '11', 'nombre' => 'Aisen del General Carlos Ibanez del Campo', 'created_at' => $now, 'updated_at' => $now],
            ['codigo' => '12', 'nombre' => 'Magallanes y de la Antartica Chilena', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('regiones');
    }
};
