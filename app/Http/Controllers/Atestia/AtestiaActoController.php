<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaActo;
use App\Models\AtestiaActoParte;
use App\Models\AtestiaAlerta;
use App\Models\AtestiaCliente;
use App\Models\AtestiaNotaria;
use App\Models\AtestiaPago;
use App\Services\Atestia\ActoThresholdAndAccumulationService;
use App\Services\Atestia\AvisoCalendarService;
use App\Services\Atestia\CashMonitorService;
use Illuminate\Http\Request;

class AtestiaActoController extends Controller
{
    public function index()
    {
        $notaria = AtestiaNotaria::first();
        $apartado = $notaria?->perfil_apartado ?? 'A';
        $catalogo = config("atestia.catalogo_actos.{$apartado}", []);

        $actos = AtestiaActo::with(['partes.cliente', 'pagos'])->latest()->paginate(15);
        $clientes = AtestiaCliente::with('personasRelacionadas')
            ->whereNull('vigente_hasta')
            ->orderBy('nombre_razon_social')
            ->get();

        return view('atestia.actos.index', compact('actos', 'clientes', 'apartado', 'catalogo'));
    }

    public function store(
        Request $request,
        ActoThresholdAndAccumulationService $thresholdSvc,
        AvisoCalendarService $avisoSvc
    ) {
        $notaria = AtestiaNotaria::firstOrFail();

        $inciso = $request->input('tipo_inciso');
        $requiereMontoPositivo = in_array($inciso, ['a', 'd', 'e'], true);

        $data = $request->validate([
            'apartado'                  => 'required|in:A,B',
            'tipo_inciso'               => 'required|in:a,b,c,d,e',
            'subtipo'                   => 'nullable|string|max:64',
            'instrumento_no'            => 'nullable|string|max:64',
            'fecha_otorgamiento'        => 'required|date',
            'valor_precio'              => $requiereMontoPositivo ? 'required|numeric|gt:0' : 'required|numeric|min:0',
            'valor_catastral'           => 'nullable|numeric|min:0',
            'valor_comercial'           => 'nullable|numeric|min:0',
            'monto_garantia'            => 'nullable|numeric|min:0',
            'contribuciones_accesorios' => 'nullable|numeric|min:0',
            'cliente_id'                => 'required|uuid|exists:atestia_clientes,id',
            'rol_parte'                 => 'required|string|max:32',
        ], [
            'valor_precio.gt'             => 'El monto/precio pactado debe ser mayor a 0 para este tipo de acto.',
            'fecha_otorgamiento.required' => 'La fecha de otorgamiento es obligatoria.',
            'cliente_id.required'         => 'Debes seleccionar un compareciente / cliente.',
        ]);

        // F-10: Bloqueo si el cliente es PM o FID sin Beneficiario Controlador
        $cliente = AtestiaCliente::with('personasRelacionadas')->findOrFail($data['cliente_id']);
        if (in_array($cliente->tipo, ['PM', 'FID'], true)) {
            $tieneBc = $cliente->personasRelacionadas->where('rol', 'BC')->isNotEmpty();
            if (! $tieneBc) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'cliente_id' => "Bloqueo normativo: El cliente {$cliente->nombre_razon_social} ({$cliente->tipo}) no tiene Beneficiario Controlador registrado. Complétalo en Expediente Único antes de registrar actos.",
                    ]);
            }
        }

        $acto = AtestiaActo::create([
            'tenant_id'                 => $notaria->tenant_id,
            'notaria_id'                => $notaria->id,
            'apartado'                  => $data['apartado'],
            'tipo_inciso'               => $data['tipo_inciso'],
            'subtipo'                   => $data['subtipo'] ?? null,
            'instrumento_no'            => $data['instrumento_no'] ?? null,
            'fecha_otorgamiento'        => $data['fecha_otorgamiento'],
            'valor_precio'              => $data['valor_precio'],
            'valor_catastral'           => $data['valor_catastral'] ?? 0,
            'valor_comercial'           => $data['valor_comercial'] ?? 0,
            'monto_garantia'            => $data['monto_garantia'] ?? 0,
            'contribuciones_accesorios' => $data['contribuciones_accesorios'] ?? 0,
            'estado'                    => 'evaluado',
            'created_by'                => auth()->id(),
        ]);

        AtestiaActoParte::create([
            'acto_id'    => $acto->id,
            'cliente_id' => $data['cliente_id'],
            'rol'        => $data['rol_parte'],
            'monto'      => $data['valor_precio'],
        ]);

        $eval = $thresholdSvc->evaluateActo($acto);

        if ($eval['por_acumulacion']) {
            AtestiaAlerta::create([
                'tenant_id'    => $notaria->tenant_id,
                'notaria_id'   => $notaria->id,
                'acto_id'      => $acto->id,
                'cliente_id'   => $data['cliente_id'],
                'regla_id'     => 'A07',
                'severidad'    => 'ALTA',
                'estado'       => 'nueva',
                'titulo'       => 'Acumulación en 6 meses alcanza umbral de aviso',
                'detalle_json' => $eval,
                'sla_vence_en' => now()->addWeekdays(2),
            ]);
        } elseif ($eval['cerca_umbral']) {
            AtestiaAlerta::create([
                'tenant_id'    => $notaria->tenant_id,
                'notaria_id'   => $notaria->id,
                'acto_id'      => $acto->id,
                'cliente_id'   => $data['cliente_id'],
                'regla_id'     => 'A06',
                'severidad'    => 'MEDIA',
                'estado'       => 'nueva',
                'titulo'       => 'Operación entre 80% y 99% del umbral (posible fraccionamiento)',
                'detalle_json' => $eval,
                'sla_vence_en' => now()->addWeekdays(2),
            ]);
        }

        if ($eval['requiere_aviso'] && $eval['canal']) {
            $avisoSvc->scheduleMonthlyAviso($acto, $eval['canal']);
        }

        $umbralTxt = $eval['umbral_aplicable_umas'] === 0.0
            ? 'Aviso siempre'
            : ($eval['umbral_aplicable_umas'] ? number_format($eval['umbral_aplicable_umas'], 0) . ' UMA' : 'N/A');
        $resultadoTxt = $eval['requiere_aviso']
            ? "GENERA AVISO (canal: {$eval['canal']}, fecha límite: {$eval['fecha_limite_aviso']})"
            : 'NO GENERA AVISO';

        $mensaje = "Acto evaluado ({$eval['inciso']}): {$eval['valor_en_umas']} UMAs · UMA aplicada: $"
            . number_format($eval['uma_aplicada'], 2)
            . " · Umbral: {$umbralTxt} · Resultado: {$resultadoTxt}.";

        $redirect = redirect()->route('atestia.actos.index')->with('success', $mensaje);

        if ($data['fecha_otorgamiento'] < '2025-07-17') {
            $redirect->with('warning', 'Carga histórica: la fecha de otorgamiento es anterior al 17-jul-2025 (entrada en vigor reforma LFPIORPI). Verifica reglas de carga histórica con el área legal.');
        }

        return $redirect;
    }

    public function confirm(string $id)
    {
        $acto = AtestiaActo::findOrFail($id);

        if ($acto->estado === 'bloqueado_efectivo') {
            return back()->with('error', 'No se puede confirmar un acto bloqueado por intento de pago en efectivo (Art. 32).');
        }

        $tieneAviso = \App\Models\AtestiaAviso::where('tenant_id', $acto->tenant_id)
            ->get()
            ->contains(fn ($a) => in_array($acto->id, (array) $a->acto_ids, true));

        $nuevoEstado = $tieneAviso ? 'avisado' : 'confirmado';
        $acto->update(['estado' => $nuevoEstado]);

        return redirect()->route('atestia.actos.index')
            ->with('success', "Acto {$acto->instrumento_no} actualizado a estado: {$nuevoEstado}.");
    }

    public function storePago(Request $request, string $id, CashMonitorService $cashSvc)
    {
        $acto = AtestiaActo::findOrFail($id);

        $data = $request->validate([
            'forma'           => 'required|in:efectivo,transferencia,cheque_caja,cheque,especie,otro',
            'monto'           => 'required|numeric|min:0.01',
            'fecha'           => 'required|date',
            'comprobante_ref' => 'nullable|string|max:255',
        ]);

        $pago = AtestiaPago::create([
            'tenant_id'       => $acto->tenant_id,
            'acto_id'         => $acto->id,
            'forma'           => $data['forma'],
            'monto'           => $data['monto'],
            'fecha'           => $data['fecha'],
            'comprobante_ref' => $data['comprobante_ref'] ?? null,
        ]);

        $res = $cashSvc->validatePago($acto, $pago);

        if ($res['bloqueado_art32']) {
            return redirect()->route('atestia.actos.index')
                ->with('error', "BLOQUEO Art. 32 LFPIORPI: Pago en efectivo prohibido. Acto: {$res['valor_en_umas']} UMA (Umbral Art. 32: {$res['umbral_art32']} UMA · UMA usada: \${$res['uma_aplicada']}). Se generó alerta crítica A08.");
        }

        return redirect()->route('atestia.actos.index')
            ->with('success', "Pago registrado correctamente (Acto: {$res['valor_en_umas']} UMA · Umbral Art. 32: {$res['umbral_art32']} UMA · UMA: \${$res['uma_aplicada']}).");
    }
}
