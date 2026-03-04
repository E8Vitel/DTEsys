<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Firma digital simulada (texto o canvas base64)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('firmas_dte', function (Blueprint $table) {
            $table->id();
            $table->foreignId('documento_dte_id')->constrained('documentos_dte')->cascadeOnDelete();
            $table->string('tipo_firma'); // 'texto' o 'canvas'
            $table->text('firma_texto')->nullable(); // nombre + cargo
            $table->longText('firma_canvas')->nullable(); // base64
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('firmas_dte');
    }
};