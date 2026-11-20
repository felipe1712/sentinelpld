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
                            <label class="form-label">Inciso LFPIORPI</label>
                            <select name="tipo_inciso" class="form-select" required>
                                @foreach($catalogo as $inciso => $info)
                                    <option value="{{ $inciso }}">{{ strtoupper($apartado) }}-{{ $inciso }} · {{ $info['nombre'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Compareciente / Cliente</label>
                            <select name="cliente_id" class="form-select" required>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre_razon_social }} ({{ $c->tipo }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Rol en el Acto</label>
                            <select name="rol_parte" class="form-select">
                                <option value="adquirente">Adquirente</option>
                                <option value="enajenante">Enajenante</option>
                                <option value="otorgante">Otorgante</option>
                                <option value="socio">Socio</option>
                                <option value="deudor">Deudor</option>
                                <option value="acreedor">Acreedor</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Instrumento No.</label>
                                <input type="text" name="instrumento_no" class="form-control" placeholder="Ej. 14,520">
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Fecha Otorgamiento</label>
                                <input type="date" name="fecha_otorgamiento" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="form-label">Precio Pactado (MXN)</label>
                                <input type="number" step="0.01" name="valor_precio" class="form-control" required>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="form-label">Valor Catastral (MXN)</label>
                                <input type="number" step="0.01" name="valor_catastral" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contribuciones y Accesorios (se excluyen de umbral)</label>
                            <input type="number" step="0.01" name="contribuciones_accesorios" class="form-control" value="0">
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
                                <th>UMAs</th>
                                <th>Estado</th>
                                <th>Registrar Pago (Art. 32/33)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($actos as $acto)
                                <tr>
                                    <td>{{ $acto->instrumento_no ?? 'S/N' }}</td>
                                    <td>{{ $acto->apartado }}-{{ $acto->tipo_inciso }}</td>
                                    <td>{{ $acto->fecha_otorgamiento?->format('Y-m-d') }}</td>
                                    <td><strong>{{ number_format($acto->valor_en_umas, 2) }}</strong></td>
                                    <td>
                                        <span class="badge {{ $acto->estado === 'bloqueado_efectivo' ? 'bg-danger' : 'bg-secondary' }}">
                                            {{ $acto->estado }}
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('atestia.actos.pagos.store', $acto->id) }}" class="d-flex gap-1">
                                            @csrf
                                            <select name="forma" class="form-select form-select-sm" style="width:120px">
                                                <option value="transferencia">Transferencia</option>
                                                <option value="cheque_caja">Cheque Caja</option>
                                                <option value="efectivo">Efectivo</option>
                                            </select>
                                            <input type="number" step="0.01" name="monto" class="form-control form-control-sm" placeholder="Monto" style="width:100px" required>
                                            <input type="hidden" name="fecha" value="{{ date('Y-m-d') }}">
                                            <input type="text" name="comprobante_ref" class="form-control form-control-sm" placeholder="Ref." style="width:90px">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">+</button>
                                        </form>
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
