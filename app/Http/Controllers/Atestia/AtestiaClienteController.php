<?php

namespace App\Http\Controllers\Atestia;

use App\Http\Controllers\Controller;
use App\Models\AtestiaCliente;
use App\Models\AtestiaNotaria;
use App\Models\AtestiaPersonaRelacionada;
use App\Services\Atestia\AtestiaEbrScoringService;
use App\Services\Pld\PldCryptoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AtestiaClienteController extends Controller
{
    private const RFC_REGEX = '/^([A-ZÑ&]{3,4})(\d{6})([A-Z\d]{3})$/';
    private const CURP_REGEX = '/^[A-Z][AEIOUX][A-Z]{2}\d{6}[HM][A-Z]{5}[A-Z\d]\d$/';

    public function index()
    {
        $clientes = AtestiaCliente::with(['personasRelacionadas', 'ultimoGradoRiesgo'])
            ->whereNull('vigente_hasta')
            ->latest()
            ->paginate(15);

        return view('atestia.clientes.index', compact('clientes'));
    }

    public function store(Request $request, PldCryptoService $crypto, AtestiaEbrScoringService $ebr)
    {
        $notaria = AtestiaNotaria::firstOrFail();

        $data = $this->validateCliente($request);
        $rfc = strtoupper(trim((string) ($data['rfc'] ?? '')));
        $curp = strtoupper(trim((string) ($data['curp'] ?? '')));
        $bcNombre = trim((string) ($data['bc_nombre'] ?? ''));
        $bcDeclaracion = ! empty($data['bc_declaracion_inexistente']);

        $requiereBc = in_array($data['tipo'], ['PM', 'FID'], true);
        $expedienteCompleto = $requiereBc ? ($bcNombre !== '') : ($rfc !== '' || $curp !== '');

        $cliente = AtestiaCliente::create([
            'tenant_id'           => $notaria->tenant_id,
            'notaria_id'          => $notaria->id,
            'tipo'                => $data['tipo'],
            'nombre_razon_social' => $data['nombre_razon_social'],
            'rfc_cifrado'         => $rfc !== '' ? $crypto->encrypt($rfc) : null,
            'rfc_bindex'          => $rfc !== '' ? $crypto->blindIndex($rfc) : null,
            'curp_cifrado'        => $curp !== '' ? $crypto->encrypt($curp) : null,
            'datos_json'          => [
                'bc_declaracion_inexistente' => $bcDeclaracion,
                'no_residente'               => ! empty($data['no_residente']),
                'actividad_riesgo'           => $data['actividad_riesgo'] ?? 'bajo',
            ],
            'expediente_completo' => $expedienteCompleto,
            'created_by'          => auth()->id(),
        ]);

        if ($bcNombre !== '') {
            AtestiaPersonaRelacionada::create([
                'tenant_id'       => $notaria->tenant_id,
                'cliente_id'      => $cliente->id,
                'rol'             => 'BC',
                'nombre_completo' => $bcNombre,
                'criterio_bc'     => '25_por_ciento',
            ]);
        }

        $flags = [
            'pep_extranjera' => ! empty($data['es_pep_extranjera']),
            'pep_nacional'   => ! empty($data['es_pep_nacional']),
        ];

        $indicadores = $this->buildIndicadores($data, $rfc, $curp, $bcNombre !== '');
        $grado = $ebr->evaluateCliente($cliente, $indicadores, [], $flags);

        $redirectRoute = route('atestia.clientes.index');
        $msg = "Expediente creado (v{$cliente->version}). Grado de Riesgo: {$grado->valor} ({$grado->puntaje} pts).";
        if ($requiereBc && $bcNombre === '') {
            $msg .= ' Nota: Sin Beneficiario Controlador registrado; los actos para este cliente permanecerán bloqueados hasta completar su identificación.';
        }

        return redirect($redirectRoute)->with('success', $msg);
    }

    public function update(Request $request, string $id, PldCryptoService $crypto, AtestiaEbrScoringService $ebr)
    {
        $prev = AtestiaCliente::with('personasRelacionadas')->whereNull('vigente_hasta')->findOrFail($id);
        $data = $this->validateCliente($request);

        $rfc = strtoupper(trim((string) ($data['rfc'] ?? '')));
        $curp = strtoupper(trim((string) ($data['curp'] ?? '')));
        $bcNombre = trim((string) ($data['bc_nombre'] ?? ''));
        $bcDeclaracion = ! empty($data['bc_declaracion_inexistente']);

        $requiereBc = in_array($data['tipo'], ['PM', 'FID'], true);
        $expedienteCompleto = $requiereBc ? ($bcNombre !== '') : ($rfc !== '' || $curp !== '');

        $now = now();
        $prev->update(['vigente_hasta' => $now]);

        $nuevo = AtestiaCliente::create([
            'tenant_id'           => $prev->tenant_id,
            'notaria_id'          => $prev->notaria_id,
            'tipo'                => $data['tipo'],
            'nombre_razon_social' => $data['nombre_razon_social'],
            'rfc_cifrado'         => $rfc !== '' ? $crypto->encrypt($rfc) : $prev->rfc_cifrado,
            'rfc_bindex'          => $rfc !== '' ? $crypto->blindIndex($rfc) : $prev->rfc_bindex,
            'curp_cifrado'        => $curp !== '' ? $crypto->encrypt($curp) : $prev->curp_cifrado,
            'datos_json'          => array_merge((array) $prev->datos_json, [
                'version_anterior_id'        => $prev->id,
                'bc_declaracion_inexistente' => $bcDeclaracion,
                'no_residente'               => ! empty($data['no_residente']),
                'actividad_riesgo'           => $data['actividad_riesgo'] ?? 'bajo',
            ]),
            'version'             => $prev->version + 1,
            'vigente_desde'       => $now,
            'expediente_completo' => $expedienteCompleto,
            'created_by'          => auth()->id(),
        ]);

        if ($bcNombre !== '') {
            AtestiaPersonaRelacionada::create([
                'tenant_id'       => $nuevo->tenant_id,
                'cliente_id'      => $nuevo->id,
                'rol'             => 'BC',
                'nombre_completo' => $bcNombre,
                'criterio_bc'     => '25_por_ciento',
            ]);
        }

        $flags = [
            'pep_extranjera' => ! empty($data['es_pep_extranjera']),
            'pep_nacional'   => ! empty($data['es_pep_nacional']),
        ];

        $indicadores = $this->buildIndicadores($data, $rfc, $curp, $bcNombre !== '');
        $grado = $ebr->evaluateCliente($nuevo, $indicadores, [], $flags, null, 'actualizacion');

        return redirect()->route('atestia.clientes.index')
            ->with('success', "Expediente actualizado a v{$nuevo->version}. Grado de Riesgo: {$grado->valor} ({$grado->puntaje} pts).");
    }

    public function destroy(Request $request, string $id)
    {
        $cliente = AtestiaCliente::whereNull('vigente_hasta')->findOrFail($id);

        if ($request->boolean('hard_delete') && auth()->user()?->hasAnyRole(['super_admin', 'administrador', 'notario'])) {
            $cliente->delete();
            return redirect()->route('atestia.clientes.index')
                ->with('success', 'Registro de prueba eliminado permanentemente.');
        }

        $data = $request->validate([
            'motivo_baja' => 'required|string|min:5|max:255',
        ], [
            'motivo_baja.required' => 'Debes indicar el motivo de la baja lógica del expediente.',
            'motivo_baja.min'      => 'El motivo de baja debe tener al menos 5 caracteres.',
        ]);

        $cliente->update([
            'vigente_hasta' => now(),
            'datos_json'    => array_merge((array) $cliente->datos_json, [
                'baja_motivo' => $data['motivo_baja'],
                'baja_por'    => auth()->id(),
                'baja_en'     => now()->toIso8601String(),
            ]),
        ]);

        return redirect()->route('atestia.clientes.index')
            ->with('success', "Expediente {$cliente->nombre_razon_social} dado de baja lógicamente.");
    }

    protected function validateCliente(Request $request): array
    {
        $data = $request->validate([
            'tipo'                       => 'required|in:PF,PM,FID,OTRA',
            'nombre_razon_social'        => 'required|string|max:255',
            'rfc'                        => 'nullable|string|max:13',
            'curp'                       => 'nullable|string|max:18',
            'bc_nombre'                  => 'nullable|string|max:255',
            'bc_declaracion_inexistente' => 'nullable|boolean',
            'es_pep_extranjera'          => 'nullable|boolean',
            'es_pep_nacional'            => 'nullable|boolean',
            'no_residente'               => 'nullable|boolean',
            'actividad_riesgo'           => 'nullable|in:bajo,medio,alto',
        ], [
            'tipo.required'                => 'Selecciona el tipo de persona.',
            'nombre_razon_social.required' => 'El nombre o razón social es obligatorio.',
        ]);

        $rfc = strtoupper(trim((string) ($data['rfc'] ?? '')));
        $curp = strtoupper(trim((string) ($data['curp'] ?? '')));
        $errors = [];

        if ($rfc !== '' && ! preg_match(self::RFC_REGEX, $rfc)) {
            $errors['rfc'] = 'El RFC no tiene formato válido (12 caracteres para Persona Moral o 13 para Persona Física).';
        }

        if ($curp !== '' && ! preg_match(self::CURP_REGEX, $curp)) {
            $errors['curp'] = 'La CURP debe tener exactamente 18 caracteres con formato oficial válido.';
        }

        if (in_array($data['tipo'], ['PM', 'FID'], true) && empty(trim((string) ($data['bc_nombre'] ?? ''))) && empty($data['bc_declaracion_inexistente'])) {
            $errors['bc_nombre'] = 'Para Personas Morales y Fideicomisos debes capturar el Beneficiario Controlador o marcar la declaración de inexistencia/trámite.';
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $data;
    }

    /**
     * Construye el vector de indicadores EBR (0..3) a partir de los datos reales del expediente.
     */
    protected function buildIndicadores(array $data, string $rfc, string $curp, bool $hasBc): array
    {
        $tipo = $data['tipo'];
        $esPmOFid = in_array($tipo, ['PM', 'FID'], true);
        $noResidente = ! empty($data['no_residente']);
        $actividad = match ($data['actividad_riesgo'] ?? 'bajo') {
            'alto'  => 3,
            'medio' => 1,
            default => 0,
        };

        $pepScore = ! empty($data['es_pep_extranjera'])
            ? 3
            : (! empty($data['es_pep_nacional']) ? 2 : 0);

        return [
            'clientes' => [
                'tipo_antiguedad'     => $tipo === 'PF' ? 0 : ($tipo === 'PM' ? 1 : 2),
                'pep'                 => $pepScore,
                'beneficiario_ctrl'   => $esPmOFid ? ($hasBc ? 1 : 3) : 0,
                'actividad_origen'    => $actividad,
                'antecedentes_listas' => 0,
                'conducta_tramite'    => ($esPmOFid && ! $hasBc) ? 2 : 0,
                'representacion'      => $esPmOFid ? 1 : 0,
            ],
            'actos' => [
                'tipo_acto'           => 0,
                'valor_acto'          => 0,
                'coherencia_valor'    => 0,
                'reventa_secuencia'   => 0,
                'estructura_compleja' => $esPmOFid ? 1 : 0,
                'financiamiento_ft'   => 0,
            ],
            'geografia' => [
                'residencia'          => $noResidente ? 2 : 0,
                'nacionalidad'        => $noResidente ? 2 : 0,
                'ubicacion_bien'      => 0,
                'origen_destino'      => $noResidente ? 1 : 0,
                'jurisdiccion_ft'     => 0,
            ],
            'canales' => [
                'forma_pago'          => 0,
                'pagos_terceros'      => 0,
                'fraccionamiento'     => 0,
                'canal_contratacion'  => 0,
                'flujos_ft'           => 0,
            ],
        ];
    }
}
