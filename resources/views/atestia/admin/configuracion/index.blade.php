@extends('atestia.layout.master')
@section('title', 'Configuración del Sistema')

@section('main-content')
<div class="container-fluid">
    <div class="row m-1">
        <div class="col-12">
            <h4 class="main-title">Configuración del Sistema e Integraciones</h4>
            <p class="text-muted">Las llaves API se almacenan cifradas en base de datos.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Integración de Correo Transaccional · Brevo</h5>
                    <span class="badge {{ $brevo['enabled'] ? 'bg-success' : 'bg-secondary' }}">
                        {{ $brevo['enabled'] ? 'Habilitado' : 'Deshabilitado' }}
                    </span>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('atestia.admin.configuracion.brevo') }}">
                        @csrf
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="brevo_enabled" value="1" id="brevoEnabled" {{ $brevo['enabled'] ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="brevoEnabled">Activar envío de correos vía API de Brevo</label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Brevo API Key
                                @if($brevo['has_api_key'])
                                    <span class="badge bg-light-success text-success ms-1">Configurada</span>
                                @endif
                            </label>
                            <input type="password" name="brevo_api_key" class="form-control"
                                   placeholder="{{ $brevo['has_api_key'] ? '•••••••••••••••• (dejar vacío para conservar la actual)' : 'xkeysib-...' }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Correo Remitente</label>
                                <input type="email" name="brevo_sender_email" value="{{ $brevo['sender_email'] }}" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nombre Remitente</label>
                                <input type="text" name="brevo_sender_name" value="{{ $brevo['sender_name'] }}" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar Configuración Brevo</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Probar Envío de Correo</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('atestia.admin.configuracion.brevo.test') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Correo de Destino para Prueba</label>
                            <input type="email" name="test_email" class="form-control" placeholder="tu-correo@dominio.com" required>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100">Enviar Correo de Prueba</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
