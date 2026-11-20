<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaParametro;
use Illuminate\Http\Request;

class AtestiaParametroController extends Controller
{
    public function index()
    {
        $parametros = AtestiaParametro::orderBy('clave')->get();
        return view('atestia.parametros.index', compact('parametros'));
    }

    public function update(Request $request, int $id)
    {
        $param = AtestiaParametro::findOrFail($id);

        $data = $request->validate([
            'valor' => 'required|string|max:255',
        ]);

        $param->update(['valor' => $data['valor']]);

        return redirect()->route('atestia.parametros.index')
            ->with('success', "Parámetro {$param->clave} actualizado.");
    }
}
