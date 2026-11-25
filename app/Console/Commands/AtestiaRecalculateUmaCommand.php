<?php

namespace App\Console\Commands;

use App\Models\AtestiaActo;
use App\Models\AtestiaAlerta;
use App\Models\AtestiaAviso;
use App\Models\AtestiaCliente;
use App\Services\Atestia\ActoThresholdAndAccumulationService;
use App\Services\Atestia\CashMonitorService;
use Illuminate\Console\Command;

class AtestiaRecalculateUmaCommand extends Command
{
    protected $signature = 'atestia:recalculate-uma {--cleanup-prueba : Elimina registros de prueba PRUEBA-*}';

    protected $description = 'Recalcula actos existentes con la UMA vigente por fecha de otorgamiento e invalida alertas/avisos falsos (F-01, F-14, F-17).';

    public function handle(ActoThresholdAndAccumulationService $thresholdSvc, CashMonitorService $cashSvc): int
    {
        $actos = AtestiaActo::with('pagos')->get();
        $recalculados = 0;
        $alertasCerradas = 0;
        $avisosEliminados = 0;

        foreach ($actos as $acto) {
            $eval = $thresholdSvc->evaluateActo($acto);
            $recalculados++;

            if (! $eval['requiere_aviso']) {
                // Eliminar borradores de avisos generados falsamente por UMA 2025
                $avisos = AtestiaAviso::where('tenant_id', $acto->tenant_id)
                    ->whereIn('estado', ['borrador', 'pendiente_de_formato'])
                    ->get();

                foreach ($avisos as $aviso) {
                    $ids = (array) $aviso->acto_ids;
                    if (in_array($acto->id, $ids, true)) {
                        $restantes = array_values(array_diff($ids, [$acto->id]));
                        if (empty($restantes)) {
                            $aviso->delete();
                            $avisosEliminados++;
                        } else {
                            $aviso->update(['acto_ids' => $restantes]);
                        }
                    }
                }
            }

            // Revalidar alertas A08/A09 si el acto estaba bloqueado o bajo el umbral real
            $acto->refresh();
            $umbralArt32 = ($acto->apartado === 'A' && $acto->tipo_inciso === 'a') ? 8025.0 : 3210.0;
            if ((float) $acto->valor_en_umas < $umbralArt32 && $acto->estado === 'bloqueado_efectivo') {
                $acto->update(['estado' => 'evaluado']);
                $acto->pagos()->update(['bloqueado_art32' => false]);

                $alertasCerradas += AtestiaAlerta::where('acto_id', $acto->id)
                    ->whereIn('regla_id', ['A08', 'A09'])
                    ->whereIn('estado', ['nueva', 'en_revision'])
                    ->update([
                        'estado'           => 'descartada',
                        'decision'         => 'descartada',
                        'analisis_escrito' => 'Invalidada automáticamente por recálculo con UMA vigente según fecha de otorgamiento.',
                        'cerrada_en'       => now(),
                    ]);
            }
        }

        if ($this->option('cleanup-prueba')) {
            $clientesPrueba = AtestiaCliente::where('nombre_razon_social', 'like', 'PRUEBA%')->pluck('id');
            $actosPrueba = AtestiaActo::where('instrumento_no', 'like', 'PRUEBA-%')->pluck('id');

            AtestiaAlerta::whereIn('acto_id', $actosPrueba)->orWhereIn('cliente_id', $clientesPrueba)->delete();
            AtestiaActo::whereIn('id', $actosPrueba)->delete();
            AtestiaCliente::whereIn('id', $clientesPrueba)->delete();
            $this->info('Registros de prueba PRUEBA-* eliminados.');
        }

        $this->info("Actos recalculados: {$recalculados}. Alertas invalidadas: {$alertasCerradas}. Avisos falsos eliminados: {$avisosEliminados}.");

        return self::SUCCESS;
    }
}
