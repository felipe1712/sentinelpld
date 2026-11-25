@extends('atestia.layout.master')
@section('title', 'Actos e Instrumentos')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Actos e Instrumentos (Apartado {{ $apartado }})</h4>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Registrar Nuevo Acto</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('atestia.actos.store') }}">
                        @csrf
                        <input type="hidden" name="apartado" value="{{ $apartado }}">
                        <div class="mb-2">
                            <label class="form-label">Inciso LFPIORPI <span class="text-danger">*</span></label>
                            <select name="tipo_inciso" class="form-select @error('tipo_inciso') is-invalid @enderror" required>
                                @foreach($catalogo as $inciso => $info)
                                    <option value="{{ $inciso }}" {{ old('tipo_inciso') === $inciso ? 'selected' : '' }}>
                                        {{ strtoupper($apartado) }}-{{ $inciso }} · {{ $info['nombre'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_inciso')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Compareciente / Cliente <span class="text-danger">*</span></label>
                            <select name="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror" required>
                                <option value="">— Seleccionar cliente —</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}" {{ old('cliente_id') === $c->id ? 'selected' : '' }}>
                                        {{ $c->nombre_razon_social }} ({{ $c->tipo }})
                                    </option>
                                @endforeach
                            </select>
                            @error('cliente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Rol en el Acto <span class="text-danger">*</span></label>
                            <select name="rol_parte" class="form-select">
                                @foreach(['adquirente' => 'Adquirente', 'enajenante' => 'Enajenante', 'otorgante' => 'Otorgante', 'socio' => 'Socio', 'deudor' => 'Deudor', 'acreedor' => 'Acreedor'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('rol_parte', 'adquirente') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Instrumento No.</label>
                                <input type="text" name="instrumento_no" class="form-control" value="{{ old('instrumento_no') }}" placeholder="Ej. 14,520">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Fecha Otorgamiento <span class="text-danger">*</span></label>
                                <input type="date" name="fecha_otorgamiento" class="form-control @error('fecha_otorgamiento') is-invalid @enderror" value="{{ old('fecha_otorgamiento', date('Y-m-d')) }}" required>
                                @error('fecha_otorgamiento')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Precio Pactado (MXN) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0.01" name="valor_precio" class="form-control @error('valor_precio') is-invalid @enderror" value="{{ old('valor_precio') }}" required>
                                @error('valor_precio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Valor Catastral (MXN)</label>
                                <input type="number" step="0.01" min="0" name="valor_catastral" class="form-control" value="{{ old('valor_catastral', '0') }}">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contribuciones y Accesorios (se excluyen de umbral)</label>
                            <input type="number" step="0.01" min="0" name="contribuciones_accesorios" class="form-control" value="{{ old('contribuciones_accesorios', '0') }}">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar y Evaluar Umbral</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Listado de Actos</h5></div>
                <div class="card-body table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Instrumento</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>UMAs (UMA)</th>
                                <th>Estado</th>
                                <th>Acciones y Pagos (Art. 32/33)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($actos as $acto)
                                @php
                                    $badgeClass = match($acto->estado) {
                                        'bloqueado_efectivo' => 'bg-danger',
                                        'confirmado'         => 'bg-success',
                                        'avisado'            => 'bg-primary',
                                        'evaluado'           => 'bg-info text-dark',
                                        default              => 'bg-secondary',
                                    };
                                @endphp
                                <tr>
                                    <td>{{ $acto->instrumento_no ?? 'S/N' }}</td>
                                    <td>{{ $acto->apartado }}-{{ $acto->tipo_inciso }}</td>
                                    <td>{{ $acto->fecha_otorgamiento?->format('Y-m-d') }}</td>
                                    <td>
                                        <strong>{{ number_format($acto->valor_en_umas, 2) }}</strong>
                                        <div class="text-muted f-s-11">${{ number_format($acto->uma_valor_aplicado, 2) }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $badgeClass }}">{{ $acto->estado }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1 mb-1">
                                            @if(in_array($acto->estado, ['borrador', 'evaluado']))
                                                <form method="POST" action="{{ route('atestia.actos.confirm', $acto->id) }}">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-success">Confirmar</button>
                                                </form>
                                            @endif
                                            <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#pago-form-{{ $acto->id }}">
                                                + Pago
                                            </button>
                                        </div>
                                        <div class="collapse mt-1" id="pago-form-{{ $acto->id }}">
                                            <form method="POST" action="{{ route('atestia.actos.pagos.store', $acto->id) }}" class="p-2 border rounded bg-light">
                                                @csrf
                                                <div class="row g-1">
                                                    <div class="col-6">
                                                        <select name="forma" class="form-select form-select-sm">
                                                            <option value="transferencia">Transferencia</option>
                                                            <option value="cheque_caja">Cheque Caja</option>
                                                            <option value="efectivo">Efectivo</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-6">
                                                        <input type="number" step="0.01" min="0.01" name="monto" class="form-control form-control-sm" placeholder="Monto" required>
                                                    </div>
                                                    <div class="col-7">
                                                        <input type="hidden" name="fecha" value="{{ date('Y-m-d') }}">
                                                        <input type="text" name="comprobante_ref" class="form-control form-control-sm" placeholder="Referencia / Comprobante">
                                                    </div>
                                                    <div class="col-5">
                                                        <button type="submit" class="btn btn-sm btn-primary w-100">Guardar</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $actos->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
