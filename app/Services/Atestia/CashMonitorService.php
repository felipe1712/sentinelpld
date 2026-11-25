<?php

namespace App\Services\Atestia;

use App\Models\AtestiaActo;
use App\Models\AtestiaAlerta;
use App\Models\AtestiaPago;
use App\Models\AtestiaParametro;
use Carbon\Carbon;

class CashMonitorService
{
    /**
     * Valida un pago contra las prohibiciones de efectivo (Art. 32) y
     * obligación de identificar forma de pago (Art. 33).
     */
    public function validatePago(AtestiaActo $acto, AtestiaPago $pago): array
    {
        $fechaRef = $acto->fecha_otorgamiento ? Carbon::parse($acto->fecha_otorgamiento) : now();
        $umbralEfectivo = $this->resolveArt32Threshold($acto, $fechaRef);
        $umbralArt33 = (float) AtestiaParametro::getValor('art33_forma_pago_umas', 8025, $fechaRef);
        $umaAplicada = (float) ($acto->uma_valor_aplicado ?: AtestiaParametro::getValor('uma_diaria_mxn', 117.31, $fechaRef));

        $bloqueado = false;
        $faltaComprobante = false;

        if ($pago->forma === 'efectivo' && (float) $acto->valor_en_umas >= $umbralEfectivo) {
            $bloqueado = true;
            $pago->update(['bloqueado_art32' => true]);
            $acto->update(['estado' => 'bloqueado_efectivo']);

            AtestiaAlerta::create([
                'tenant_id'    => $acto->tenant_id,
                'notaria_id'   => $acto->notaria_id,
                'acto_id'      => $acto->id,
                'regla_id'     => 'A08',
                'es_orpi'      => false,
                'severidad'    => 'CRITICA',
                'estado'       => 'nueva',
                'titulo'       => 'Intento de pago en efectivo en supuesto prohibido (Art. 32 LFPIORPI)',
                'detalle_json' => [
                    'pago_id'       => $pago->id,
                    'monto'         => $pago->monto,
                    'valor_en_umas' => $acto->valor_en_umas,
                    'umbral_art32'  => $umbralEfectivo,
                    'uma_aplicada'  => $umaAplicada,
                ],
                'sla_vence_en' => now()->endOfDay(),
            ]);
        }

        if ((float) $acto->valor_en_umas >= $umbralArt33 && empty($pago->comprobante_ref)) {
            $faltaComprobante = true;
            AtestiaAlerta::create([
                'tenant_id'    => $acto->tenant_id,
                'notaria_id'   => $acto->notaria_id,
                'acto_id'      => $acto->id,
                'regla_id'     => 'A09',
                'es_orpi'      => false,
                'severidad'    => 'ALTA',
                'estado'       => 'nueva',
                'titulo'       => 'Forma de pago sin comprobante en acto >= 8,025 UMA (Art. 33 LFPIORPI)',
                'detalle_json' => [
                    'pago_id'       => $pago->id,
                    'forma'         => $pago->forma,
                    'valor_en_umas' => $acto->valor_en_umas,
                    'umbral_art33'  => $umbralArt33,
                    'uma_aplicada'  => $umaAplicada,
                ],
                'sla_vence_en' => now()->addWeekdays(2),
            ]);
        }

        return [
            'bloqueado_art32'   => $bloqueado,
            'falta_comprobante' => $faltaComprobante,
            'valor_en_umas'     => (float) $acto->valor_en_umas,
            'umbral_art32'      => $umbralEfectivo,
            'uma_aplicada'      => $umaAplicada,
        ];
    }

    protected function resolveArt32Threshold(AtestiaActo $acto, Carbon $fechaRef): float
    {
        if ($acto->apartado === 'A' && $acto->tipo_inciso === 'a') {
            return (float) AtestiaParametro::getValor('art32_efectivo_inmuebles_umas', 8025, $fechaRef);
        }

        return (float) AtestiaParametro::getValor('art32_efectivo_otros_umas', 3210, $fechaRef);
    }
}
