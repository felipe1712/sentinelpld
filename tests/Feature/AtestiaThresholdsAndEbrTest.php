<?php

namespace Tests\Feature;

use App\Models\AtestiaActo;
use App\Models\AtestiaCliente;
use App\Models\AtestiaNotaria;
use App\Models\AtestiaParametro;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Atestia\ActoThresholdAndAccumulationService;
use App\Services\Atestia\CashMonitorService;
use Database\Seeders\AtestiaParametroSeeder;
use Database\Seeders\AtestiaRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AtestiaThresholdsAndEbrTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private AtestiaNotaria $notaria;
    private User $notario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AtestiaParametroSeeder::class);
        $this->seed(AtestiaRolesSeeder::class);

        $this->tenant = Tenant::create(['name' => 'Notaría Prueba', 'activo' => true]);
        $this->notaria = AtestiaNotaria::create([
            'tenant_id'          => $this->tenant->id,
            'rfc'                => 'XAXX010101000',
            'titular'            => 'Notario Titular',
            'tipo_fedatario'     => 'NOTARIO',
            'perfil_apartado'    => 'A',
            'perfil_servicio'    => 'pro',
            'numero_fedatario'   => '10',
            'entidad_federativa' => 'CDMX',
        ]);

        $this->notario = User::factory()->create([
            'tenant_id'            => $this->tenant->id,
            'avatar'               => 'avatar-1.jpg',
            'must_change_password' => false,
            'mfa_enabled'          => false,
        ]);
        $this->notario->assignRole('notario');
    }

    public function test_f01_uma_vigency_by_date_and_dashboard_value(): void
    {
        $this->assertEquals(113.14, (float) AtestiaParametro::getValor('uma_diaria_mxn', 0, '2025-12-15'));
        $this->assertEquals(113.14, (float) AtestiaParametro::getValor('uma_diaria_mxn', 0, '2026-01-31'));
        $this->assertEquals(117.31, (float) AtestiaParametro::getValor('uma_diaria_mxn', 0, '2026-02-01'));
        $this->assertEquals(117.31, (float) AtestiaParametro::getValor('uma_diaria_mxn', 0, '2026-11-25'));

        $this->actingAs($this->notario)
            ->get('/app/dashboard')
            ->assertOk()
            ->assertSee('117.31');
    }

    public function test_f02_legacy_atestia_routes_redirect_301_to_app(): void
    {
        $this->actingAs($this->notario)
            ->get('/atestia/dashboard')
            ->assertStatus(301)
            ->assertRedirect('/app/dashboard');
    }

    public function test_f03_f04_report_cases_920k_and_460k_do_not_trigger_aviso_or_art32_with_2026_uma(): void
    {
        $svc = app(ActoThresholdAndAccumulationService::class);

        $actoAa = AtestiaActo::create([
            'tenant_id'          => $this->tenant->id,
            'notaria_id'         => $this->notaria->id,
            'apartado'           => 'A',
            'tipo_inciso'        => 'a',
            'fecha_otorgamiento' => '2026-11-25',
            'valor_precio'       => 920000.00,
        ]);
        $evalAa = $svc->evaluateActo($actoAa);
        $this->assertEquals(117.31, $evalAa['uma_aplicada']);
        $this->assertEquals(7842.47, $evalAa['valor_en_umas']);
        $this->assertFalse($evalAa['requiere_aviso']);

        $pago = $actoAa->pagos()->create([
            'tenant_id' => $this->tenant->id,
            'forma'     => 'efectivo',
            'monto'     => 920000.00,
            'fecha'     => '2026-11-25',
        ]);
        $cashRes = app(CashMonitorService::class)->validatePago($actoAa->fresh(), $pago);
        $this->assertFalse($cashRes['bloqueado_art32']);
        $this->assertDatabaseMissing('atestia_alertas', ['acto_id' => $actoAa->id, 'regla_id' => 'A08']);

        $actoAd = AtestiaActo::create([
            'tenant_id'          => $this->tenant->id,
            'notaria_id'         => $this->notaria->id,
            'apartado'           => 'A',
            'tipo_inciso'        => 'd',
            'fecha_otorgamiento' => '2026-11-25',
            'valor_precio'       => 460000.00,
        ]);
        $evalAd = $svc->evaluateActo($actoAd);
        $this->assertEquals(3921.23, $evalAd['valor_en_umas']);
        $this->assertFalse($evalAd['requiere_aviso']);
    }

    public function test_f03_boundary_thresholds_for_all_apartado_a_incisos(): void
    {
        $svc = app(ActoThresholdAndAccumulationService::class);
        $uma = 117.31;

        // A-a: umbral 8000 UMA
        $abajo = AtestiaActo::create(['tenant_id' => $this->tenant->id, 'notaria_id' => $this->notaria->id, 'apartado' => 'A', 'tipo_inciso' => 'a', 'fecha_otorgamiento' => '2026-11-25', 'valor_precio' => 7999 * $uma]);
        $igual = AtestiaActo::create(['tenant_id' => $this->tenant->id, 'notaria_id' => $this->notaria->id, 'apartado' => 'A', 'tipo_inciso' => 'a', 'fecha_otorgamiento' => '2026-11-25', 'valor_precio' => 8000 * $uma]);
        $this->assertFalse($svc->evaluateActo($abajo)['requiere_aviso']);
        $this->assertTrue($svc->evaluateActo($igual)['requiere_aviso']);

        // A-d: umbral 4000 UMA
        $abajoD = AtestiaActo::create(['tenant_id' => $this->tenant->id, 'notaria_id' => $this->notaria->id, 'apartado' => 'A', 'tipo_inciso' => 'd', 'fecha_otorgamiento' => '2026-11-25', 'valor_precio' => 3999 * $uma]);
        $igualD = AtestiaActo::create(['tenant_id' => $this->tenant->id, 'notaria_id' => $this->notaria->id, 'apartado' => 'A', 'tipo_inciso' => 'd', 'fecha_otorgamiento' => '2026-11-25', 'valor_precio' => 4000 * $uma]);
        $this->assertFalse($svc->evaluateActo($abajoD)['requiere_aviso']);
        $this->assertTrue($svc->evaluateActo($igualD)['requiere_aviso']);

        // A-b, A-c, A-e: aviso siempre
        foreach (['b', 'c', 'e'] as $inc) {
            $acto = AtestiaActo::create(['tenant_id' => $this->tenant->id, 'notaria_id' => $this->notaria->id, 'apartado' => 'A', 'tipo_inciso' => $inc, 'fecha_otorgamiento' => '2026-11-25', 'valor_precio' => 1000.00]);
            $this->assertTrue($svc->evaluateActo($acto)['requiere_aviso']);
        }
    }

    public function test_f09_f10_f11_ebr_varies_by_client_and_validates_rfc_and_bc(): void
    {
        // RFC inválido rechazado
        $this->actingAs($this->notario)
            ->post('/app/clientes', [
                'tipo'                => 'PF',
                'nombre_razon_social' => 'Cliente RFC Malo',
                'rfc'                 => '123',
            ])
            ->assertSessionHasErrors('rfc');

        // PF limpia -> BAJO (< 35)
        $this->actingAs($this->notario)
            ->post('/app/clientes', [
                'tipo'                => 'PF',
                'nombre_razon_social' => 'Persona Limpia',
                'rfc'                 => 'GODE800101ABC',
            ])
            ->assertRedirect('/app/clientes');

        $pf = AtestiaCliente::where('nombre_razon_social', 'Persona Limpia')->firstOrFail();
        $this->assertEquals('BAJO', $pf->ultimoGradoRiesgo->valor);
        $this->assertLessThan(35, $pf->ultimoGradoRiesgo->puntaje);

        // PM sin BC -> rechazada sin declaración
        $this->actingAs($this->notario)
            ->post('/app/clientes', [
                'tipo'                => 'PM',
                'nombre_razon_social' => 'Empresa Sin BC SA',
            ])
            ->assertSessionHasErrors('bc_nombre');

        // PM con declaración sin BC -> bloqueada para actos
        $this->actingAs($this->notario)
            ->post('/app/clientes', [
                'tipo'                       => 'PM',
                'nombre_razon_social'        => 'Empresa En Tramite SA',
                'bc_declaracion_inexistente' => '1',
            ])
            ->assertRedirect('/app/clientes');

        $pm = AtestiaCliente::where('nombre_razon_social', 'Empresa En Tramite SA')->firstOrFail();
        $this->actingAs($this->notario)
            ->post('/app/actos', [
                'apartado'           => 'A',
                'tipo_inciso'        => 'a',
                'fecha_otorgamiento' => '2026-11-25',
                'valor_precio'       => 500000,
                'cliente_id'         => $pm->id,
                'rol_parte'          => 'adquirente',
            ])
            ->assertSessionHasErrors('cliente_id');
    }

    public function test_f20_weak_password_rejected_on_user_creation(): void
    {
        $this->actingAs($this->notario)
            ->post('/app/usuarios', [
                'name'     => 'Operador Prueba',
                'email'    => 'op@notaria.mx',
                'password' => '12345678',
                'role'     => 'operador',
            ])
            ->assertSessionHasErrors('password');
    }

    public function test_f20_toggle_mfa_per_user(): void
    {
        $user = User::factory()->create([
            'tenant_id'            => $this->tenant->id,
            'avatar'               => 'avatar-1.jpg',
            'must_change_password' => false,
            'mfa_enabled'          => false,
        ]);

        $this->actingAs($this->notario)
            ->patch("/app/usuarios/{$user->id}/mfa")
            ->assertRedirect('/app/usuarios');

        $this->assertTrue((bool) $user->fresh()->mfa_enabled);

        $this->actingAs($this->notario)
            ->patch("/app/usuarios/{$user->id}/mfa")
            ->assertRedirect('/app/usuarios');

        $this->assertFalse((bool) $user->fresh()->mfa_enabled);
    }
}
