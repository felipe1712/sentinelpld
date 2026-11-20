@extends('atestia.layout.master')
@section('title', 'Alertas A01-A18')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Alertas de Monitoreo (M5 · Catálogo A01–A18)</h4>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Regla</th>
                        <th>Severidad</th>
                        <th>Título</th>
                        <th>Estado</th>
                        <th>SLA</th>
                        <th>Resolución Escrita</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alertas as $alerta)
                        <tr>
                            <td><strong>{{ $alerta->regla_id }}</strong></td>
                            <td>
                                <span class="badge {{ $alerta->severidad === 'CRITICA' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                    {{ $alerta->severidad }}
                                </span>
                            </td>
                            <td>{{ $alerta->titulo }}</td>
                            <td>{{ $alerta->estado }}</td>
                            <td>{{ $alerta->sla_vence_en?->format('Y-m-d H:i') }}</td>
                            <td>
                                @if(in_array($alerta->estado, ['nueva', 'en_revision']))
                                    <form method="POST" action="{{ route('atestia.alertas.resolve', $alerta->id) }}" class="d-flex gap-1">
                                        @csrf
                                        <select name="estado" class="form-select form-select-sm" style="width:130px">
                                            <option value="descartada">Descartar</option>
                                            <option value="escalada">Escalar a Notario</option>
                                            <option value="aviso">Generar Aviso</option>
                                        </select>
                                        <input type="text" name="analisis_escrito" class="form-control form-control-sm" placeholder="Análisis obligatorio..." required minlength="10">
                                        <button type="submit" class="btn btn-sm btn-primary">Cerrar</button>
                                    </form>
                                @else
                                    <span class="text-muted f-s-12">{{ $alerta->analisis_escrito }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $alertas->links() }}
        </div>
    </div>
</div>
@endsection
