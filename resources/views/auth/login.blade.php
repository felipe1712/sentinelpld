<!DOCTYPE html>
<html lang="es">
<head>
    @section('title', 'Iniciar Sesión')
    @include('atestia.layout.head')
    @include('atestia.layout.css')
</head>
<body style="background-color: #eef3f0;">
<div class="app-wrapper d-flex align-items-center justify-content-center min-vh-100 py-4">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 16px;">
                    <div class="row g-0">
                        <div class="col-lg-5 d-none d-lg-flex flex-column justify-content-between p-4 text-white" style="background-color: #0B4229;">
                            <div>
                                <div class="d-inline-flex align-items-center bg-white px-3 py-2 rounded-3 mb-4">
                                    <img src="{{ asset('assets/images/logo/sentinel-logo.svg') }}" alt="{{ config('app.name') }}" style="height:34px;width:auto;">
                                </div>
                                <h4 class="text-white fw-bold mb-3">Cumplimiento LFPIORPI para Fedatarios Públicos</h4>
                                <p class="f-s-14 opacity-75">
                                    Metodología EBR, monitoreo de umbrales en UMAs, control de efectivo y calendario de avisos conforme al Acuerdo 115/2026.
                                </p>
                            </div>
                            <div class="pt-3 border-top border-light border-opacity-25 f-s-12 opacity-75">
                                © {{ date('Y') }} {{ config('app.name') }} · Acceso confidencial
                            </div>
                        </div>

                        <div class="col-lg-7 bg-white p-4 p-md-5">
                            <div class="d-lg-none mb-4 text-center">
                                <img src="{{ asset('assets/images/logo/sentinel-logo.svg') }}" alt="{{ config('app.name') }}" style="height:38px;width:auto;">
                            </div>

                            <h3 class="fw-bold mb-1" style="color: #0B4229;">¡Bienvenido de nuevo!</h3>
                            <p class="text-muted mb-4">Inicia sesión en la plataforma confidencial de <strong>{{ config('app.name') }}</strong>.</p>

                            @if ($errors->any())
                                <div class="alert alert-danger f-s-13">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" class="app-form">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                                           class="form-control @error('email') is-invalid @enderror"
                                           placeholder="notario@sentinelpld.mx" required autofocus>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold">Contraseña</label>
                                    <input type="password" name="password" id="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           placeholder="Ingresa tu contraseña" required>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label text-secondary" for="remember">Recordarme</label>
                                    </div>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}" class="text-decoration-none f-s-13" style="color: #0B4229;">
                                            ¿Olvidaste tu contraseña?
                                        </a>
                                    @endif
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold" style="background-color: #0B4229; border-color: #0B4229;">
                                    Iniciar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('atestia.layout.script')
</body>
</html>
