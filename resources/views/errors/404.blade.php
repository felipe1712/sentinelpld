@extends('atestia.layout.master')
@section('title', 'Página no encontrada (404)')
@section('main-content')
<div class="container py-5 text-center">
    <img src="{{ asset('assets/images/logo/sentinel-logo.svg') }}" alt="Sentinel PLD" style="height:48px;" class="mb-4">
    <h1 class="display-4 fw-bold text-primary">404</h1>
    <h4 class="mb-3">Recurso no encontrado</h4>
    <p class="text-muted mb-4">La ruta solicitada no existe o fue movida en Sentinel PLD.</p>
    <a href="{{ url('/app/dashboard') }}" class="btn btn-primary">Volver al Inicio</a>
</div>
@endsection
