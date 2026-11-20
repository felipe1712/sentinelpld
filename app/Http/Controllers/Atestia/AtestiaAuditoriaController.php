<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaAlerta;
use App\Models\AtestiaBitacoraAcceso;
use App\Models\AtestiaCliente;
use App\Models\AtestiaGradoRiesgo;
use App\Models\AtestiaNotaria;
use App\Services\Atestia\AtestiaNotariaEbrService;
use Illuminate\Http\Request;

class AtestiaAuditoriaController extends Controller
{
    public function index(Request $request, AtestiaNotariaEbrService $ebrService)
    {
        $notaria = AtestiaNotaria::firstOrFail();

        // T11: Todo acceso del auditor o exportación queda registrado en bitácora inmutable
        AtestiaBitacoraAcceso::create([
            'tenant_id'   => $notaria->tenant_id,
            'usuario_id'  => auth()->id(),
            'objeto_tipo' => 'paquete_auditoria',
            'objeto_id'   => (string) $notaria->id,
            'accion'      => 'consulta_solo_lectura',
            'ip'          => $request->ip(),
            'user_agent'  => $request->userAgent(),
            'created_at'  => now(),
        ]);

        $ebrInstitucional = $ebrService->generateInstitutionalReport($notaria);
        $muestraClientes = AtestiaCliente::whereNull('vigente_hasta')->latest()->limit(20)->get();
        $historialGrados = AtestiaGradoRiesgo::latest('calculado_en')->limit(30)->get();
        $alertasResumen = AtestiaAlerta::latest()->limit(30)->get();
        $bitacora = AtestiaBitacoraAcceso::latest('created_at')->limit(30)->get();

        if ($request->boolean('export_json')) {
            return response()->json([
                'nota_alcance'       => 'Insumos de solo lectura para dictamen Art. 47. El dictamen lo emite el auditor independiente.',
                'ebr_institucional'  => $ebrInstitucional,
                'muestra_clientes'   => $muestraClientes->map(fn ($c) => ['id' => $c->id, 'tipo' => $c->tipo, 'nombre' => $c->nombre_razon_social, 'version' => $c->version]),
                'historial_grados'   => $historialGrados,
                'alertas_resumen'    => $alertasResumen,
            ]);
        }

        return view('atestia.auditoria.index', compact(
            'notaria', 'ebrInstitucional', 'muestraClientes', 'historialGrados', 'alertasResumen', 'bitacora'
        ));
    }
}
