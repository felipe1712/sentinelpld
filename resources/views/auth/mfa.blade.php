@extends('atestia.layout.master')
@section('title', 'Verificación en Dos Pasos (MFA)')
@section('main-content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Verificación en Dos Pasos (MFA)</h5></div>
                <div class="card-body">
                    <p class="text-muted f-s-13">Ingresa el código de 6 dígitos enviado a tu correo electrónico.</p>
                    <form method="POST" action="{{ route('mfa.verify') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Código MFA <span class="text-danger">*</span></label>
                            <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" maxlength="6" pattern="\d{6}" required autofocus>
                            @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Verificar y Entrar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
