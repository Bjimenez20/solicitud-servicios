<!DOCTYPE html>
<html lang="es">

<x-head />

<style>
    .hero-section {
        background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.5)),
        url("{{ asset('assets/img/banner-1.png') }}") no-repeat center center;
        background-size: cover;
        background-position: center;
        min-height: 70vh;
        color: white;
        border-bottom-right-radius: 80px;
    }
</style>

<body class="bg-white">

    <!-- Navbar Superior -->
    <x-navbar />

    <!-- Hero Section con la imagen corporativa -->
    <section class="hero-section d-flex align-items-center">
        <div class="container py-5">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Overall TrackField</h1>
                    <p class="lead text-light mb-4">Elige el servicio a solicitar</p>
                    @php
                    $isProvider = auth()->user()->isProvider();
                    $routePrefix = $isProvider ? 'provider.' : 'client.';
                    $appointmentButtonText = $isProvider
                    ? 'Agendar Mantenimiento o Auditoría'
                    : 'Agendar Mantenimiento';
                    @endphp

                    <div class="d-flex flex-wrap gap-3">
                        <a href="{{ route($routePrefix . 'requests.create') }}" class="btn btn-light text-dark fw-bold px-4 py-3 rounded-3">
                            Agendar Instalación
                        </a>
                        <a href="{{ route($routePrefix . 'services.select') }}" class="btn btn-light text-dark fw-bold px-4 py-3 rounded-3">
                            {{ $appointmentButtonText }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <x-footer />

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>