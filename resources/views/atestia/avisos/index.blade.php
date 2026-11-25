@extends('atestia.layout.master')
@section('title', 'Calendario de Avisos')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Calendario de Avisos LFPIORPI (Día 17 del mes siguiente)</h4>
            <p class="text-muted">Uso exclusivo del Notario y Coordinador (Art. 63 fr. II). Los acuses se almacenan cifrados.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Canal</th>
                        <th>Fecha Límite</th>
                        <th>Actos Incluidos</th>
                        <th>Estado</th>
                        <th>Folio / Acuse</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($avisos as $aviso)
                        @php
                            $actosAviso = collect((array) $aviso->acto_ids)
                                ->map(fn ($aid) => $actosById->get($aid))
                                ->filter();
                        @endphp
                        <tr>
                            <td>{{ strtoupper($aviso->tipo) }}</td>
                            <td><span class="badge bg-info">{{ strtoupper($aviso->canal) }}</span></td>
                            <td><strong>{{ $aviso->fecha_limite?->format('Y-m-d') }}</strong></td>
                            <td>
                                @if($actosAviso->isNotEmpty())
                                    <ul class="list-unstyled mb-0 f-s-12">
                                        @foreach($actosAviso as $a)
                                            <li>
                                                <a href="{{ route('atestia.actos.index') }}">
                                                    {{ $a->instrumento_no ?? 'S/N' }}
                                                </a>
                                                ({{ $a->apartado }}-{{ $a->tipo_inciso }} · {{ number_format($a->valor_en_umas, 2) }} UMA)
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="text-muted f-s-12">Sin actos enlazados</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $aviso->estado === 'pendiente_de_formato' ? 'bg-warning text-dark' : 'bg-primary' }}">
                                    {{ $aviso->estado }}
                                </span>
                            </td>
                            <td>
                                @if($aviso->acuse_folio)
                                    <span class="text-success fw-bold">{{ $aviso->acuse_folio }}</span>
                                @else
                                    <form method="POST" action="{{ route('atestia.avisos.acuse', $aviso->id) }}" class="d-flex gap-1">
                                        @csrf
                                        <input type="text" name="acuse_folio" class="form-control form-control-sm" placeholder="Folio de acuse SAT/UIF" required>
                                        <button type="submit" class="btn btn-sm btn-success">Registrar Acuse</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $avisos->links() }}
        </div>
    </div>
</div>
@endsection
