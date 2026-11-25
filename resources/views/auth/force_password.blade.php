@extends('atestia.layout.master')
@section('title', 'Cambio Obligatorio de Contraseña')
@section('main-content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h5 class="mb-0">Cambio Obligatorio de Contraseña</h5></div>
                <div class="card-body">
                    <p class="text-muted f-s-13">Por política de seguridad de Sentinel PLD, debes establecer una contraseña nueva de al menos 12 caracteres (con mayúsculas, minúsculas, números y símbolos).</p>
                    <form method="POST" action="{{ route('password.force.update') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="12" required>
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" minlength="12" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Actualizar Contraseña</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
