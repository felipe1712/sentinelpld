@extends('atestia.layout.master')
@section('title', 'Panel Plataforma')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Consola Super Administrador · {{ config('app.name') }}</h4>
            <p class="text-muted mb-3">Gestión global de clientes (notarías y corredurías), usuarios e integraciones del sistema.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted f-s-13">Notarías Activas</span>
                    <h3 class="mt-2 mb-0">{{ $stats['notarias_activas'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted f-s-13">Usuarios Totales</span>
                    <h3 class="mt-2 mb-0">{{ $stats['usuarios_totales'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted f-s-13">Planes (Pro / Lite)</span>
                    <h3 class="mt-2 mb-0">{{ $stats['plan_pro'] }} / {{ $stats['plan_lite'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card">
                <div class="card-body">
                    <span class="text-muted f-s-13">Integración Brevo</span>
                    <h4 class="mt-2 mb-0">
                        <span class="badge {{ $stats['brevo_activo'] ? 'bg-success' : 'bg-secondary' }}">
                            {{ $stats['brevo_activo'] ? 'Activa' : 'Inactiva' }}
                        </span>
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Últimos Clientes Dados de Alta</h5>
                    <a href="{{ route('atestia.admin.notarias.index') }}" class="btn btn-sm btn-primary">Administrar Notarías</a>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Titular</th>
                                <th>Tipo / Apartado</th>
                                <th>Plan</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimasNotarias as $n)
                                <tr>
                                    <td><strong>{{ $n->tenant?->name }}</strong></td>
                                    <td>{{ $n->titular }}</td>
                                    <td>{{ $n->tipo_fedatario }} ({{ $n->perfil_apartado }})</td>
                                    <td><span class="badge bg-info">{{ strtoupper($n->perfil_servicio) }}</span></td>
                                    <td>
                                        <span class="badge {{ $n->activo ? 'bg-success' : 'bg-danger' }}">
                                            {{ $n->activo ? 'Activa' : 'Suspendida' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted">Sin clientes registrados aún.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
