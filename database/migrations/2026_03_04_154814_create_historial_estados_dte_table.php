<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historial completo de estados (Borrador, Emitida, Firmada, Enviada, Pagada, Anulada, Rechazada)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_estados_dte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_dte_id')->constrained('documentos_dte')->cascadeOnDelete();
            $table->string('estado_anterior');
            $table->string('estado_nuevo');
            $table->foreignId('user_id')->constrained('users');
            $table->text('comentario')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estados_dte');
    }
};