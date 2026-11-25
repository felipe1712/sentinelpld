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
                        <th>Título y Detalle / Origen</th>
                        <th>Estado</th>
                        <th>SLA</th>
                        <th style="min-width: 320px;">Resolución Escrita</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($alertas as $alerta)
                        @php $det = (array) $alerta->detalle_json; @endphp
                        <tr>
                            <td><strong>{{ $alerta->regla_id }}</strong></td>
                            <td>
                                <span class="badge {{ $alerta->severidad === 'CRITICA' ? 'bg-danger' : 'bg-warning text-dark' }}">
                                    {{ $alerta->severidad }}
                                </span>
                            </td>
                            <td>
                                <div>{{ $alerta->titulo }}</div>
                                <div class="text-muted f-s-12 mt-1">
                                    @if($alerta->acto)
                                        Acto: <a href="{{ route('atestia.actos.index') }}">{{ $alerta->acto->instrumento_no ?? $alerta->acto->id }}</a>
                                        ({{ $alerta->acto->apartado }}-{{ $alerta->acto->tipo_inciso }}) ·
                                    @endif
                                    @if($alerta->cliente)
                                        Cliente: <a href="{{ route('atestia.clientes.index') }}">{{ $alerta->cliente->nombre_razon_social }}</a> ·
                                    @endif
                                    @if(isset($det['monto']))
                                        Monto: ${{ number_format((float) $det['monto'], 2) }} ·
                                    @endif
                                    @if(isset($det['valor_en_umas']))
                                        {{ number_format((float) $det['valor_en_umas'], 2) }} UMA ·
                                    @endif
                                    @if(isset($det['uma_aplicada']))
                                        UMA: ${{ number_format((float) $det['uma_aplicada'], 2) }}
                                    @endif
                                </div>
                            </td>
                            <td>{{ $alerta->estado }}</td>
                            <td>{{ $alerta->sla_vence_en?->format('Y-m-d H:i') }}</td>
                            <td>
                                @if(in_array($alerta->estado, ['nueva', 'en_revision']))
                                    <form method="POST" action="{{ route('atestia.alertas.resolve', $alerta->id) }}">
                                        @csrf
                                        <div class="d-flex gap-1 mb-1">
                                            <select name="estado" class="form-select form-select-sm">
                                                <option value="descartada">Descartar</option>
                                                <option value="escalada">Escalar a Notario</option>
                                                <option value="aviso">Generar Aviso</option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">Cerrar</button>
                                        </div>
                                        <textarea name="analisis_escrito" rows="2" class="form-control form-control-sm" placeholder="Análisis escrito obligatorio (mín. 10 caracteres)..." required minlength="10"></textarea>
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
