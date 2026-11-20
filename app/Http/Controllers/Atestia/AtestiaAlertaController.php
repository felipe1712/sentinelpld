<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaAlerta;
use Illuminate\Http\Request;

class AtestiaAlertaController extends Controller
{
    public function index()
    {
        $alertas = AtestiaAlerta::latest()->paginate(20);
        return view('atestia.alertas.index', compact('alertas'));
    }

    public function resolve(Request $request, string $id)
    {
        $alerta = AtestiaAlerta::findOrFail($id);

        $data = $request->validate([
            'estado'           => 'required|in:descartada,escalada,aviso',
            'analisis_escrito' => 'required|string|min:10',
        ]);

        $alerta->update([
            'estado'           => $data['estado'],
            'analisis_escrito' => $data['analisis_escrito'],
            'decision'         => $data['estado'],
            'revisor_id'       => auth()->id(),
            'cerrada_en'       => now(),
        ]);

        return redirect()->route('atestia.alertas.index')
            ->with('success', "Alerta {$alerta->regla_id} cerrada como {$data['estado']}.");
    }
}
