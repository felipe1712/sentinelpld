<?php

namespace App\Services\Atestia;

use App\Models\AtestiaActo;
use App\Models\AtestiaAlerta;
use App\Models\AtestiaPago;
use App\Models\AtestiaParametro;

class CashMonitorService
{
    /**
     * Valida un pago contra las prohibiciones de efectivo (Art. 32) y
     * obligación de identificar forma de pago (Art. 33).
     */
    public function validatePago(AtestiaActo $acto, AtestiaPago $pago): array
    {
        $umbralEfectivo = $this->resolveArt32Threshold($acto);
        $umbralArt33 = (float) AtestiaParametro::getValor('art33_forma_pago_umas', 8025);

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
                    'pago_id'        => $pago->id,
                    'monto'          => $pago->monto,
                    'valor_en_umas'  => $acto->valor_en_umas,
                    'umbral_art32'   => $umbralEfectivo,
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
                ],
                'sla_vence_en' => now()->addWeekdays(2),
            ]);
        }

        return [
            'bloqueado_art32'  => $bloqueado,
            'falta_comprobante' => $faltaComprobante,
        ];
    }

    protected function resolveArt32Threshold(AtestiaActo $acto): float
    {
        if ($acto->apartado === 'A' && $acto->tipo_inciso === 'a') {
            return (float) AtestiaParametro::getValor('art32_efectivo_inmuebles_umas', 8025);
        }

        return (float) AtestiaParametro::getValor('art32_efectivo_otros_umas', 3210);
    }
}
