<nav>
    <div class="app-logo">
        <a class="logo d-inline-flex align-items-center text-decoration-none"
           href="{{ auth()->user()?->hasRole('super_admin') ? route('atestia.admin.dashboard') : route('atestia.dashboard') }}">
            <img src="{{ asset('assets/images/logo/sentinel-logo.svg') }}" alt="{{ config('app.name') }}" style="height:36px;width:auto;">
        </a>

        <span class="bg-light-primary toggle-semi-nav">
          <i class="ti ti-chevrons-right f-s-20"></i>
        </span>
    </div>
    <div class="app-nav" id="app-simple-bar">
        <ul class="main-nav p-0 mt-2">
            @if(auth()->user()?->hasRole('super_admin'))
                <li class="menu-title">
                    <span>Plataforma</span>
                </li>
                <li class="no-sub">
                    <a href="{{ route('atestia.admin.dashboard') }}">
                        <i class="ti ti-dashboard"></i>
                        Dashboard
                    </a>
                </li>
                <li class="no-sub">
                    <a href="{{ route('atestia.admin.notarias.index') }}">
                        <i class="ti ti-building"></i>
                        Notarías y Clientes
                    </a>
                </li>
                <li class="no-sub">
                    <a href="{{ route('atestia.usuarios.index') }}">
                        <i class="ti ti-user"></i>
                        Usuarios
                    </a>
                </li>

                <li class="sentinel-menu-divider" aria-hidden="true"></li>
                <li class="menu-title">
                    <span>Sistema</span>
                </li>
                <li class="no-sub">
                    <a href="{{ route('atestia.admin.configuracion.index') }}">
                        <i class="ti ti-plug"></i>
                        Configuración e Integraciones
                    </a>
                </li>
                <li class="no-sub">
                    <a href="{{ route('atestia.parametros.index') }}">
                        <i class="ti ti-adjustments"></i>
                        Parámetros Normativos
                    </a>
                </li>
            @else
                <li class="menu-title">
                    <span>Principal</span>
                </li>
                <li class="no-sub">
                    <a href="{{ route('atestia.dashboard') }}">
                        <i class="ti ti-dashboard"></i>
                        Dashboard
                    </a>
                </li>

                <li class="sentinel-menu-divider" aria-hidden="true"></li>
                <li class="menu-title">
                    <span>Operación</span>
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
                        Expediente Único
                    </a>
                </li>

                @if(auth()->user()?->can('atestia.alertas.revisar') || auth()->user()?->can('atestia.avisos.ver'))
                    <li class="sentinel-menu-divider" aria-hidden="true"></li>
                    <li class="menu-title">
                        <span>Monitoreo</span>
                    </li>
                @endif

                @can('atestia.alertas.revisar')
                    <li class="no-sub">
                        <a href="{{ route('atestia.alertas.index') }}">
                            <i class="ti ti-bell-ringing"></i>
                            Alertas
                        </a>
                    </li>
                @endcan

                @can('atestia.avisos.ver')
                    <li class="no-sub">
                        <a href="{{ route('atestia.avisos.index') }}">
                            <i class="ti ti-calendar-due"></i>
                            Calendario Avisos
                        </a>
                    </li>
                @endcan

                @if(auth()->user()?->can('atestia.auditoria.exportar') || auth()->user()?->can('atestia.parametros.editar'))
                    <li class="sentinel-menu-divider" aria-hidden="true"></li>
                    <li class="menu-title">
                        <span>Gobierno</span>
                    </li>
                @endif

                @can('atestia.auditoria.exportar')
                    <li class="no-sub">
                        <a href="{{ route('atestia.auditoria.index') }}">
                            <i class="ti ti-shield-lock"></i>
                            Auditoría y EBR
                        </a>
                    </li>
                @endcan

                @can('atestia.parametros.editar')
                    <li class="no-sub">
                        <a href="{{ route('atestia.cumplimiento.index') }}">
                            <i class="ti ti-certificate"></i>
                            Cumplimiento Interno
                        </a>
                    </li>
                @endcan

                @can('atestia.usuarios.gestionar')
                    <li class="sentinel-menu-divider" aria-hidden="true"></li>
                    <li class="menu-title">
                        <span>Configuración</span>
                    </li>
                    <li class="no-sub">
                        <a href="{{ route('atestia.usuarios.index') }}">
                            <i class="ti ti-user"></i>
                            Usuarios
                        </a>
                    </li>
                @endcan
            @endif
        </ul>
    </div>

    <div class="menu-navs">
        <span class="menu-previous"><i class="ti ti-chevron-left"></i></span>
        <span class="menu-next"><i class="ti ti-chevron-right"></i></span>
    </div>
</nav>
