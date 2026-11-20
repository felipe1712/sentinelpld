@extends('atestia.layout.master')
@section('title', 'Notarías y Clientes')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Administración Multi-Tenant · Notarías y Clientes</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Nueva Notaría / Cliente</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('atestia.admin.notarias.store') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Nombre Comercial</label>
                            <input type="text" name="nombre_comercial" class="form-control" placeholder="Ej. Notaría Pública No. 12" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">RFC Titular</label>
                                <input type="text" name="rfc" class="form-control" maxlength="13" required>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Número</label>
                                <input type="text" name="numero_fedatario" class="form-control" placeholder="Ej. 12">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Nombre del Fedatario Titular</label>
                            <input type="text" name="titular" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Tipo</label>
                                <select name="tipo_fedatario" class="form-select">
                                    <option value="NOTARIO">Notario Público</option>
                                    <option value="CORREDOR">Corredor Público</option>
                                </select>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Plan</label>
                                <select name="perfil_servicio" class="form-select">
                                    <option value="pro">Pro</option>
                                    <option value="lite">Lite</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Entidad Federativa</label>
                            <input type="text" name="entidad_federativa" class="form-control" placeholder="Ej. CDMX, Jalisco">
                        </div>

                        <hr>
                        <h6 class="fw-bold mb-2">Usuario Administrador Inicial</h6>
                        <div class="mb-2">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="admin_name" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="admin_email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña (mín. 8 caracteres)</label>
                            <input type="password" name="admin_password" class="form-control" minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Crear Notaría y Administrador</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Clientes Registrados</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Cliente / Notaría</th>
                                <th>Titular / RFC</th>
                                <th>Apartado</th>
                                <th>Plan</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notarias as $notaria)
                                <tr>
                                    <td><strong>{{ $notaria->tenant?->name }}</strong></td>
                                    <td>
                                        <div>{{ $notaria->titular }}</div>
                                        <small class="text-muted">{{ $notaria->rfc }} · {{ $notaria->entidad_federativa }}</small>
                                    </td>
                                    <td>{{ $notaria->tipo_fedatario }} ({{ $notaria->perfil_apartado }})</td>
                                    <td><span class="badge bg-info">{{ strtoupper($notaria->perfil_servicio) }}</span></td>
                                    <td>
                                        <span class="badge {{ $notaria->activo ? 'bg-success' : 'bg-danger' }}">
                                            {{ $notaria->activo ? 'Activa' : 'Suspendida' }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('atestia.admin.notarias.toggle', $notaria->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                {{ $notaria->activo ? 'Suspender' : 'Activar' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Sin notarías registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $notarias->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
