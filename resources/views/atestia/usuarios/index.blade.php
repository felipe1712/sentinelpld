@extends('atestia.layout.master')
@section('title', 'Usuarios')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Gestión de Usuarios</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Nuevo Usuario</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('atestia.usuarios.store') }}">
                        @csrf
                        @if($isSuper)
                            <div class="mb-2">
                                <label class="form-label">Notaría / Cliente</label>
                                <select name="tenant_id" class="form-select">
                                    <option value="">— Global / Superadministrador —</option>
                                    @foreach($tenants as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="mb-2">
                            <label class="form-label">Nombre Completo</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-2">
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
                        <div class="mb-3">
                            <label class="form-label">Contraseña (mín. 8 caracteres)</label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Crear Usuario</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Usuarios Registrados</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Correo</th>
                                <th>Perfil</th>
                                <th>Estado</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuarios as $u)
                                <tr>
                                    <td>{{ $u->name }}</td>
                                    <td>{{ $u->email }}</td>
                                    <td><span class="badge bg-primary">{{ $u->roles->pluck('name')->join(', ') }}</span></td>
                                    <td>
                                        <span class="badge {{ $u->activo ? 'bg-success' : 'bg-danger' }}">
                                            {{ $u->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('atestia.usuarios.toggle', $u->id) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                    {{ $u->activo ? 'Desactivar' : 'Activar' }}
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $usuarios->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
