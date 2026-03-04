<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para tabla tipos_dte
 * Códigos oficiales SII: 33 Factura, 39 Boleta, 56 Nota Débito, 61 Nota Crédito
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_dte', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 3)->unique(); // 33,39,56,61
            $table->string('nombre');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipos_dte');
    }
};