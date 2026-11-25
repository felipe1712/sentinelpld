@extends('atestia.layout.master')
@section('title', 'Expediente Único y BC')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Expediente Único de Clientes y Beneficiario Controlador (M1 / M4)</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Nuevo Expediente</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('atestia.clientes.store') }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">Tipo de Persona <span class="text-danger">*</span></label>
                            <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                                <option value="PF" {{ old('tipo', 'PF') === 'PF' ? 'selected' : '' }}>Persona Física (PF)</option>
                                <option value="PM" {{ old('tipo') === 'PM' ? 'selected' : '' }}>Persona Moral (PM)</option>
                                <option value="FID" {{ old('tipo') === 'FID' ? 'selected' : '' }}>Fideicomiso (FID)</option>
                            </select>
                            @error('tipo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Nombre o Razón Social <span class="text-danger">*</span></label>
                            <input type="text" name="nombre_razon_social" class="form-control @error('nombre_razon_social') is-invalid @enderror" value="{{ old('nombre_razon_social') }}" required>
                            @error('nombre_razon_social')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">RFC (12 o 13 car., cifrado)</label>
                                <input type="text" name="rfc" class="form-control @error('rfc') is-invalid @enderror" value="{{ old('rfc') }}" maxlength="13" placeholder="XAXX010101000">
                                @error('rfc')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">CURP (18 car., cifrado)</label>
                                <input type="text" name="curp" class="form-control @error('curp') is-invalid @enderror" value="{{ old('curp') }}" maxlength="18">
                                @error('curp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Beneficiario Controlador (obligatorio PM / FID)</label>
                            <input type="text" name="bc_nombre" class="form-control @error('bc_nombre') is-invalid @enderror" value="{{ old('bc_nombre') }}" placeholder="Nombre completo del BC">
                            @error('bc_nombre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="bc_declaracion_inexistente" value="1" id="bcDecl" {{ old('bc_declaracion_inexistente') ? 'checked' : '' }}>
                            <label class="form-check-label f-s-12" for="bcDecl">Declaración: sin BC identificado (bloquea actos hasta completar)</label>
                        </div>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="no_residente" value="1" id="noRes" {{ old('no_residente') ? 'checked' : '' }}>
                            <label class="form-check-label" for="noRes">No residente / domicilio en extranjero</label>
                        </div>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="es_pep_nacional" value="1" id="pepNac" {{ old('es_pep_nacional') ? 'checked' : '' }}>
                            <label class="form-check-label" for="pepNac">PEP Nacional (Art. 23 Bis 3)</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="es_pep_extranjera" value="1" id="pepExt" {{ old('es_pep_extranjera') ? 'checked' : '' }}>
                            <label class="form-check-label" for="pepExt">PEP Extranjera (Fuerza ALTO)</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Crear Expediente y Calcular EBR</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Expedientes Vigentes</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Nombre / Razón Social</th>
                                <th>Tipo</th>
                                <th>Beneficiario Controlador</th>
                                <th>Grado EBR</th>
                                <th>Ver.</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                @php
                                    $grado = $cliente->ultimoGradoRiesgo;
                                    $bcText = $cliente->personasRelacionadas->where('rol', 'BC')->pluck('nombre_completo')->join(', ');
                                    $riesgoClass = match($grado?->valor) {
                                        'ALTO'  => 'bg-danger',
                                        'MEDIO' => 'bg-warning text-dark',
                                        'BAJO'  => 'bg-success',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $cliente->nombre_razon_social }}</td>
                                    <td><span class="badge bg-info">{{ $cliente->tipo }}</span></td>
                                    <td>
                                        {{ $bcText ?: (in_array($cliente->tipo, ['PM', 'FID']) ? 'Pendiente (actos bloqueados)' : 'N/A (PF)') }}
                                    </td>
                                    <td>
                                        @if($grado)
                                            <span class="badge {{ $riesgoClass }}">{{ $grado->valor }} ({{ $grado->puntaje }} pts)</span>
                                            @if($grado->residuales_json)
                                                <div class="text-muted f-s-11">
                                                    C:{{ $grado->residuales_json['clientes'] ?? 0 }} ·
                                                    A:{{ $grado->residuales_json['actos'] ?? 0 }} ·
                                                    G:{{ $grado->residuales_json['geografia'] ?? 0 }} ·
                                                    P:{{ $grado->residuales_json['canales'] ?? 0 }}
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>v{{ $cliente->version }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#edit-cli-{{ $cliente->id }}">Editar</button>
                                            <button type="button" class="btn btn-xs btn-outline-danger" data-bs-toggle="collapse" data-bs-target="#baja-cli-{{ $cliente->id }}">Baja</button>
                                        </div>
                                        <div class="collapse mt-2" id="edit-cli-{{ $cliente->id }}">
                                            <form method="POST" action="{{ route('atestia.clientes.update', $cliente->id) }}" class="p-2 border rounded bg-light">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="tipo" value="{{ $cliente->tipo }}">
                                                <input type="text" name="nombre_razon_social" class="form-control form-control-sm mb-1" value="{{ $cliente->nombre_razon_social }}" required>
                                                <input type="text" name="bc_nombre" class="form-control form-control-sm mb-1" value="{{ $bcText }}" placeholder="Beneficiario Controlador">
                                                <button type="submit" class="btn btn-xs btn-primary">Guardar nueva versión</button>
                                            </form>
                                        </div>
                                        <div class="collapse mt-2" id="baja-cli-{{ $cliente->id }}">
                                            <form method="POST" action="{{ route('atestia.clientes.destroy', $cliente->id) }}" class="p-2 border rounded bg-light">
                                                @csrf
                                                @method('DELETE')
                                                <input type="text" name="motivo_baja" class="form-control form-control-sm mb-1" placeholder="Motivo de baja (mín. 5 car.)" minlength="5" required>
                                                <button type="submit" class="btn btn-xs btn-danger">Confirmar baja</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
