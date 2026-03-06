<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_dte', function (Blueprint $table) {
            $table->string('giro_receptor')->nullable()->after('razon_social_receptor');
            $table->string('direccion_receptor')->nullable()->after('giro_receptor');
            $table->foreignId('region_id')->nullable()->after('direccion_receptor')->constrained('regiones')->nullOnDelete();
            $table->foreignId('comuna_id')->nullable()->after('region_id')->constrained('comunas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documentos_dte', function (Blueprint $table) {
            $table->dropForeign(['comuna_id']);
            $table->dropForeign(['region_id']);
            $table->dropColumn(['comuna_id', 'region_id', 'direccion_receptor', 'giro_receptor']);
        });
    }
};
