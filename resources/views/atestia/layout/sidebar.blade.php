<nav>
    <div class="app-logo">
        <a class="logo d-inline-flex align-items-center text-decoration-none" href="{{ route('atestia.dashboard') }}">
            <img src="{{ asset('assets/images/logo/sentinel-logo.svg') }}" alt="{{ config('app.name') }}" style="height:36px;width:auto;">
        </a>

        <span class="bg-light-primary toggle-semi-nav">
          <i class="ti ti-chevrons-right f-s-20"></i>
        </span>
    </div>
    <div class="app-nav" id="app-simple-bar">
        <ul class="main-nav p-0 mt-2">
            <li class="menu-title">
                <span>Principal</span>
            </li>
            <li class="no-sub">
                <a href="{{ route('atestia.dashboard') }}">
                    <i class="ti ti-dashboard"></i>
                    Dashboard
                </a>
            </li>

            <li class="menu-title">
                <span>Operación Vulnerable</span>
            </li>
            <li class="no-sub">
                <a href="{{ route('atestia.actos.index') }}">
                    <i class="ti ti-file-certificate"></i>
                    Actos e Instrumentos
                </a>
            </li>
            <li class="no-sub">
                <a href="{{ route('atestia.clientes.index') }}">
                    <i class="ti ti-users"></i>
                    Expediente Único (BC)
                </a>
            </li>

            @if(auth()->user()?->can('atestia.alertas.revisar') || auth()->user()?->can('atestia.avisos.ver'))
                <li class="menu-title">
                    <span>Monitoreo y Avisos</span>
                </li>
            @endif

            @can('atestia.alertas.revisar')
                <li class="no-sub">
                    <a href="{{ route('atestia.alertas.index') }}">
                        <i class="ti ti-bell-ringing"></i>
                        Alertas (A01–A18)
                    </a>
                </li>
            @endcan

            @can('atestia.avisos.ver')
                <li class="no-sub">
                    <a href="{{ route('atestia.avisos.index') }}">
                        <i class="ti ti-calendar-due"></i>
                        Calendario Avisos (Día 17)
                    </a>
                </li>
            @endcan

            @if(auth()->user()?->can('atestia.auditoria.exportar') || auth()->user()?->can('atestia.parametros.editar'))
                <li class="menu-title">
                    <span>Gobierno y Auditoría</span>
                </li>
            @endif

            @can('atestia.auditoria.exportar')
                <li class="no-sub">
                    <a href="{{ route('atestia.auditoria.index') }}">
                        <i class="ti ti-shield-lock"></i>
                        Auditoría y EBR (M3/M8)
                    </a>
                </li>
            @endcan

            @can('atestia.parametros.editar')
                <li class="no-sub">
                    <a href="{{ route('atestia.cumplimiento.index') }}">
                        <i class="ti ti-certificate"></i>
                        Cumplimiento Interno (M9)
                    </a>
                </li>
                <li class="no-sub">
                    <a href="{{ route('atestia.parametros.index') }}">
                        <i class="ti ti-adjustments"></i>
                        Parámetros (UMA / Anexos)
                    </a>
                </li>
            @endcan
        </ul>
    </div>

    <div class="menu-navs">
        <span class="menu-previous"><i class="ti ti-chevron-left"></i></span>
        <span class="menu-next"><i class="ti ti-chevron-right"></i></span>
    </div>
</nav>
