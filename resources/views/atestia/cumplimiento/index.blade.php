@extends('atestia.layout.master')
@section('title', 'Cumplimiento Interno')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Cumplimiento Interno (M9 · F-07 Personal, F-08 Capacitación, F-10 Revisión Manual)</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Registrar Evidencia Interna</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('atestia.cumplimiento.store') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Tipo de Registro</label>
                            <select name="tipo" class="form-select" required>
                                <option value="capacitacion_f08">Capacitación Anual (F-08 · Art. 39 Bis)</option>
                                <option value="declaracion_personal_f07">Declaración de Personal (F-07 · Art. 39 Bis 2)</option>
                                <option value="revision_manual_f10">Revisión Anual Manual/EBR (F-10 · ≤ 12m)</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Título / Descripción</label>
                            <input type="text" name="titulo" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Fecha</label>
                                <input type="date" name="fecha_evento" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Vigencia hasta</label>
                                <input type="date" name="vigencia_hasta" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-8 mb-2">
                                <label class="form-label">Instructor (si F-08)</label>
                                <input type="text" name="instructor_nombre" class="form-control">
                            </div>
                            <div class="col-4 mb-2">
                                <label class="form-label">Años Exp. (≥5)</label>
                                <input type="number" name="instructor_experiencia_anios" class="form-control" min="0" value="5">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Resultado / Observación</label>
                            <input type="text" name="resultado_evaluacion" class="form-control" placeholder="Ej. Aprobado 95%">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar Registro</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Histórico de Cumplimiento Interno</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th>Título</th>
                                <th>Fecha</th>
                                <th>Instructor / Exp.</th>
                                <th>Resultado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($registros as $reg)
                                <tr>
                                    <td><span class="badge bg-secondary">{{ $reg->tipo }}</span></td>
                                    <td>{{ $reg->titulo }}</td>
                                    <td>{{ $reg->fecha_evento?->format('Y-m-d') }}</td>
                                    <td>
                                        @if($reg->instructor_nombre)
                                            {{ $reg->instructor_nombre }} ({{ $reg->instructor_experiencia_anios }} años)
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $reg->resultado_evaluacion ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Sin registros aún.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $registros->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
