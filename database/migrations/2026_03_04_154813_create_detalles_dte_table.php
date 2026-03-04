<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Detalles de ítems por DTE (obligatorio SII)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalles_dte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_dte_id')->constrained('documentos_dte')->cascadeOnDelete();
            $table->integer('cantidad');
            $table->string('descripcion');
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_dte');
    }
};