<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AtestiaParametroSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        foreach (config('atestia.parametros_default', []) as $param) {
            DB::table('atestia_parametros')->updateOrInsert(
                [
                    'clave'         => $param['clave'],
                    'vigente_desde' => $param['vigente_desde'],
                ],
                array_merge($param, [
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
            );
        }
    }
}
