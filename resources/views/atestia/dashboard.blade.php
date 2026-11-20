@extends('atestia.layout.master')
@section('title', 'Dashboard')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Panel de Cumplimiento LFPIORPI</h4>
            <p class="text-muted mb-3">
                {{ $notaria?->titular ?? 'Notaría / Correduría' }} ·
                Apartado {{ $notaria?->perfil_apartado ?? 'A' }} ·
                Perfil {{ strtoupper($notaria?->perfil_servicio ?? 'lite') }} ·
                UMA vigente: <strong>${{ number_format($stats['uma_vigente'], 2) }} MXN</strong>
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted f-s-13">Actos del Mes</span>
                    <h3 class="mt-2 mb-0">{{ $stats['actos_mes'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted f-s-13">Expedientes Únicos</span>
                    <h3 class="mt-2 mb-0">{{ $stats['clientes_total'] }}</h3>
                </div>
            </div>
        </div>
        @if(!is_null($stats['alertas_abiertas']))
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <span class="text-muted f-s-13">Alertas Abiertas</span>
                        <h3 class="mt-2 mb-0 text-warning">{{ $stats['alertas_abiertas'] }}</h3>
                    </div>
                </div>
            </div>
        @endif
        @if(!is_null($stats['avisos_pendientes']))
            <div class="col-md-3 col-sm-6">
                <div class="card">
                    <div class="card-body">
                        <span class="text-muted f-s-13">Avisos Pendientes (Día 17)</span>
                        <h3 class="mt-2 mb-0 text-danger">{{ $stats['avisos_pendientes'] }}</h3>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Últimos Actos Registrados</h5>
                    <a href="{{ route('atestia.actos.index') }}" class="btn btn-sm btn-primary">Ver todos / Registrar</a>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Instrumento</th>
                                <th>Apartado / Inciso</th>
                                <th>Fecha</th>
                                <th>Monto Computable</th>
                                <th>UMAs</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosActos as $acto)
                                <tr>
                                    <td>{{ $acto->instrumento_no ?? 'S/N' }}</td>
                                    <td>{{ $acto->apartado }}-{{ $acto->tipo_inciso }}</td>
                                    <td>{{ $acto->fecha_otorgamiento?->format('Y-m-d') }}</td>
                                    <td>${{ number_format($acto->monto_computable_umbral, 2) }}</td>
                                    <td>{{ number_format($acto->valor_en_umas, 2) }}</td>
                                    <td><span class="badge bg-secondary">{{ $acto->estado }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Sin actos registrados aún.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
