<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaActo;
use App\Models\AtestiaAlerta;
use App\Models\AtestiaAviso;
use App\Models\AtestiaCliente;
use App\Models\AtestiaNotaria;
use App\Models\AtestiaParametro;

class AtestiaDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $notaria = AtestiaNotaria::first();

        $stats = [
            'actos_mes'        => AtestiaActo::whereMonth('fecha_otorgamiento', now()->month)->count(),
            'actos_bloqueados' => AtestiaActo::where('estado', 'bloqueado_efectivo')->count(),
            'clientes_total'   => AtestiaCliente::whereNull('vigente_hasta')->count(),
            'alertas_abiertas' => $user->can('atestia.alertas.revisar')
                ? AtestiaAlerta::whereIn('estado', ['nueva', 'en_revision'])->count()
                : null,
            'avisos_pendientes' => $user->can('atestia.avisos.ver')
                ? AtestiaAviso::whereIn('estado', ['borrador', 'pendiente_de_formato', 'listo_para_presentar'])->count()
                : null,
            'uma_vigente'      => AtestiaParametro::getValor('uma_diaria_mxn', 117.31, now()),
            'anexo_12a'        => AtestiaParametro::getValor('anexo_12a_actualizado', 'desconocido'),
        ];

        $ultimosActos = AtestiaActo::latest()->limit(8)->get();

        return view('atestia.dashboard', compact('notaria', 'stats', 'ultimosActos'));
    }
}
