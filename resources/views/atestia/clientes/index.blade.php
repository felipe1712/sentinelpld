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
                            <label class="form-label">Tipo de Persona</label>
                            <select name="tipo" class="form-select" required>
                                <option value="PF">Persona Física (PF)</option>
                                <option value="PM">Persona Moral (PM)</option>
                                <option value="FID">Fideicomiso (FID)</option>
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Nombre o Razón Social</label>
                            <input type="text" name="nombre_razon_social" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">RFC (cifrado)</label>
                                <input type="text" name="rfc" class="form-control" maxlength="13">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">CURP (cifrado)</label>
                                <input type="text" name="curp" class="form-control" maxlength="18">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Beneficiario Controlador (si aplica)</label>
                            <input type="text" name="bc_nombre" class="form-control" placeholder="Nombre completo del BC">
                        </div>
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="es_pep_nacional" value="1" id="pepNac">
                            <label class="form-check-label" for="pepNac">PEP Nacional (Art. 23 Bis 3)</label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="es_pep_extranjera" value="1" id="pepExt">
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
                                <th>Versión</th>
                                <th>Alta</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->nombre_razon_social }}</td>
                                    <td><span class="badge bg-info">{{ $cliente->tipo }}</span></td>
                                    <td>
                                        {{ $cliente->personasRelacionadas->where('rol', 'BC')->pluck('nombre_completo')->join(', ') ?: 'No registrado' }}
                                    </td>
                                    <td>v{{ $cliente->version }}</td>
                                    <td>{{ $cliente->created_at?->format('Y-m-d') }}</td>
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
