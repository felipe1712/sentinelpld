<?php

namespace App\Services\Atestia;

use App\Models\AtestiaActo;
use App\Models\AtestiaParametro;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ActoThresholdAndAccumulationService
{
    /**
     * Calcula el monto computable, su equivalente en UMAs y determina si
     * el acto genera obligación de aviso por sí mismo o por acumulación (6 meses).
     */
    public function evaluateActo(AtestiaActo $acto): array
    {
        $umaValor = (float) AtestiaParametro::getValor('uma_diaria_mxn', 113.14);
        $baseMax = max(
            (float) $acto->valor_precio,
            (float) $acto->valor_catastral,
            (float) $acto->valor_comercial,
            (float) $acto->monto_garantia
        );

        // Reglamento Art. 6: sin contribuciones ni accesorios para el cómputo del umbral
        $computable = max(0.0, $baseMax - (float) $acto->contribuciones_accesorios);
        $umas = $umaValor > 0 ? round($computable / $umaValor, 2) : 0.0;

        $acto->update([
            'monto_computable_umbral' => $computable,
            'uma_valor_aplicado'      => $umaValor,
            'valor_en_umas'           => $umas,
        ]);

        $regla = config("atestia.catalogo_actos.{$acto->apartado}.{$acto->tipo_inciso}");
        if (! $regla || $acto->excepcion_financiera) {
            return $this->result($umas, false, false, false, null);
        }

        // B-a (Corredores): solo aplica si se utiliza fe pública en el avalúo (Reglamento Art. 29)
        if ($acto->apartado === 'B' && $acto->tipo_inciso === 'a' && ! $acto->fe_publica_en_avaluo) {
            return $this->result($umas, false, false, false, null);
        }

        if ($regla['siempre_aviso']) {
            return $this->result($umas, true, false, false, $regla['canal']);
        }

        $umbralUmas = (float) AtestiaParametro::getValor($regla['umbral_clave'], 0);
        if ($umbralUmas > 0 && $umas >= $umbralUmas) {
            return $this->result($umas, true, false, false, $regla['canal']);
        }

        // Zona de posible fraccionamiento (80% a 99% del umbral) -> Alerta A06
        $cercaUmbral = $umbralUmas > 0 && $umas >= ($umbralUmas * 0.80) && $umas < $umbralUmas;

        // Acumulación a 6 meses (Acuerdo Art. 19: excluye A-b, A-e y todo Apartado B)
        $acumulaAlcanza = false;
        if ($regla['acumula'] && $umbralUmas > 0) {
            $acumulaAlcanza = $this->checkSixMonthAccumulation($acto, $umbralUmas, $umaValor);
        }

        return $this->result($umas, $acumulaAlcanza, $acumulaAlcanza, $cercaUmbral, $regla['canal']);
    }

    protected function checkSixMonthAccumulation(AtestiaActo $acto, float $umbralUmas, float $umaValor): bool
    {
        $ventanaMeses = (int) AtestiaParametro::getValor('ventana_acumulacion_meses', 6);
        $fechaRef = Carbon::parse($acto->fecha_otorgamiento);
        $desde = $fechaRef->copy()->subMonths($ventanaMeses)->toDateString();
        $hasta = $fechaRef->toDateString();

        $clienteIds = $acto->partes()->pluck('cliente_id');
        if ($clienteIds->isEmpty()) {
            return false;
        }

        foreach ($clienteIds as $clienteId) {
            $sumaMxn = DB::table('atestia_actos as a')
                ->join('atestia_acto_partes as ap', 'ap.acto_id', '=', 'a.id')
                ->where('a.tenant_id', $acto->tenant_id)
                ->where('ap.cliente_id', $clienteId)
                ->where('a.apartado', $acto->apartado)
                ->where('a.tipo_inciso', $acto->tipo_inciso)
                ->where('a.excepcion_financiera', false)
                ->whereNotIn('a.estado', ['abstencion'])
                ->whereDate('a.fecha_otorgamiento', '>=', $desde)
                ->whereDate('a.fecha_otorgamiento', '<=', $hasta)
                ->sum('a.monto_computable_umbral');

            $sumaUmas = $umaValor > 0 ? ($sumaMxn / $umaValor) : 0.0;
            if ($sumaUmas >= $umbralUmas) {
                return true;
            }
        }

        return false;
    }

    protected function result(
        float $umas,
        bool $requiereAviso,
        bool $porAcumulacion,
        bool $cercaUmbral,
        ?string $canal
    ): array {
        return [
            'valor_en_umas'   => $umas,
            'requiere_aviso'  => $requiereAviso,
            'por_acumulacion' => $porAcumulacion,
            'cerca_umbral'    => $cercaUmbral,
            'canal'           => $canal,
        ];
    }
}
