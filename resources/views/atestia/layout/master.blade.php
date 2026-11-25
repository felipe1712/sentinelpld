<!DOCTYPE html>
<html lang="es">

<head>
    @include('atestia.layout.head')
    @include('atestia.layout.css')
</head>

<body>
<div class="app-wrapper">
    <div class="loader-wrapper">
        <div class="loader_16"></div>
    </div>

    @include('atestia.layout.sidebar')

    <div class="app-content">
        @include('atestia.layout.header')

        <main>
            @if (session('success'))
                <div class="container-fluid pt-3">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                </div>
            @endif
            @if (session('warning'))
                <div class="container-fluid pt-3">
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                </div>
            @endif
            @if (session('error'))
                <div class="container-fluid pt-3">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                </div>
            @endif
            @if ($errors->any())
                <div class="container-fluid pt-3">
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Corrige los siguientes campos:</strong>
                        <ul class="mb-0 mt-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
                    </div>
                </div>
            @endif

            @yield('main-content')
        </main>
    </div>

    <div class="go-top">
        <span class="progress-value">
            <i class="ti ti-arrow-up"></i>
        </span>
    </div>

    @include('atestia.layout.footer')
</div>

@include('atestia.layout.script')
</body>
</html>
