<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaNotaria;
use App\Models\SentinelSystemSetting;
use App\Models\User;

class SuperAdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'notarias_activas'     => AtestiaNotaria::withoutGlobalScopes()->where('activo', true)->count(),
            'notarias_suspendidas' => AtestiaNotaria::withoutGlobalScopes()->where('activo', false)->count(),
            'usuarios_totales'     => User::count(),
            'plan_pro'             => AtestiaNotaria::withoutGlobalScopes()->where('perfil_servicio', 'pro')->count(),
            'plan_lite'            => AtestiaNotaria::withoutGlobalScopes()->where('perfil_servicio', 'lite')->count(),
            'brevo_activo'         => filter_var(SentinelSystemSetting::getValue('brevo_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
        ];

        $ultimasNotarias = AtestiaNotaria::withoutGlobalScopes()
            ->with('tenant')
            ->latest()
            ->limit(8)
            ->get();

        return view('atestia.admin.dashboard', compact('stats', 'ultimasNotarias'));
    }
}
