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
                <li class="nav-item">
                    <a class="nav-link text-dark fw-bold active" href="{{ route('provider.requests.hub') }}">
                        <i class="bi bi-grid-fill me-1 text-primary"></i> Panel de Gestión
                    </a>
                </li>
                @elseif(auth()->user()->role === 'bodega')
                <!-- Menú exclusivo para Bodega -->
                <li class="nav-item">
                    <a class="nav-link text-dark fw-bold active" href="{{ route('bodega.requests.index') }}">
                        <i class="bi bi-box-seam me-1 text-warning"></i> Control de Despachos
                    </a>
                </li>
                @else
                <!-- Menú para Cliente -->
                <li class="nav-item"><a class="nav-link text-dark fw-semibold" href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="nav-item"><a class="nav-link text-secondary" href="{{ route('client.services.select') }}">Agendamientos</a></li>
                <li class="nav-item"><a class="nav-link text-secondary" href="{{ route('client.requests.create') }}">Solicitudes</a></li>
                @endif
                @endauth
            </ul>
        </div>

        <!-- Zona Derecha: Botón de Agendar Cita + Perfil -->
        <div class="d-flex align-items-center gap-3">
            <!-- Perfil / Cierre de Sesión -->
            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle border-0 fw-semibold" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    @auth
                    @if(!auth()->user()->isProvider() && auth()->user()->role !== 'bodega')
                    <!-- Opción extra para móviles donde el botón superior se oculta -->
                    <li><a class="dropdown-item d-lg-none" href="{{ route('client.requests.create') }}">Agendar Cita</a></li>
                    <li><a class="dropdown-item" href="{{ route('client.requests.hub') }}">Mis Solicitudes</a></li>
                    <li>
                        <hr class="dropdown-divider">
                    </li>
                    @endif
                    @endauth
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">Cerrar Sesión</button>
                        </form>
                    </li>
                </ul>
            </div>
            @auth
            @if(!auth()->user()->isProvider() && auth()->user()->role !== 'bodega')
            <a href="{{ route('client.services.select') }}" class="btn btn-primary text-white fw-bold px-3 py-2 rounded-pill shadow-sm d-none d-lg-inline-block">
                <i class="bi bi-plus-circle me-1"></i> Agendar Cita
            </a>
            @endif
            @endauth
        </div>
    </div>
</nav>