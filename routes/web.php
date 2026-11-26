<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Auth::routes();

Route::get('/mfa-verify', [App\Http\Controllers\Auth\MfaController::class, 'showMfaForm'])->name('mfa.show');
Route::post('/mfa-verify', [App\Http\Controllers\Auth\MfaController::class, 'verifyMfa'])->name('mfa.verify');
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password-required', [App\Http\Controllers\Auth\MfaController::class, 'showForcePasswordForm'])->name('password.force.show');
    Route::post('/change-password-required', [App\Http\Controllers\Auth\MfaController::class, 'updateForcePassword'])->name('password.force.update');
});

// Super Admin Panel Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\SuperAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/activity-logs', [App\Http\Controllers\SuperAdminController::class, 'activityLogs'])->name('activity-logs');
    Route::get('/audit-logs', [App\Http\Controllers\SuperAdminController::class, 'auditLogs'])->name('audit-logs');
    Route::get('/api-logs', [App\Http\Controllers\SuperAdminController::class, 'apiLogs'])->name('api-logs');
    Route::resource('tenants', App\Http\Controllers\SuperAdminController::class);
    Route::patch('users/{id}/toggle-status', [App\Http\Controllers\SuperAdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', App\Http\Controllers\SuperAdminUserController::class);

    // Catálogo Global y Centro de Control PLD/EBR (M1)
    Route::get('/pld-catalogo', [App\Http\Controllers\SuperAdmin\PldCatalogoController::class, 'index'])->name('pld-catalogo.index');
    Route::post('/pld-catalogo', [App\Http\Controllers\SuperAdmin\PldCatalogoController::class, 'store'])->name('pld-catalogo.store');
    Route::put('/pld-catalogo/{id}', [App\Http\Controllers\SuperAdmin\PldCatalogoController::class, 'update'])->name('pld-catalogo.update');
});

//Language Translation
Route::get('index/{locale}', [App\Http\Controllers\HomeController::class, 'lang']);

// Tenant Panel Routes (accessible to tenant_admin and investigador)
Route::middleware(['auth', 'role:tenant_admin|investigador'])->prefix('tenant')->name('tenant.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\TenantDashboardController::class, 'index'])->name('dashboard');
    Route::resource('projects', App\Http\Controllers\ProjectController::class);

    // Background check execution and report endpoints
    Route::post('/subjects/{id}/investigate', [App\Http\Controllers\InvestigationController::class, 'investigate'])->name('subjects.investigate');
    Route::post('/subjects/{id}/investigate/{source_type}', [App\Http\Controllers\InvestigationController::class, 'investigateSource'])->name('subjects.investigate.source');
    Route::get('/subjects/{id}/report', [App\Http\Controllers\ReportController::class, 'downloadReport'])->name('subjects.report');

    // Tablero, Visor y Acciones de Expedientes PLD/EBR (M6, M8)
    Route::get('/pld-expedientes', [App\Http\Controllers\Tenant\PldExpedienteWebController::class, 'index'])->name('pld.expedientes.index');
    Route::get('/pld-expedientes/{id}', [App\Http\Controllers\Tenant\PldExpedienteWebController::class, 'show'])->whereNumber('id')->name('pld.expedientes.show');
    Route::post('/pld-expedientes/{id}/monitoring', [App\Http\Controllers\Tenant\PldExpedienteWebController::class, 'toggleMonitoring'])->whereNumber('id')->name('pld.expedientes.monitoring');
    Route::post('/subjects/{id}/pld-expediente', [App\Http\Controllers\Tenant\PldExpedienteWebController::class, 'generate'])->name('subjects.pld-expediente.generate');
    Route::get('/pld-expedientes/{folio}/pdf', [App\Http\Controllers\Tenant\PldExpedienteWebController::class, 'downloadPdf'])->name('pld.expediente.pdf');
    Route::get('/pld-expedientes/{folio}/json', [App\Http\Controllers\Tenant\PldExpedienteWebController::class, 'downloadJson'])->name('pld.expediente.json');

    Route::post('/subjects/{id}/tier', [App\Http\Controllers\SubjectController::class, 'updateTierLevel'])->name('subjects.update-tier');
    Route::resource('subjects', App\Http\Controllers\SubjectController::class);

    // Tenant User Management & Consumption (exclusively for tenant_admin)
    Route::middleware(['role:tenant_admin'])->group(function () {
        Route::resource('users', App\Http\Controllers\TenantUserController::class);
        Route::get('/consumption', [App\Http\Controllers\TenantDashboardController::class, 'consumption'])->name('consumption');
        // Configuración del tenant: Términos y Condiciones de enrolamiento
        Route::get('/settings',  [App\Http\Controllers\TenantSettingsController::class, 'index']) ->name('settings');
        Route::post('/settings', [App\Http\Controllers\TenantSettingsController::class, 'update'])->name('settings.update');

        // Política de Puntuación, API Key y Webhooks PLD/EBR por Tenant (M1, M9)
        Route::get('/pld-policy', [App\Http\Controllers\Tenant\PldPolicyController::class, 'index'])->name('pld.policy');
        Route::post('/pld-policy', [App\Http\Controllers\Tenant\PldPolicyController::class, 'update'])->name('pld.policy.update');
        Route::get('/pld-integrations', [App\Http\Controllers\Tenant\PldPolicyController::class, 'integrations'])->name('pld.integrations');
        Route::post('/pld-policy/webhook', [App\Http\Controllers\Tenant\PldPolicyController::class, 'storeWebhook'])->name('pld.policy.webhook');
        Route::post('/pld-policy/rotate-key', [App\Http\Controllers\Tenant\PldPolicyController::class, 'rotateApiKey'])->name('pld.policy.rotate-key');
    });

    // Regenerar token de enrolamiento (tenant_admin e investigador)
    Route::post('/subjects/{id}/regenerate-enrollment',
        [App\Http\Controllers\SubjectController::class, 'regenerateEnrollment']
    )->name('subjects.regenerate-enrollment');

    // Servir documentos privados del sujeto (INE, selfie, consentimiento, comprobante)
    Route::get('/subjects/{id}/document/{type}',
        [App\Http\Controllers\SubjectController::class, 'serveDocument']
    )->name('subjects.document');

    // Subida manual de documentos desde el panel (reemplaza el archivo existente)
    Route::post('/subjects/{id}/document/{type}',
        [App\Http\Controllers\SubjectController::class, 'uploadDocument']
    )->name('subjects.document.upload');

    // Borrado de documentos (resetea enrolamiento si se borran los 3 principales)
    Route::delete('/subjects/{id}/document/{type}',
        [App\Http\Controllers\SubjectController::class, 'deleteDocument']
    )->name('subjects.document.delete');
});

// ─────────────────────────────────────────────────────────
// RUTAS SENTINEL PLD (/app) — Redirección 301 desde /atestia
// ─────────────────────────────────────────────────────────
Route::any('/atestia/{any?}', function (?string $any = null) {
    $target = '/app' . ($any ? '/' . ltrim($any, '/') : '');
    return redirect($target, 301);
})->where('any', '.*');

Route::middleware(['auth', 'password.changed'])->prefix('app')->name('atestia.')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Atestia\AtestiaDashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['permission:atestia.actos.registrar'])->group(function () {
        Route::get('/actos', [App\Http\Controllers\Atestia\AtestiaActoController::class, 'index'])->name('actos.index');
        Route::post('/actos', [App\Http\Controllers\Atestia\AtestiaActoController::class, 'store'])->name('actos.store');
        Route::post('/actos/{id}/confirm', [App\Http\Controllers\Atestia\AtestiaActoController::class, 'confirm'])->name('actos.confirm');
        Route::post('/actos/{id}/pagos', [App\Http\Controllers\Atestia\AtestiaActoController::class, 'storePago'])->name('actos.pagos.store');
    });

    Route::middleware(['permission:atestia.expedientes.ver'])->group(function () {
        Route::get('/clientes', [App\Http\Controllers\Atestia\AtestiaClienteController::class, 'index'])->name('clientes.index');
        Route::post('/clientes', [App\Http\Controllers\Atestia\AtestiaClienteController::class, 'store'])
            ->middleware('permission:atestia.expedientes.editar')
            ->name('clientes.store');
        Route::put('/clientes/{id}', [App\Http\Controllers\Atestia\AtestiaClienteController::class, 'update'])
            ->middleware('permission:atestia.expedientes.editar')
            ->name('clientes.update');
        Route::delete('/clientes/{id}', [App\Http\Controllers\Atestia\AtestiaClienteController::class, 'destroy'])
            ->middleware('permission:atestia.expedientes.editar')
            ->name('clientes.destroy');
    });

    Route::middleware(['permission:atestia.alertas.revisar'])->group(function () {
        Route::get('/alertas', [App\Http\Controllers\Atestia\AtestiaAlertaController::class, 'index'])->name('alertas.index');
        Route::post('/alertas/{id}/resolve', [App\Http\Controllers\Atestia\AtestiaAlertaController::class, 'resolve'])->name('alertas.resolve');
    });

    // Avisos y acuses: estrictamente prohibido para colaborador (Art. 63 fr. II LFPIORPI)
    Route::middleware(['permission:atestia.avisos.ver'])->group(function () {
        Route::get('/avisos', [App\Http\Controllers\Atestia\AtestiaAvisoController::class, 'index'])->name('avisos.index');
        Route::post('/avisos/{id}/acuse', [App\Http\Controllers\Atestia\AtestiaAvisoController::class, 'registerAcuse'])
            ->middleware('permission:atestia.avisos.presentar')
            ->name('avisos.acuse');
    });

    Route::middleware(['permission:atestia.auditoria.exportar'])->group(function () {
        Route::get('/auditoria', [App\Http\Controllers\Atestia\AtestiaAuditoriaController::class, 'index'])->name('auditoria.index');
    });

    Route::middleware(['permission:atestia.parametros.editar'])->group(function () {
        Route::get('/cumplimiento', [App\Http\Controllers\Atestia\AtestiaCumplimientoController::class, 'index'])->name('cumplimiento.index');
        Route::post('/cumplimiento', [App\Http\Controllers\Atestia\AtestiaCumplimientoController::class, 'store'])->name('cumplimiento.store');
        Route::get('/parametros', [App\Http\Controllers\Atestia\AtestiaParametroController::class, 'index'])->name('parametros.index');
        Route::put('/parametros/{id}', [App\Http\Controllers\Atestia\AtestiaParametroController::class, 'update'])->name('parametros.update');
    });

    Route::middleware(['permission:atestia.usuarios.gestionar'])->group(function () {
        Route::get('/usuarios', [App\Http\Controllers\Atestia\AtestiaUsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [App\Http\Controllers\Atestia\AtestiaUsuarioController::class, 'store'])->name('usuarios.store');
        Route::patch('/usuarios/{id}/toggle', [App\Http\Controllers\Atestia\AtestiaUsuarioController::class, 'toggle'])->name('usuarios.toggle');
        Route::patch('/usuarios/{id}/mfa', [App\Http\Controllers\Atestia\AtestiaUsuarioController::class, 'toggleMfa'])->name('usuarios.mfa');
        Route::patch('/usuarios/{id}/password', [App\Http\Controllers\Atestia\AtestiaUsuarioController::class, 'updatePassword'])->name('usuarios.password');
        Route::delete('/usuarios/{id}', [App\Http\Controllers\Atestia\AtestiaUsuarioController::class, 'destroy'])->name('usuarios.destroy');
    });

    Route::middleware(['permission:atestia.tenants.gestionar'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Atestia\SuperAdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/notarias', [App\Http\Controllers\Atestia\AtestiaAdminNotariaController::class, 'index'])->name('notarias.index');
        Route::post('/notarias', [App\Http\Controllers\Atestia\AtestiaAdminNotariaController::class, 'store'])->name('notarias.store');
        Route::patch('/notarias/{id}/toggle', [App\Http\Controllers\Atestia\AtestiaAdminNotariaController::class, 'toggle'])->name('notarias.toggle');
        Route::get('/configuracion', [App\Http\Controllers\Atestia\SystemSettingsController::class, 'index'])->name('configuracion.index');
        Route::post('/configuracion/brevo', [App\Http\Controllers\Atestia\SystemSettingsController::class, 'updateBrevo'])->name('configuracion.brevo');
        Route::post('/configuracion/brevo/test', [App\Http\Controllers\Atestia\SystemSettingsController::class, 'testBrevo'])->name('configuracion.brevo.test');
    });
});

Route::get('/', [App\Http\Controllers\HomeController::class, 'root'])->name('root');
Route::get('/landing', [App\Http\Controllers\LandingController::class, 'index'])->name('landing');
Route::post('/contacto', [App\Http\Controllers\LandingController::class, 'storeContact'])->name('contacto.store');

//Update User Details
Route::post('/update-profile/{id}', [App\Http\Controllers\HomeController::class, 'updateProfile'])->name('updateProfile');
Route::post('/update-password/{id}', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('updatePassword');

// ─────────────────────────────────────────────────────────
// RUTAS PÚBLICAS — Enrolamiento del Investigado (sin auth)
// Seguridad basada en token UUID + expiración 24h
// ─────────────────────────────────────────────────────────
// Ruta ultra-corta de enrolamiento: /e/{token}
Route::get('/e/{token}', [App\Http\Controllers\EnrollmentController::class, 'show'])->middleware('throttle:10,1')->name('enroll.short');

Route::prefix('enroll')->name('enroll.')->middleware('throttle:10,1')->group(function () {
    Route::get('/{token}',             [App\Http\Controllers\EnrollmentController::class, 'show'])        ->name('show');
    Route::post('/{token}/accept-tc',  [App\Http\Controllers\EnrollmentController::class, 'acceptTerms']) ->name('accept-tc');
    Route::post('/{token}/upload',     [App\Http\Controllers\EnrollmentController::class, 'upload'])      ->name('upload');
    Route::post('/{token}/liveness-session', [App\Http\Controllers\EnrollmentController::class, 'startLivenessSession'])->name('liveness-session');
    Route::get('/{token}/liveness-mock',     [App\Http\Controllers\EnrollmentController::class, 'livenessMock'])         ->name('liveness-mock');
    Route::get('/{token}/liveness-done',     [App\Http\Controllers\EnrollmentController::class, 'livenessDone'])         ->name('liveness-done');
    Route::get('/{token}/done',              [App\Http\Controllers\EnrollmentController::class, 'done'])                 ->name('done');
});

Route::get('{any}', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
