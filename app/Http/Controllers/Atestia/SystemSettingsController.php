<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\SentinelSystemSetting;
use App\Services\Atestia\BrevoMailerService;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $brevo = [
            'enabled'      => filter_var(SentinelSystemSetting::getValue('brevo_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
            'has_api_key'  => ! empty(SentinelSystemSetting::getValue('brevo_api_key', '')),
            'sender_email' => SentinelSystemSetting::getValue('brevo_sender_email', 'notificaciones@sentinelpld.mx'),
            'sender_name'  => SentinelSystemSetting::getValue('brevo_sender_name', config('app.name')),
        ];

        return view('atestia.admin.configuracion.index', compact('brevo'));
    }

    public function updateBrevo(Request $request)
    {
        $data = $request->validate([
            'brevo_enabled'      => 'nullable|boolean',
            'brevo_api_key'      => 'nullable|string|max:255',
            'brevo_sender_email' => 'required|email|max:255',
            'brevo_sender_name'  => 'required|string|max:255',
        ]);

        SentinelSystemSetting::setValue('brevo_enabled', ! empty($data['brevo_enabled']) ? '1' : '0', false, 'brevo');
        SentinelSystemSetting::setValue('brevo_sender_email', $data['brevo_sender_email'], false, 'brevo');
        SentinelSystemSetting::setValue('brevo_sender_name', $data['brevo_sender_name'], false, 'brevo');

        if (! empty($data['brevo_api_key'])) {
            SentinelSystemSetting::setValue('brevo_api_key', trim($data['brevo_api_key']), true, 'brevo');
        }

        return redirect()->route('atestia.admin.configuracion.index')
            ->with('success', 'Configuración de Brevo guardada correctamente.');
    }

    public function testBrevo(Request $request, BrevoMailerService $mailer)
    {
        $data = $request->validate([
            'test_email' => 'required|email',
        ]);

        $res = $mailer->send(
            $data['test_email'],
            'Prueba Sentinel PLD',
            'Prueba de integración Brevo — ' . config('app.name'),
            '<p>La integración de correo transaccional con <strong>Brevo</strong> en <strong>' . e(config('app.name')) . '</strong> está funcionando correctamente.</p>'
        );

        return redirect()->route('atestia.admin.configuracion.index')
            ->with($res['ok'] ? 'success' : 'error', $res['message']);
    }
}
