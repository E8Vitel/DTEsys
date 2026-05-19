<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_dte', function (Blueprint $table) {
            $table->string('acuse_receptor_token', 80)->nullable()->after('fecha_envio_receptor');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_dte', function (Blueprint $table) {
            $table->dropColumn('acuse_receptor_token');
        });
    }
};
