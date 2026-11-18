@php
$routePrefix = auth()->user()->isProvider() ? 'provider.' : 'client.';
@endphp

<!DOCTYPE html>
<html lang="es">

<x-head />

<style>
    .service-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid rgba(0, 0, 0, .08);
    }

    .service-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
    }

    .service-img {
        height: 180px;
        object-fit: cover;
        background-color: #f8f9fa;
    }

    .btn-corporate {
        background-color: #1b3a6b;
        color: white;
    }

    .btn-corporate:hover {
        background-color: #132b4e;
        color: white;
    }
</style>

<body class="bg-light">

    <!-- Navbar Global -->
    <x-navbar />

    <!-- Contenido Principal -->
    <div class="container py-5">

        <!-- Fila superior: Título -->
        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h2 class="fw-bold text-dark mb-0">Seleccione su cita</h2>
            </div>
        </div>

        <!-- Contenedor de Tarjetas de Selección -->
        <div class="row g-4" id="serviceList">

            <!-- Opción 1: Agendamiento Auditoría -->
            @if(auth()->user()->isProvider())
            <div class="col-md-4 col-lg-3 service-item" data-name="agendamiento auditoria">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden service-card">
                    <img src="{{ asset('assets/img/tarjeta-1.png') }}"
                        class="card-img-top service-img" alt="Auditoría">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <div>
                            <h5 class="card-title fw-bold text-dark fs-5 mb-2">Agendamiento Auditoría</h5>
                            <p class="card-text text-muted small mb-4">
                                <i class="bi bi-geo-alt text-danger me-1"></i> Presencial en punto
                            </p>
                        </div>
                        <a href="{{ route($routePrefix . 'services.book', ['tipo' => 'Auditoría']) }}" class="btn btn-corporate w-100 fw-bold py-2 rounded-3 shadow-sm">
                            Reservar ahora
                        </a>
                    </div>
                </div>
            </div>
            @endif
            <!-- Opción 2: Agendar Mantenimiento -->
            <div class="col-md-4 col-lg-3 service-item" data-name="agendar mantenimiento e instalacion">
                <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden service-card">
                    <img src="{{ asset('assets/img/tarjeta-2.jfif') }}"
                        class="card-img-top service-img" alt="Mantenimiento">
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <div>
                            <h5 class="card-title fw-bold text-dark fs-5 mb-2">Agendar Mantenimiento</h5>
                            <p class="card-text text-muted small mb-4">
                                <i class="bi bi-geo-alt text-danger me-1"></i> Presencial en punto
                            </p>
                        </div>
                        <a href="{{ route($routePrefix . 'services.book', ['tipo' => 'Mantenimiento']) }}" class="btn btn-corporate w-100 fw-bold py-2 rounded-3 shadow-sm">
                            Reservar ahora
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Script rápido de búsqueda interactiva (protegido si no hay input en pantalla) -->
    <script>
        const searchInput = document.getElementById('serviceSearch');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                let filter = this.value.toLowerCase();
                let items = document.querySelectorAll('.service-item');

                items.forEach(item => {
                    let name = item.getAttribute('data-name');
                    item.style.display = name.includes(filter) ? '' : 'none';
                });
            });
        }
    </script>

    <x-footer />
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>