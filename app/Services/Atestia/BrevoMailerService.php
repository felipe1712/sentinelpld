<?php

namespace App\Services\Atestia;

use App\Models\SentinelSystemSetting;
use Illuminate\Support\Facades\Http;

class BrevoMailerService
{
    /**
     * Envía un correo transaccional usando la API v3 de Brevo configurada en el panel Super Admin.
     */
    public function send(string $toEmail, string $toName, string $subject, string $htmlContent): array
    {
        $enabled = filter_var(SentinelSystemSetting::getValue('brevo_enabled', '0'), FILTER_VALIDATE_BOOLEAN);
        $apiKey = (string) SentinelSystemSetting::getValue('brevo_api_key', '');
        $senderEmail = (string) SentinelSystemSetting::getValue('brevo_sender_email', 'notificaciones@sentinelpld.mx');
        $senderName = (string) SentinelSystemSetting::getValue('brevo_sender_name', config('app.name'));

        if (! $enabled || $apiKey === '') {
            return ['ok' => false, 'message' => 'La integración con Brevo no está habilitada o falta la API Key.'];
        }

        $response = Http::withHeaders([
            'api-key' => $apiKey,
            'accept'  => 'application/json',
        ])->timeout(10)->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'name'  => $senderName,
                'email' => $senderEmail,
            ],
            'to' => [
                ['email' => $toEmail, 'name' => $toName],
            ],
            'subject'     => $subject,
            'htmlContent' => $htmlContent,
        ]);

        if ($response->successful()) {
            return ['ok' => true, 'message' => 'Correo enviado correctamente vía Brevo.'];
        }

        return [
            'ok'      => false,
            'message' => 'Error Brevo (' . $response->status() . '): ' . $response->body(),
        ];
    }
}
