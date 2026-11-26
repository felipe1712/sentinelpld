@extends('atestia.layout.master')
@section('title', 'Usuarios')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Gestión de Usuarios</h4>
        </div>
    </div>

    {{-- 1. Primero: Tabla de Usuarios Registrados --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Usuarios Registrados</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                @if($isSuper)
                                    <th>Notaría / Cliente</th>
                                @endif
                                <th>Perfil</th>
                                <th>Estado</th>
                                <th>MFA (Correo)</th>
                                <th>Cambiar Contraseña</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($usuarios as $u)
                                <tr>
                                    <td>{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
                                    @if($isSuper)
                                        <td>{{ $u->tenant?->name ?? 'Global' }}</td>
                                    @endif
                                    <td><span class="badge bg-primary">{{ $u->roles->pluck('name')->join(', ') ?: 'Sin rol' }}</span></td>
                                    <td>
                                        <span class="badge {{ $u->activo ? 'bg-success' : 'bg-danger' }}">
                                            {{ $u->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('atestia.usuarios.mfa', $u->id) }}">
                                            @csrf
                                            @method('PATCH')
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input" type="checkbox" role="switch"
                                                       id="mfaSwitch{{ $u->id }}"
                                                       {{ $u->mfa_enabled ? 'checked' : '' }}
                                                       onchange="this.form.submit()">
                                                <label class="form-check-label f-s-12" for="mfaSwitch{{ $u->id }}">
                                                    {{ $u->mfa_enabled ? 'Sí' : 'No' }}
                                                </label>
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('atestia.usuarios.password', $u->id) }}" class="d-flex gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="password" name="password" class="form-control form-control-sm" placeholder="Mín. 12 car. (Aa1!)" minlength="12" required style="width:165px">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                                        </form>
                                    </td>
                                    <td>
                                        @if($u->id !== auth()->id())
                                            <div class="d-flex gap-1">
                                                <form method="POST" action="{{ route('atestia.usuarios.toggle', $u->id) }}">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                        {{ $u->activo ? 'Desactivar' : 'Activar' }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('atestia.usuarios.destroy', $u->id) }}"
                                                      onsubmit="return confirm('¿Eliminar al usuario {{ $u->email }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-muted f-s-12">Sesión actual</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuper ? 8 : 7 }}" class="text-center text-muted">Sin usuarios registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $usuarios->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. Abajo: Formulario para Crear Nuevo Usuario --}}
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Nuevo Usuario</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('atestia.usuarios.store') }}">
                        @csrf
                        <div class="row g-3">
                            @if($isSuper)
                                <div class="col-md-4">
                                    <label class="form-label">Notaría / Cliente</label>
                                    <select name="tenant_id" class="form-select">
                                        <option value="">— Global / Superadministrador —</option>
                                        @foreach($tenants as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-md-4">
                                <label class="form-label">Nombre Completo</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Perfil</label>
                                <select name="role" class="form-select" required>
                                    @if($isSuper)
                                        <option value="super_admin">Superadministrador</option>
                                    @endif
                                    <option value="administrador">Administrador de Notaría</option>
                                    <option value="operador">Operador</option>
                                    <option value="auditor_ro">Auditor Solo Lectura</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Contraseña (mín. 12 car., Aa1!)</label>
                                <input type="password" name="password" class="form-control" minlength="12" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-check form-switch mt-3">
                                    <input class="form-check-input" type="checkbox" role="switch" name="mfa_enabled" value="1" id="newMfaSwitch">
                                    <label class="form-check-label" for="newMfaSwitch">Enviar código MFA por correo al iniciar sesión</label>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Crear Usuario</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
