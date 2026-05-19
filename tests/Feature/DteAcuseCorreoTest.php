<?php

namespace Tests\Feature;

use App\Models\DocumentoDte;
use App\Models\TipoDte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class DteAcuseCorreoTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptor_can_only_accept_a_dte_once_from_email(): void
    {
        $dte = $this->createDteForEmailFlow();

        $url = URL::temporarySignedRoute('dtes.acuse-receptor.email', now()->addDay(), [
            'dte' => $dte->id,
            'respuesta' => 'aceptado',
            'token' => $dte->acuse_receptor_token,
        ]);

        $this->get($url)
            ->assertOk()
            ->assertSee('Acuse registrado')
            ->assertSee('Aceptado');

        $this->get($url)
            ->assertOk()
            ->assertSee('Respuesta ya registrada')
            ->assertSee('Ya haz hecho acuse de recibo, cierre esta pestaña.');
    }

    public function test_email_acuse_requires_valid_token(): void
    {
        $dte = $this->createDteForEmailFlow();

        $url = URL::temporarySignedRoute('dtes.acuse-receptor.email', now()->addDay(), [
            'dte' => $dte->id,
            'respuesta' => 'aceptado',
            'token' => 'token-invalido',
        ]);

        $this->get($url)->assertForbidden();
    }

    private function createDteForEmailFlow(): DocumentoDte
    {
        $user = User::factory()->create();
        $tipo = TipoDte::factory()->create();

        return DocumentoDte::create([
            'tipo_dte_id' => $tipo->id,
            'user_id' => $user->id,
            'folio' => 'Folio-100',
            'fecha_emision' => now()->toDateString(),
            'rut_emisor' => $user->rut,
            'razon_social_emisor' => 'Emisor Test',
            'rut_receptor' => '12345678-5',
            'razon_social_receptor' => 'Receptor Test',
            'monto_neto' => 1000,
            'iva' => 190,
            'monto_total' => 1190,
            'timbre' => 'TED-TEST',
            'estado_sii' => 'Aceptado',
            'fecha_envio_receptor' => now(),
            'acuse_receptor_token' => 'token-prueba-acuse',
            'estado_receptor' => 'Sin acuse',
        ]);
    }
}
