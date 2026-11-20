<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaCumplimientoRegistro;
use App\Models\AtestiaNotaria;
use Illuminate\Http\Request;

class AtestiaCumplimientoController extends Controller
{
    public function index()
    {
        $registros = AtestiaCumplimientoRegistro::latest('fecha_evento')->paginate(20);
        return view('atestia.cumplimiento.index', compact('registros'));
    }

    public function store(Request $request)
    {
        $notaria = AtestiaNotaria::firstOrFail();

        $data = $request->validate([
            'tipo'                         => 'required|in:capacitacion_f08,declaracion_personal_f07,revision_manual_f10',
            'titulo'                       => 'required|string|max:255',
            'fecha_evento'                 => 'required|date',
            'vigencia_hasta'               => 'nullable|date',
            'instructor_nombre'            => 'nullable|string|max:255',
            'instructor_experiencia_anios' => 'nullable|integer|min:0|max:60',
            'resultado_evaluacion'         => 'nullable|string|max:64',
        ]);

        // Art. 39 Bis fr. III: quien capacite debe acreditar >= 5 años de experiencia PLD
        if ($data['tipo'] === 'capacitacion_f08' && ($data['instructor_experiencia_anios'] ?? 0) < 5) {
            return back()->withErrors([
                'instructor_experiencia_anios' => 'El instructor de capacitación anual debe acreditar al menos 5 años de experiencia en PLD (Acuerdo Art. 39 Bis fr. III).',
            ])->withInput();
        }

        AtestiaCumplimientoRegistro::create([
            'tenant_id'                    => $notaria->tenant_id,
            'notaria_id'                   => $notaria->id,
            'tipo'                         => $data['tipo'],
            'titulo'                       => $data['titulo'],
            'fecha_evento'                 => $data['fecha_evento'],
            'vigencia_hasta'               => $data['vigencia_hasta'] ?? null,
            'instructor_nombre'            => $data['instructor_nombre'] ?? null,
            'instructor_experiencia_anios' => $data['instructor_experiencia_anios'] ?? null,
            'resultado_evaluacion'         => $data['resultado_evaluacion'] ?? null,
            'aprobado_por'                 => auth()->id(),
        ]);

        return redirect()->route('atestia.cumplimiento.index')
            ->with('success', 'Registro de cumplimiento interno guardado.');
    }
}
