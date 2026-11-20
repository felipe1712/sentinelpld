<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaCliente;
use App\Models\AtestiaNotaria;
use App\Models\AtestiaPersonaRelacionada;
use App\Services\Atestia\AtestiaEbrScoringService;
use App\Services\Pld\PldCryptoService;
use Illuminate\Http\Request;

class AtestiaClienteController extends Controller
{
    public function index()
    {
        $clientes = AtestiaCliente::with('personasRelacionadas')
            ->whereNull('vigente_hasta')
            ->latest()
            ->paginate(15);

        return view('atestia.clientes.index', compact('clientes'));
    }

    public function store(Request $request, PldCryptoService $crypto, AtestiaEbrScoringService $ebr)
    {
        $notaria = AtestiaNotaria::firstOrFail();

        $data = $request->validate([
            'tipo'                => 'required|in:PF,PM,FID,OTRA',
            'nombre_razon_social' => 'required|string|max:255',
            'rfc'                 => 'nullable|string|max:13',
            'curp'                => 'nullable|string|max:18',
            'bc_nombre'           => 'nullable|string|max:255',
            'es_pep_extranjera'   => 'nullable|boolean',
            'es_pep_nacional'     => 'nullable|boolean',
        ]);

        $rfc = strtoupper(trim((string) ($data['rfc'] ?? '')));
        $curp = strtoupper(trim((string) ($data['curp'] ?? '')));

        $cliente = AtestiaCliente::create([
            'tenant_id'           => $notaria->tenant_id,
            'notaria_id'          => $notaria->id,
            'tipo'                => $data['tipo'],
            'nombre_razon_social' => $data['nombre_razon_social'],
            'rfc_cifrado'         => $rfc !== '' ? $crypto->encrypt($rfc) : null,
            'rfc_bindex'          => $rfc !== '' ? $crypto->blindIndex($rfc) : null,
            'curp_cifrado'        => $curp !== '' ? $crypto->encrypt($curp) : null,
            'created_by'          => auth()->id(),
        ]);

        if (! empty($data['bc_nombre'])) {
            AtestiaPersonaRelacionada::create([
                'tenant_id'       => $notaria->tenant_id,
                'cliente_id'      => $cliente->id,
                'rol'             => 'BC',
                'nombre_completo' => $data['bc_nombre'],
                'criterio_bc'     => '25_por_ciento',
            ]);
        }

        $flags = [
            'pep_extranjera' => ! empty($data['es_pep_extranjera']),
            'pep_nacional'   => ! empty($data['es_pep_nacional']),
        ];

        $grado = $ebr->evaluateCliente($cliente, [], [], $flags);

        return redirect()->route('atestia.clientes.index')
            ->with('success', "Expediente creado. Grado de Riesgo inicial: {$grado->valor} ({$grado->puntaje} pts).");
    }
}
