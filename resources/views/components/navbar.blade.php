<nav class="navbar navbar-expand-lg navbar-light bg-white py-2 shadow-sm">
    <div class="container">
        <!-- Logo / Marca -->
        <a class="navbar-brand fw-bold d-flex align-items-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('assets/img/Overall 1.png') }}" alt="Logo" class="me-2" style="height: 40px;">
        </a>

        <!-- Botón responsive para móviles -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menú de Navegación Condicional por Rol -->
        <div class="collapse navbar-collapse justify-content-center" id="navbarNav">
            <ul class="navbar-nav gap-3">
                @auth
                @if(auth()->user()->isProvider())
                <!-- Menú para Proveedor -->
                <li class="nav-item">
                    <a class="nav-link text-dark fw-semibold" href="{{ route('dashboard') }}">
                        <i class="bi bi-house-door me-1 text-primary"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-secondary" href="{{ route('provider.services.select') }}">
                        <i class="bi bi-calendar-check me-1 text-primary"></i> Agendamientos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-secondary" href="{{ route('provider.requests.create') }}">
                        <i class="bi bi-file-earmark-plus me-1 text-primary"></i> Solicitudes
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-dark fw-bold active" href="{{ route('provider.requests.hub') }}">
                        <i class="bi bi-grid-fill me-1 text-primary"></i> Panel de Gestión
                    </a>
                </li>
                @elseif(auth()->user()->role === 'bodega')
                <!-- Menú exclusivo para Bodega -->
                <li class="nav-item">
                    <a class="nav-link text-dark fw-bold active" href="{{ route('bodega.requests.index') }}">
                        <i class="bi bi-box-seam-fill me-1 text-warning"></i> Control de Agendamientos y Despachos
                    </a>
                </li>
                @else
                <!-- Menú para Cliente -->
                <li class="nav-item">
                    <a class="nav-link text-dark fw-semibold" href="{{ route('dashboard') }}">
                        <i class="bi bi-house-door me-1 text-success"></i> Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-secondary" href="{{ route('client.services.select') }}">
                        <i class="bi bi-calendar2-week me-1 text-success"></i> Agendamientos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-secondary" href="{{ route('client.requests.create') }}">
                        <i class="bi bi-clipboard-plus me-1 text-success"></i> Solicitudes
                    </a>
                </li>
                @endif
                @endauth
            </ul>
        </div>

        <!-- Zona Derecha: Perfil con Icono por Rol + Botón de Agendar Cita -->
        <div class="d-flex align-items-center gap-3">
            <!-- Perfil / Cierre de Sesión -->
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle border rounded-pill px-3 py-1 fw-semibold d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                    @auth
                    @if(auth()->user()->isProvider())
                    <span class="badge bg-primary-subtle text-primary rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="bi bi-shield-check"></i>
                    </span>
                    <span class="small text-dark d-none d-sm-inline">Proveedor - {{ auth()->user()->name }}</span>
                    @elseif(auth()->user()->role === 'bodega')
                    <span class="badge bg-warning-subtle text-warning-emphasis rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="bi bi-box-seam-fill"></i>
                    </span>
                    <span class="small text-dark d-none d-sm-inline">Backoffice - {{ auth()->user()->name }}</span>
                    @else
                    <span class="badge bg-success-subtle text-success rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                        <i class="bi bi-building"></i>
                    </span>
                    <span class="small text-dark d-none d-sm-inline">Cliente - {{ auth()->user()->name }}</span>
                    @endif
                    @else
                    <i class="bi bi-person-circle"></i>
                    @endauth
                </button>

                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                    @auth
                    <!-- Cabecera del dropdown con nombre y rol -->
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-bold text-dark small">{{ auth()->user()->name }}</div>
                        <div class="text-muted" style="font-size: 0.75rem;">
                            @if(auth()->user()->isProvider())
                            <i class="bi bi-shield-check text-primary me-1"></i> Rol: Proveedor
                            @elseif(auth()->user()->role === 'bodega')
                            <i class="bi bi-box-seam-fill text-warning me-1"></i> Rol: Backoffice
                            @else
                            <i class="bi bi-building text-success me-1"></i> Rol: Cliente
                            @endif
                        </div>
                    </li>

                    @if(auth()->user()->role !== 'bodega')
                    <!-- Opción extra para móviles donde el botón superior se oculta -->
                    <li>
                        <a class="dropdown-item d-lg-none" href="{{ auth()->user()->isProvider() ? route('provider.services.select') : route('client.services.select') }}">
                            <i class="bi bi-plus-circle me-2 text-primary"></i> Agendar Cita
                        </a>
                    </li>

                    @if(auth()->user()->isProvider())
                    <li>
                        <a class="dropdown-item" href="{{ route('provider.requests.hub') }}">
                            <i class="bi bi-grid-fill me-2 text-primary"></i> Panel de Gestión
                        </a>
                    </li>
                    @else
                    <li>
                        <a class="dropdown-item" href="{{ route('client.requests.hub') }}">
                            <i class="bi bi-folder2-open me-2 text-success"></i> Mis Solicitudes
                        </a>
                    </li>
                    @endif

                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    @endif
                    @endauth
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
                            </button>
                        </form>
                    </li>
                </ul>
            </div>

            @auth
            @if(auth()->user()->role !== 'bodega')
            <a href="{{ auth()->user()->isProvider() ? route('provider.services.select') : route('client.services.select') }}" class="btn btn-primary text-white fw-bold px-3 py-2 rounded-pill shadow-sm d-none d-lg-inline-block">
                <i class="bi bi-plus-circle me-1"></i> Agendar Cita
            </a>
            @endif
            @endauth
        </div>
    </div>
</nav>