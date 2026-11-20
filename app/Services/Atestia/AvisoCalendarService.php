<?php

namespace App\Services\Atestia;

use App\Models\AtestiaActo;
use App\Models\AtestiaAviso;
use App\Models\AtestiaNotaria;
use App\Models\AtestiaParametro;
use Carbon\Carbon;

class AvisoCalendarService
{
    /**
     * Genera o programa el borrador de aviso mensual (día 17 del mes siguiente)
     * respetando el candado del Anexo 12-A (Transitorio Sexto fr. III).
     */
    public function scheduleMonthlyAviso(AtestiaActo $acto, string $canal): AtestiaAviso
    {
        $fechaActo = Carbon::parse($acto->fecha_protocolizacion ?? $acto->fecha_otorgamiento);
        $fechaLimite = $fechaActo->copy()->addMonthNoOverflow()->day(17);

        $notaria = AtestiaNotaria::find($acto->notaria_id);
        if ($notaria && ! empty($notaria->entidad_colegiada) && $canal !== 'fiscal') {
            $canal = 'entidad_colegiada';
        }

        $anexo12a = strtolower((string) AtestiaParametro::getValor('anexo_12a_actualizado', 'desconocido'));
        $estado = in_array($anexo12a, ['si', 'true', '1'], true)
            ? 'borrador'
            : 'pendiente_de_formato';

        return AtestiaAviso::create([
            'tenant_id'    => $acto->tenant_id,
            'notaria_id'   => $acto->notaria_id,
            'acto_ids'     => [$acto->id],
            'tipo'         => 'mensual',
            'canal'        => $canal,
            'fecha_limite' => $fechaLimite->toDateString(),
            'estado'       => $estado,
        ]);
    }

    /**
     * Indica si el envío de avisos 24h está vigente según la fecha de formatos.
     */
    public function is24hAvisoActive(): bool
    {
        $vigenteDesde = (string) AtestiaParametro::getValor('formatos_24h_vigente_desde', '');
        if ($vigenteDesde === '') {
            return false;
        }

        return now()->greaterThanOrEqualTo(Carbon::parse($vigenteDesde));
    }
}
