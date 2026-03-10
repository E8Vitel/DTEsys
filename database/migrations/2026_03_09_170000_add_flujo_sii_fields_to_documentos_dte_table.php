<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documentos_dte', function (Blueprint $table) {
            $table->string('track_id')->nullable()->after('pdf_path');
            $table->string('estado_sii')->default('No enviado')->after('track_id');
            $table->string('codigo_respuesta_sii')->nullable()->after('estado_sii');
            $table->text('mensaje_respuesta_sii')->nullable()->after('codigo_respuesta_sii');
            $table->timestamp('fecha_envio_sii')->nullable()->after('mensaje_respuesta_sii');
            $table->timestamp('fecha_respuesta_sii')->nullable()->after('fecha_envio_sii');
            $table->timestamp('fecha_envio_receptor')->nullable()->after('fecha_respuesta_sii');
            $table->string('estado_receptor')->default('Sin acuse')->after('fecha_envio_receptor');
            $table->timestamp('fecha_respuesta_receptor')->nullable()->after('estado_receptor');
            $table->text('observacion_receptor')->nullable()->after('fecha_respuesta_receptor');
            $table->string('transporte_patente')->nullable()->after('observacion_receptor');
            $table->string('transporte_rut_chofer', 12)->nullable()->after('transporte_patente');
            $table->string('transporte_nombre_chofer')->nullable()->after('transporte_rut_chofer');
            $table->string('direccion_destino')->nullable()->after('transporte_nombre_chofer');
        });
    }

    public function down(): void
    {
        Schema::table('documentos_dte', function (Blueprint $table) {
            $table->dropColumn([
                'track_id',
                'estado_sii',
                'codigo_respuesta_sii',
                'mensaje_respuesta_sii',
                'fecha_envio_sii',
                'fecha_respuesta_sii',
                'fecha_envio_receptor',
                'estado_receptor',
                'fecha_respuesta_receptor',
                'observacion_receptor',
                'transporte_patente',
                'transporte_rut_chofer',
                'transporte_nombre_chofer',
                'direccion_destino',
            ]);
        });
    }
};
