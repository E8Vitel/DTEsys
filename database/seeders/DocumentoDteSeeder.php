<?php

namespace Database\Seeders;

use App\Models\DetalleDte;
use App\Models\DocumentoDte;
use App\Models\HistorialEstadoDte;
use App\Models\TipoDte;
use App\Models\User;
use Illuminate\Database\Seeder;

class DocumentoDteSeeder extends Seeder
{
    public function run(): void
    {
        // Usa al primer usuario como propietario de los datos de demostracion.
        $admin = User::first();
        $tipos = TipoDte::all();

        foreach ($tipos as $tipo) {
            // Se genera un ejemplo por cada tipo de DTE cargado en catalogo.
            $dte = DocumentoDte::create([
                'tipo_dte_id' => $tipo->id,
                'user_id' => $admin->id,
                'folio' => 'F' . rand(1000,9999),
                'fecha_emision' => now()->subDays(rand(1,30)),
                'rut_emisor' => '11111111-1',
                'razon_social_emisor' => 'ARPIS SPA',
                'rut_receptor' => '22222222-2',
                'razon_social_receptor' => 'Cliente Test',
                'monto_neto' => 100000,
                'iva' => 19000,
                'monto_total' => 119000,
                'timbre' => 'TED-SIMULADO-' . rand(100000,999999),
            ]);

            // Item simple para que el documento tenga monto y contenido.
            DetalleDte::create([
                'documento_dte_id' => $dte->id,
                'cantidad' => 1,
                'descripcion' => 'Servicio de desarrollo',
                'precio_unitario' => 100000,
                'subtotal' => 100000,
            ]);

            // Historial minimo para probar la trazabilidad.
            HistorialEstadoDte::create([
                'documento_dte_id' => $dte->id,
                'estado_anterior' => 'Borrador',
                'estado_nuevo' => 'Emitida',
                'user_id' => $admin->id,
                'comentario' => 'Creación inicial',
            ]);
        }
    }
}
