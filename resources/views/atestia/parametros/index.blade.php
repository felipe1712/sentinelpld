@extends('atestia.layout.master')
@section('title', 'Parámetros Normativos')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Parámetros Normativos (UMA, Umbrales y Anexo 12-A)</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Descripción y Fuente</th>
                        <th>Vigente Desde</th>
                        <th>Valor</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parametros as $param)
                        <tr>
                            <td><code>{{ $param->clave }}</code></td>
                            <td>
                                <div>{{ $param->descripcion }}</div>
                                <small class="text-muted">{{ $param->fuente }}</small>
                            </td>
                            <td>{{ $param->vigente_desde?->format('Y-m-d') }}</td>
                            <td>
                                <form method="POST" action="{{ route('atestia.parametros.update', $param->id) }}" class="d-flex gap-1">
                                    @csrf
                                    @method('PUT')
                                    <input type="text" name="valor" value="{{ $param->valor }}" class="form-control form-control-sm" style="width:160px">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
