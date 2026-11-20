<?php

namespace App\Services\Atestia;

use App\Models\AtestiaActo;
use App\Models\AtestiaGradoRiesgo;
use App\Models\AtestiaNotaria;
use App\Models\AtestiaPago;

class AtestiaNotariaEbrService
{
    /**
     * Genera los insumos de 12 meses para la EBR institucional (M3) y calcula
     * el Nivel de Riesgo de la Notaría (Doc 02 §4.7).
     */
    public function generateInstitutionalReport(AtestiaNotaria $notaria): array
    {
        $desde = now()->subMonths(12)->toDateString();

        $primerActo = AtestiaActo::where('notaria_id', $notaria->id)
            ->orderBy('fecha_otorgamiento')
            ->first();

        // T13: Si la notaría tiene menos de 12 meses de datos, marca datos_proyectados=true
        $datosProyectados = ! $primerActo || $primerActo->fecha_otorgamiento->greaterThan(now()->subMonths(12));

        $actos = AtestiaActo::where('notaria_id', $notaria->id)
            ->whereDate('fecha_otorgamiento', '>=', $desde)
            ->get();

        $totalActos = max(1, $actos->count());
        $actosSiempreAviso = $actos->whereIn('tipo_inciso', ['b', 'c', 'e'])->count();
        $mezclaActos = round(($actosSiempreAviso / $totalActos) * 100, 2);

        $grados = AtestiaGradoRiesgo::where('notaria_id', $notaria->id)
            ->where('calculado_en', '>=', now()->subMonths(12))
            ->get();
        $totalGrados = max(1, $grados->count());
        $clientesAlto = $grados->where('valor', 'ALTO')->count();
        $mezclaClientes = round(($clientesAlto / $totalGrados) * 100, 2);

        $pagosEfectivoBloqueados = AtestiaPago::where('tenant_id', $notaria->tenant_id)
            ->where('bloqueado_art32', true)
            ->whereDate('fecha', '>=', $desde)
            ->count();
        $canalesYPagos = min(100.0, $pagosEfectivoBloqueados * 25.0);
        $exposicionGeografica = 15.0; // Base parametrizable por entidad federativa

        $indice = round(
            (0.30 * $mezclaActos) +
            (0.30 * $mezclaClientes) +
            (0.20 * $exposicionGeografica) +
            (0.20 * $canalesYPagos),
            2
        );

        $nivel = match (true) {
            $indice >= 65.0 => 'ALTO',
            $indice >= 35.0 => 'MEDIO',
            default         => 'BAJO',
        };

        return [
            'corte_desde'          => $desde,
            'datos_proyectados'    => $datosProyectados,
            'total_actos_12m'      => $actos->count(),
            'indice_notaria'       => $indice,
            'nivel_riesgo'         => $nivel,
            'auditoria_aplicable'  => $nivel === 'ALTO'
                ? 'Auditor Externo Independiente Certificado UIF (Art. 45)'
                : 'Auditoría Interna o Externa (Art. 44)',
            'componentes' => [
                'mezcla_actos'          => $mezclaActos,
                'mezcla_clientes'       => $mezclaClientes,
                'exposicion_geografica' => $exposicionGeografica,
                'canales_y_pagos'       => $canalesYPagos,
            ],
        ];
    }
}
