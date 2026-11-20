<header class="header-main">
    <div class="container-fluid">
        <div class="row">
            <div class="col-6 col-sm-4 d-flex align-items-center header-left p-0">
                <span class="header-toggle me-3">
                    <i class="ti ti-category"></i>
                </span>
            </div>

            <div class="col-6 col-sm-8 d-flex align-items-center justify-content-end header-right p-0">
                <ul class="d-flex align-items-center">
                    <li class="header-profile">
                        <a href="#" class="d-block head-icon" role="button" data-bs-toggle="offcanvas" data-bs-target="#profilecanvasRight" aria-controls="profilecanvasRight">
                            <span class="badge bg-light-primary text-primary px-3 py-2">
                                <i class="ti ti-user me-1"></i>
                                {{ auth()->user()?->name ?? 'Usuario' }}
                            </span>
                        </a>

                        <div class="offcanvas offcanvas-end header-profile-canvas" tabindex="-1" id="profilecanvasRight" aria-labelledby="profilecanvasRight">
                            <div class="offcanvas-body app-scroll">
                                <ul>
                                    <li>
                                        <div class="d-flex-center">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ auth()->user()?->name ?? 'Usuario' }}</h6>
                                                <p class="f-s-12 mb-0 text-secondary">{{ auth()->user()?->email ?? '' }}</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="app-divider-v dotted py-1"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="btn btn-light-danger btn-sm w-100 justify-content-center">
                                                <i class="ti ti-logout pe-1"></i> Cerrar sesión
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
