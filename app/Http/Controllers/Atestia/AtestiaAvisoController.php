<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaAviso;
use App\Services\Pld\PldCryptoService;
use Illuminate\Http\Request;

class AtestiaAvisoController extends Controller
{
    public function index()
    {
        $avisos = AtestiaAviso::orderBy('fecha_limite')->paginate(20);
        return view('atestia.avisos.index', compact('avisos'));
    }

    public function registerAcuse(Request $request, string $id, PldCryptoService $crypto)
    {
        $aviso = AtestiaAviso::findOrFail($id);

        $data = $request->validate([
            'acuse_folio' => 'required|string|max:120',
        ]);

        $aviso->update([
            'acuse_folio'   => $data['acuse_folio'],
            'acuse_cifrado' => $crypto->encrypt(json_encode([
                'folio'         => $data['acuse_folio'],
                'registrado_en' => now()->toIso8601String(),
                'usuario_id'    => auth()->id(),
            ])),
            'estado'        => 'presentado',
            'presentado_en' => now(),
        ]);

        return redirect()->route('atestia.avisos.index')
            ->with('success', 'Folio y acuse cifrado registrados.');
    }
}
