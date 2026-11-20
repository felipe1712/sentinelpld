<?php

namespace Tests\Feature;

use App\Models\AtestiaActo;
use App\Models\AtestiaActoParte;
use App\Models\AtestiaCliente;
use App\Models\AtestiaNotaria;
use App\Models\AtestiaPago;
use App\Models\AtestiaParametro;
use App\Models\Tenant;
use App\Services\Atestia\ActoThresholdAndAccumulationService;
use App\Services\Atestia\AtestiaEbrScoringService;
use App\Services\Atestia\AvisoCalendarService;
use App\Services\Atestia\CashMonitorService;
use Database\Seeders\AtestiaParametroSeeder;
use Database\Seeders\AtestiaRolesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AtestiaComplianceTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;
    private AtestiaNotaria $notaria;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(AtestiaParametroSeeder::class);
        $this->seed(AtestiaRolesSeeder::class);

        $this->tenant = Tenant::create([
            'name'   => 'Notaría Pública No. 1',
            'activo' => true,
        ]);

        $this->notaria = AtestiaNotaria::create([
            'tenant_id'          => $this->tenant->id,
            'rfc'                => 'XAXX010101000',
            'titular'            => 'Lic. Juan Pérez',
            'tipo_fedatario'     => 'NOTARIO',
            'perfil_apartado'    => 'A',
            'perfil_servicio'    => 'pro',
            'numero_fedatario'   => '1',
            'entidad_federativa' => 'CDMX',
        ]);
    }

    /** T01: Compraventa inmueble usa valor más alto (catastral > precio) y genera aviso día 17 */
    public function test_t01_inmueble_uses_highest_value_and_schedules_day_17_aviso(): void
    {
        $uma = (float) AtestiaParametro::getValor('uma_diaria_mxn', 113.14);

        $acto = AtestiaActo::create([
            'tenant_id'          => $this->tenant->id,
            'notaria_id'         => $this->notaria->id,
            'apartado'           => 'A',
            'tipo_inciso'        => 'a',
            'fecha_otorgamiento' => '2027-01-31',
            'valor_precio'       => 500000.00,
            'valor_catastral'    => 950000.00, // > 8000 UMA (~905,120)
        ]);

        $res = app(ActoThresholdAndAccumulationService::class)->evaluateActo($acto);

        $this->assertTrue($res['requiere_aviso']);
        $this->assertEquals('fiscal', $res['canal']);
        $this->assertEquals(round(950000 / $uma, 2), $res['valor_en_umas']);

        // T08 + T10: Acto 31-ene -> fecha límite 17-feb; Anexo 12-A desconocido -> pendiente_de_formato
        $aviso = app(AvisoCalendarService::class)->scheduleMonthlyAviso($acto, $res['canal']);
        $this->assertEquals('2027-02-17', $aviso->fecha_limite->toDateString());
        $this->assertEquals('pendiente_de_formato', $aviso->estado);
    }

    /** T02: Dos compraventas del mismo cliente en 4 meses que suman >= umbral */
    public function test_t02_six_month_accumulation_triggers_aviso_on_second_act(): void
    {
        $cliente = AtestiaCliente::create([
            'tenant_id'           => $this->tenant->id,
            'notaria_id'          => $this->notaria->id,
            'tipo'                => 'PF',
            'nombre_razon_social' => 'María López',
        ]);

        $acto1 = AtestiaActo::create([
            'tenant_id'          => $this->tenant->id,
            'notaria_id'         => $this->notaria->id,
            'apartado'           => 'A',
            'tipo_inciso'        => 'a',
            'fecha_otorgamiento' => '2027-01-15',
            'valor_precio'       => 500000.00, // ~4,419 UMA
        ]);
        AtestiaActoParte::create(['acto_id' => $acto1->id, 'cliente_id' => $cliente->id, 'rol' => 'adquirente']);

        $svc = app(ActoThresholdAndAccumulationService::class);
        $r1 = $svc->evaluateActo($acto1);
        $this->assertFalse($r1['requiere_aviso']);

        $acto2 = AtestiaActo::create([
            'tenant_id'          => $this->tenant->id,
            'notaria_id'         => $this->notaria->id,
            'apartado'           => 'A',
            'tipo_inciso'        => 'a',
            'fecha_otorgamiento' => '2027-05-10',
            'valor_precio'       => 500000.00, // Suma 1,000,000 (~8,838 UMA >= 8,000)
        ]);
        AtestiaActoParte::create(['acto_id' => $acto2->id, 'cliente_id' => $cliente->id, 'rol' => 'adquirente']);

        $r2 = $svc->evaluateActo($acto2);
        $this->assertTrue($r2['requiere_aviso']);
        $this->assertTrue($r2['por_acumulacion']);
    }

    /** T04: Pago en efectivo en inmueble >= 8,025 UMA bloquea y genera A08 */
    public function test_t04_cash_payment_prohibited_blocks_act_and_creates_a08(): void
    {
        $acto = AtestiaActo::create([
            'tenant_id'          => $this->tenant->id,
            'notaria_id'         => $this->notaria->id,
            'apartado'           => 'A',
            'tipo_inciso'        => 'a',
            'fecha_otorgamiento' => '2027-02-01',
            'valor_precio'       => 1000000.00,
        ]);

        app(ActoThresholdAndAccumulationService::class)->evaluateActo($acto);

        $pago = AtestiaPago::create([
            'tenant_id' => $this->tenant->id,
            'acto_id'   => $acto->id,
            'forma'     => 'efectivo',
            'monto'     => 100000.00,
            'fecha'     => '2027-02-01',
        ]);

        $res = app(CashMonitorService::class)->validatePago($acto, $pago);

        $this->assertTrue($res['bloqueado_art32']);
        $this->assertEquals('bloqueado_efectivo', $acto->fresh()->estado);
        $this->assertDatabaseHas('atestia_alertas', [
            'acto_id'   => $acto->id,
            'regla_id'  => 'A08',
            'severidad' => 'CRITICA',
        ]);
    }

    /** T05 + T07 + T09 + T15: PEP extranjera fuerza ALTO, perfil acto_unico, versionado metodología y dato faltante=2 */
    public function test_t05_t07_t09_t15_ebr_scoring_hard_rules_and_missing_data(): void
    {
        $cliente = AtestiaCliente::create([
            'tenant_id'           => $this->tenant->id,
            'notaria_id'          => $this->notaria->id,
            'tipo'                => 'PF',
            'nombre_razon_social' => 'Carlos Extranjero',
        ]);

        $ebr = app(AtestiaEbrScoringService::class);

        // T15: indicadores vacíos -> todo puntúa 2 (inherente = 66.67); T05: PEP extranjera fuerza ALTO
        $grado1 = $ebr->evaluateCliente($cliente, [], [], ['pep_extranjera' => true]);
        $this->assertEquals('ALTO', $grado1->valor);
        $this->assertEquals('PEP_EXTRANJERA_ALTO', $grado1->regla_forzada);
        $this->assertNotEmpty($grado1->factores_json['_datos_faltantes']);

        // T09: cambio de versión de metodología preserva el registro previo intacto
        $grado2 = $ebr->evaluateCliente($cliente, [], [], [], null, 'manual', 'ebr-notaria-v2.0');
        $this->assertEquals('ebr-notaria-v1.0', $grado1->fresh()->metodologia_ver);
        $this->assertEquals('ebr-notaria-v2.0', $grado2->metodologia_ver);

        // T07: Perfil acto_unico
        $perfil = $ebr->updatePerfilTransaccional($cliente, 'acto_unico', 950000.00);
        $this->assertEquals('acto_unico', $perfil->base);
    }

    /** T12: Colaborador no puede tener permiso de ver avisos ni acuses (Art. 63 fr. II) */
    public function test_t12_colaborador_cannot_view_avisos_or_acuses(): void
    {
        $colaboradorRole = Role::findByName('colaborador', 'web');
        $this->assertFalse($colaboradorRole->hasPermissionTo('atestia.avisos.ver'));
        $this->assertFalse($colaboradorRole->hasPermissionTo('atestia.acuses.ver'));

        $notarioRole = Role::findByName('notario', 'web');
        $this->assertTrue($notarioRole->hasPermissionTo('atestia.acuses.ver'));

        $colaboradorUser = \App\Models\User::factory()->create(['tenant_id' => $this->tenant->id, 'avatar' => 'avatar-1.jpg']);
        $colaboradorUser->assignRole('colaborador');
        $this->actingAs($colaboradorUser)->get('/atestia/avisos')->assertStatus(403);
        $this->actingAs($colaboradorUser)->get('/atestia/dashboard')->assertStatus(200);

        $notarioUser = \App\Models\User::factory()->create(['tenant_id' => $this->tenant->id, 'avatar' => 'avatar-1.jpg']);
        $notarioUser->assignRole('notario');
        $this->actingAs($notarioUser)->get('/atestia/avisos')->assertStatus(200);
    }

    /** T11 + T13: Auditor solo lectura registra acceso en bitácora y EBR <12m marca datos_proyectados */
    public function test_t11_t13_auditor_access_is_logged_and_ebr_marks_projected_data(): void
    {
        $auditorUser = \App\Models\User::factory()->create(['tenant_id' => $this->tenant->id, 'avatar' => 'avatar-1.jpg']);
        $auditorUser->assignRole('auditor_ro');

        $this->actingAs($auditorUser)->get('/atestia/auditoria')->assertStatus(200);
        $this->actingAs($auditorUser)->get('/atestia/avisos')->assertStatus(403);

        $this->assertDatabaseHas('atestia_bitacora_accesos', [
            'usuario_id'  => $auditorUser->id,
            'objeto_tipo' => 'paquete_auditoria',
            'accion'      => 'consulta_solo_lectura',
        ]);

        $report = app(\App\Services\Atestia\AtestiaNotariaEbrService::class)->generateInstitutionalReport($this->notaria);
        $this->assertTrue($report['datos_proyectados']);
    }

    /** Multi-tenant: super_admin crea notaría + administrador, y administrador crea operador aislado */
    public function test_multitenant_superadmin_and_client_profiles(): void
    {
        $super = \App\Models\User::factory()->create(['tenant_id' => null, 'avatar' => 'avatar-1.jpg']);
        $super->assignRole('super_admin');

        $this->actingAs($super)->post('/atestia/admin/notarias', [
            'nombre_comercial'   => 'Notaría 99',
            'rfc'                => 'NPN990101AAA',
            'titular'            => 'Lic. Ana Ruiz',
            'tipo_fedatario'     => 'NOTARIO',
            'perfil_servicio'    => 'pro',
            'admin_name'         => 'Admin 99',
            'admin_email'        => 'admin99@sentinelpld.mx',
            'admin_password'     => 'Secret1234',
        ])->assertRedirect('/atestia/admin/notarias');

        $admin99 = \App\Models\User::where('email', 'admin99@sentinelpld.mx')->firstOrFail();
        $this->assertTrue($admin99->hasRole('administrador'));

        // Administrador de cliente NO puede acceder a administración global de notarías
        $this->actingAs($admin99)->get('/atestia/admin/notarias')->assertStatus(403);

        // Administrador crea Operador en su propio tenant
        $this->actingAs($admin99)->post('/atestia/usuarios', [
            'name'     => 'Operador 99',
            'email'    => 'op99@sentinelpld.mx',
            'password' => 'Secret1234',
            'role'     => 'operador',
        ])->assertRedirect('/atestia/usuarios');

        $op99 = \App\Models\User::where('email', 'op99@sentinelpld.mx')->firstOrFail();
        $this->assertEquals($admin99->tenant_id, $op99->tenant_id);
        $this->actingAs($op99)->get('/atestia/avisos')->assertStatus(403);
        $this->actingAs($op99)->get('/atestia/dashboard')->assertStatus(200);

        // Super Admin tiene su propio dashboard y configuración Brevo cifrada; cliente recibe 403
        $this->actingAs($super)->get('/atestia/admin/dashboard')->assertStatus(200);
        $this->actingAs($admin99)->get('/atestia/admin/dashboard')->assertStatus(403);
        $this->actingAs($admin99)->get('/atestia/admin/configuracion')->assertStatus(403);

        $this->actingAs($super)->post('/atestia/admin/configuracion/brevo', [
            'brevo_enabled'      => 1,
            'brevo_api_key'      => 'xkeysib-test-secret',
            'brevo_sender_email' => 'notificaciones@sentinelpld.mx',
            'brevo_sender_name'  => 'Sentinel PLD',
        ])->assertRedirect('/atestia/admin/configuracion');

        $this->assertEquals('xkeysib-test-secret', \App\Models\SentinelSystemSetting::getValue('brevo_api_key'));

        // Administrador puede cambiar contraseña y eliminar operador de su propio tenant, pero NO al super_admin
        $this->actingAs($admin99)->patch("/atestia/usuarios/{$op99->id}/password", [
            'password' => 'NewSecret123',
        ])->assertRedirect('/atestia/usuarios');
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('NewSecret123', $op99->fresh()->password));

        $this->actingAs($admin99)->delete("/atestia/usuarios/{$super->id}")->assertStatus(403);
        $this->actingAs($admin99)->delete("/atestia/usuarios/{$op99->id}")->assertRedirect('/atestia/usuarios');
        $this->assertDatabaseMissing('users', ['id' => $op99->id]);
    }
}
