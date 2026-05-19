<?php

namespace Tests\Feature;

use App\Models\DocumentoDte;
use App\Models\TipoDte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
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


    public function test_dte_search_only_filters_by_numeric_folio(): void
    {
        Permission::create(['name' => 'ver-dtes']);

        $user = User::factory()->create();
        $user->givePermissionTo('ver-dtes');

        $matchingDte = $this->createDte($user, 'Folio-15');
        $nonMatchingDte = $this->createDte($user, 'Folio-16');

        $this->actingAs($user)
            ->get(route('dtes.index', ['search' => '15']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dtes/Index')
                ->where('filters.search', '15')
                ->has('dtes', 1)
                ->where('dtes.0.id', $matchingDte->id)
                ->where('dtes.0.folio', $matchingDte->folio)
            );

        $this->actingAs($user)
            ->get(route('dtes.index', ['search' => '12345678']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dtes/Index')
                ->where('filters.search', '12345678')
                ->has('dtes', 0)
            );

        $this->assertNotSame($matchingDte->id, $nonMatchingDte->id);
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
