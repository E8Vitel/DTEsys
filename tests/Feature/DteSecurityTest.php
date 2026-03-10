<?php

namespace Tests\Feature;

use App\Models\DocumentoDte;
use App\Models\TipoDte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DteSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_user_cannot_view_dte_from_another_user(): void
    {
        Permission::create(['name' => 'ver-dtes']);

        $owner = User::factory()->create();
        $intruder = User::factory()->create();

        $owner->givePermissionTo('ver-dtes');
        $intruder->givePermissionTo('ver-dtes');

        $dte = $this->createDte($owner, 'F-OWN-001');

        $this->actingAs($intruder)
            ->get(route('dtes.show', $dte))
            ->assertForbidden();
    }

    public function test_export_excel_is_scoped_to_authenticated_user_for_non_admins(): void
    {
        Permission::create(['name' => 'ver-reportes']);

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $userA->givePermissionTo('ver-reportes');
        $userB->givePermissionTo('ver-reportes');

        $dteA = $this->createDte($userA, 'F-USER-A');
        $dteB = $this->createDte($userB, 'F-USER-B');

        $response = $this->actingAs($userA)
            ->get(route('reportes.export-excel'));

        $response->assertOk();

        $csv = $response->streamedContent();

        $this->assertStringContainsString($dteA->folio, $csv);
        $this->assertStringNotContainsString($dteB->folio, $csv);
    }

    private function createDte(User $user, string $folio): DocumentoDte
    {
        $tipo = TipoDte::factory()->create();

        return DocumentoDte::create([
            'tipo_dte_id' => $tipo->id,
            'user_id' => $user->id,
            'folio' => $folio,
            'fecha_emision' => now()->toDateString(),
            'rut_emisor' => $user->rut,
            'razon_social_emisor' => 'Emisor Test',
            'rut_receptor' => '12345678-5',
            'razon_social_receptor' => 'Receptor Test',
            'monto_neto' => 1000,
            'iva' => 190,
            'monto_total' => 1190,
            'timbre' => 'TED-TEST',
            'estado_sii' => 'No enviado',
            'estado_receptor' => 'Sin acuse',
        ]);
    }
}
