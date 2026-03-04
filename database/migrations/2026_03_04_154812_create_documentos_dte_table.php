<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración principal Documentos DTE
 * Campos 100% fieles a estructura oficial SII (folio, emisor/receptor, totales, timbre)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_dte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipo_dte_id')->constrained('tipos_dte')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // emisor
            $table->string('folio')->unique();
            $table->date('fecha_emision');
            $table->string('rut_emisor', 12);
            $table->string('razon_social_emisor');
            $table->string('rut_receptor', 12);
            $table->string('razon_social_receptor');
            $table->decimal('monto_neto', 12, 2);
            $table->decimal('iva', 12, 2);
            $table->decimal('monto_total', 12, 2);
            $table->text('timbre')->nullable(); // TED simulado
            $table->text('xml_generado')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_dte');
    }
};