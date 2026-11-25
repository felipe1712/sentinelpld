@extends('atestia.layout.master')
@section('title', 'Acceso denegado (403)')
@section('main-content')
<div class="container py-5 text-center">
    <img src="{{ asset('assets/images/logo/sentinel-logo.svg') }}" alt="Sentinel PLD" style="height:48px;" class="mb-4">
    <h1 class="display-4 fw-bold text-danger">403</h1>
    <h4 class="mb-3">Acceso denegado</h4>
    <p class="text-muted mb-4">No cuentas con permisos suficientes para consultar esta sección de Sentinel PLD.</p>
    <a href="{{ url('/app/dashboard') }}" class="btn btn-primary">Volver al Inicio</a>
</div>
@endsection
