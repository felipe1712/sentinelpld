@extends('atestia.layout.master')
@section('title', 'Auditoría y EBR Institucional')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h4 class="main-title">Insumos para Auditoría y EBR Institucional (M3 / M8)</h4>
                <p class="text-muted">Vista de solo lectura (Art. 45 / Art. 47). Cada consulta queda registrada en bitácora.</p>
            </div>
            <a href="{{ route('atestia.auditoria.index', ['export_json' => 1]) }}" class="btn btn-outline-primary btn-sm">
                <i class="ti ti-download me-1"></i> Exportar Paquete JSON
            </a>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted f-s-13">Nivel de Riesgo de la Notaría</span>
                    <h3 class="mt-2 mb-1">{{ $ebrInstitucional['nivel_riesgo'] }} ({{ $ebrInstitucional['indice_notaria'] }} pts)</h3>
                    @if($ebrInstitucional['datos_proyectados'])
                        <span class="badge bg-warning text-dark">Datos Proyectados (&lt; 12 meses)</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted f-s-13">Auditoría Aplicable</span>
                    <h5 class="mt-2 mb-1">{{ $ebrInstitucional['auditoria_aplicable'] }}</h5>
                    <small class="text-muted">Actos últimos 12m: {{ $ebrInstitucional['total_actos_12m'] }} · Corte desde: {{ $ebrInstitucional['corte_desde'] }}</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h5 class="mb-0">Bitácora Inmutable de Accesos (últimos 30)</h5></div>
        <div class="card-body table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Usuario ID</th>
                        <th>Objeto</th>
                        <th>Acción</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bitacora as $row)
                        <tr>
                            <td>{{ $row->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $row->usuario_id }}</td>
                            <td>{{ $row->objeto_tipo }} #{{ $row->objeto_id }}</td>
                            <td>{{ $row->accion }}</td>
                            <td>{{ $row->ip }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
