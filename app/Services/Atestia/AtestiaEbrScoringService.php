<?php

namespace App\Services\Atestia;

use App\Models\AtestiaActo;
use App\Models\AtestiaAlerta;
use App\Models\AtestiaCliente;
use App\Models\AtestiaGradoRiesgo;
use App\Models\AtestiaPerfilTransaccional;

class AtestiaEbrScoringService
{
    public const METODOLOGIA_VERSION = 'ebr-notaria-v1.0';

    /**
     * Pesos de los 4 elementos normativos (Acuerdo 115/2026 Art. 10 Septies 1).
     */
    private const ELEMENT_WEIGHTS = [
        'actos'     => 0.25,
        'clientes'  => 0.35,
        'geografia' => 0.20,
        'canales'   => 0.20,
    ];

    /**
     * Pesos internos de indicadores por elemento (Doc 02 §4.3).
     */
    private const INDICATOR_WEIGHTS = [
        'actos' => [
            'tipo_acto'            => 25,
            'valor_acto'           => 20,
            'coherencia_valor'     => 20,
            'reventa_secuencia'    => 15,
            'estructura_compleja'  => 10,
            'financiamiento_ft'    => 10,
        ],
        'clientes' => [
            'tipo_antiguedad'      => 15,
            'pep'                  => 20,
            'beneficiario_ctrl'    => 15,
            'actividad_origen'     => 15,
            'antecedentes_listas'  => 20,
            'conducta_tramite'     => 10,
            'representacion'       => 5,
        ],
        'geografia' => [
            'residencia'           => 30,
            'nacionalidad'         => 15,
            'ubicacion_bien'       => 25,
            'origen_destino'       => 20,
            'jurisdiccion_ft'      => 10,
        ],
        'canales' => [
            'forma_pago'           => 30,
            'pagos_terceros'       => 20,
            'fraccionamiento'      => 20,
            'canal_contratacion'   => 15,
            'flujos_ft'            => 15,
        ],
    ];

    /**
     * Calcula y persiste el Grado de Riesgo del cliente (inmutable, retención >= 10 años).
     *
     * @param  array<string, array<string, int|null>>  $indicadores  Valores 0..3 o null (dato faltante = 2)
     * @param  array<string, array{eficacia: float, evidencia: ?string}>  $mitigantes
     * @param  array<string, bool>  $reglasDurasFlags
     */
    public function evaluateCliente(
        AtestiaCliente $cliente,
        array $indicadores,
        array $mitigantes = [],
        array $reglasDurasFlags = [],
        ?AtestiaActo $acto = null,
        string $disparadoPor = 'alta',
        string $metodologiaVer = self::METODOLOGIA_VERSION
    ): AtestiaGradoRiesgo {
        $inherentes = [];
        $mitigantesAplicados = [];
        $residuales = [];
        $factoresDetalle = [];
        $datosFaltantes = [];

        foreach (self::INDICATOR_WEIGHTS as $elemento => $pesos) {
            $sumaPonderada = 0.0;
            $sumaPesos = 0.0;

            foreach ($pesos as $clave => $peso) {
                $raw = $indicadores[$elemento][$clave] ?? null;
                if ($raw === null) {
                    // Regla Doc 02 §4.4 & T15: dato faltante puntúa 2 (alto)
                    $valor = 2;
                    $datosFaltantes[] = "{$elemento}.{$clave}";
                    $nota = 'dato no obtenido';
                } else {
                    $valor = max(0, min(3, (int) $raw));
                    $nota = null;
                }

                $sumaPonderada += ($valor * $peso);
                $sumaPesos += $peso;

                $factoresDetalle[$elemento][$clave] = [
                    'valor' => $valor,
                    'peso'  => $peso,
                    'nota'  => $nota,
                ];
            }

            $inherente = $sumaPesos > 0
                ? round(($sumaPonderada / (3.0 * $sumaPesos)) * 100.0, 2)
                : 0.0;

            // Mitigante: máximo 0.30, y vale 0 si no tiene evidencia de aplicación efectiva
            $mitInfo = $mitigantes[$elemento] ?? ['eficacia' => 0.0, 'evidencia' => null];
            $eficacia = ! empty($mitInfo['evidencia'])
                ? max(0.0, min(0.30, (float) ($mitInfo['eficacia'] ?? 0.0)))
                : 0.0;

            $residual = round($inherente * (1.0 - $eficacia), 2);

            $inherentes[$elemento] = $inherente;
            $mitigantesAplicados[$elemento] = [
                'eficacia'  => $eficacia,
                'evidencia' => $mitInfo['evidencia'] ?? null,
            ];
            $residuales[$elemento] = $residual;
        }

        $puntaje = round(
            (self::ELEMENT_WEIGHTS['actos'] * $residuales['actos']) +
            (self::ELEMENT_WEIGHTS['clientes'] * $residuales['clientes']) +
            (self::ELEMENT_WEIGHTS['geografia'] * $residuales['geografia']) +
            (self::ELEMENT_WEIGHTS['canales'] * $residuales['canales']),
            2
        );

        $grado = match (true) {
            $puntaje >= 65.0 => 'ALTO',
            $puntaje >= 35.0 => 'MEDIO',
            default          => 'BAJO',
        };

        $reglaForzada = $this->applyHardRules($cliente, $reglasDurasFlags, $grado, $acto);
        if ($reglaForzada !== null) {
            $grado = $reglaForzada === 'PEP_NACIONAL_MIN_MEDIO' && $grado === 'BAJO'
                ? 'MEDIO'
                : ($reglaForzada === 'PEP_NACIONAL_MIN_MEDIO' ? $grado : 'ALTO');
        }

        if (! empty($datosFaltantes)) {
            $factoresDetalle['_datos_faltantes'] = $datosFaltantes;
        }

        return AtestiaGradoRiesgo::create([
            'tenant_id'       => $cliente->tenant_id,
            'notaria_id'      => $cliente->notaria_id,
            'cliente_id'      => $cliente->id,
            'acto_id'         => $acto?->id,
            'valor'           => $grado,
            'puntaje'         => $puntaje,
            'inherentes_json' => $inherentes,
            'mitigantes_json' => $mitigantesAplicados,
            'residuales_json' => $residuales,
            'factores_json'   => $factoresDetalle,
            'regla_forzada'   => $reglaForzada,
            'metodologia_ver' => $metodologiaVer,
            'disparado_por'   => $disparadoPor,
            'calculado_en'    => now(),
        ]);
    }

    /**
     * Calcula y registra el perfil transaccional del cliente (Doc 02 §4.6).
     */
    public function updatePerfilTransaccional(
        AtestiaCliente $cliente,
        string $base,
        float $montoMaxMensual,
        int $frecuenciaMensual = 1,
        array $detalle = []
    ): AtestiaPerfilTransaccional {
        return AtestiaPerfilTransaccional::create([
            'tenant_id'                   => $cliente->tenant_id,
            'notaria_id'                  => $cliente->notaria_id,
            'cliente_id'                  => $cliente->id,
            'base'                        => $base, // acto_unico | estimado_6m | historico
            'monto_max_mensual'           => $montoMaxMensual,
            'frecuencia_mensual_estimada' => $frecuenciaMensual,
            'detalle_json'                => $detalle,
            'calculado_en'                => now(),
        ]);
    }

    protected function applyHardRules(
        AtestiaCliente $cliente,
        array $flags,
        string $gradoActual,
        ?AtestiaActo $acto
    ): ?string {
        if (! empty($flags['lista_uif_coincidencia'])) {
            $this->createHardAlert($cliente, $acto, 'A03', 'CRITICA', 'Coincidencia en listado UIF (Art. 27 / 38 Acuerdo)');
            return 'LISTA_UIF_ALTO_24H';
        }

        if (! empty($flags['pep_extranjera'])) {
            $this->createHardAlert($cliente, $acto, 'A02', 'ALTA', 'Cliente o vinculado PEP Extranjera (Art. 23 Bis 4 / Ter 5)');
            return 'PEP_EXTRANJERA_ALTO';
        }

        if (! empty($flags['no_residente_jurisdiccion_uif'])) {
            $this->createHardAlert($cliente, $acto, 'A04', 'ALTA', 'No residente vinculado a jurisdicción en lista UIF');
            return 'JURISDICCION_UIF_ALTO';
        }

        if (! empty($flags['efectivo_art32'])) {
            return 'EFECTIVO_PROHIBIDO_ART32';
        }

        if (! empty($flags['negativa_informacion'])) {
            return 'NEGATIVA_ABSTENCION_ART21';
        }

        if (! empty($flags['pep_nacional'])) {
            $this->createHardAlert($cliente, $acto, 'A02', 'ALTA', 'Cliente o vinculado PEP Nacional (Art. 23 Bis 3)');
            return 'PEP_NACIONAL_MIN_MEDIO';
        }

        return null;
    }

    protected function createHardAlert(
        AtestiaCliente $cliente,
        ?AtestiaActo $acto,
        string $reglaId,
        string $severidad,
        string $titulo
    ): void {
        AtestiaAlerta::create([
            'tenant_id'    => $cliente->tenant_id,
            'notaria_id'   => $cliente->notaria_id,
            'acto_id'      => $acto?->id,
            'cliente_id'   => $cliente->id,
            'regla_id'     => $reglaId,
            'es_orpi'      => false,
            'severidad'    => $severidad,
            'estado'       => 'nueva',
            'titulo'       => $titulo,
            'detalle_json' => ['origen' => 'motor_ebr'],
            'sla_vence_en' => $severidad === 'CRITICA' ? now()->endOfDay() : now()->addWeekdays(2),
        ]);
    }
}
