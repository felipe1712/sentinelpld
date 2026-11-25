<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atestia_parametros', function (Blueprint $table) {
            $table->dropUnique(['clave']);
            $table->unique(['clave', 'vigente_desde'], 'atestia_parametros_clave_vigencia_unique');
        });

        $now = now();

        // Actualizar cualquier registro 2026-02-01 que haya quedado con 113.14
        DB::table('atestia_parametros')
            ->where('clave', 'uma_diaria_mxn')
            ->where('vigente_desde', '2026-02-01')
            ->update([
                'valor'         => '117.31',
                'vigente_hasta' => null,
                'fuente'        => 'INEGI (DOF 9-ene-2026, vigente desde 1-feb-2026)',
                'descripcion'   => 'Valor diario de la UMA en MXN vigente para cálculo de umbrales LFPIORPI.',
                'updated_at'    => $now,
            ]);

        // Asegurar registro histórico UMA 2025 (hasta 31-ene-2026)
        DB::table('atestia_parametros')->updateOrInsert(
            ['clave' => 'uma_diaria_mxn', 'vigente_desde' => '2025-02-01'],
            [
                'valor'         => '113.14',
                'tipo'          => 'decimal',
                'vigente_hasta' => '2026-01-31',
                'fuente'        => 'INEGI (DOF 10-ene-2025, vigente hasta 31-ene-2026)',
                'descripcion'   => 'Valor diario de la UMA en MXN vigente del 1-feb-2025 al 31-ene-2026.',
                'created_at'    => $now,
                'updated_at'    => $now,
            ]
        );

        // Asegurar registro vigente UMA 2026 (desde 1-feb-2026)
        DB::table('atestia_parametros')->updateOrInsert(
            ['clave' => 'uma_diaria_mxn', 'vigente_desde' => '2026-02-01'],
            [
                'valor'         => '117.31',
                'tipo'          => 'decimal',
                'vigente_hasta' => null,
                'fuente'        => 'INEGI (DOF 9-ene-2026, vigente desde 1-feb-2026)',
                'descripcion'   => 'Valor diario de la UMA en MXN vigente para cálculo de umbrales LFPIORPI.',
                'created_at'    => $now,
                'updated_at'    => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('atestia_parametros')
            ->where('clave', 'uma_diaria_mxn')
            ->where('vigente_desde', '2025-02-01')
            ->delete();

        Schema::table('atestia_parametros', function (Blueprint $table) {
            $table->dropUnique('atestia_parametros_clave_vigencia_unique');
            $table->unique('clave');
        });
    }
};
