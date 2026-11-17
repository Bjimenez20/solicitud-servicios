<!DOCTYPE html>
<html lang="es">

<x-head />

<body class="bg-light">
    <x-navbar />

    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold text-dark">Panel de Gestión de Servicios</h2>
            <p class="text-muted">Seleccione qué módulo desea consultar o administrar.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Tarjeta 1: Solicitudes de Productos -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center hover-card">
                    <div class="card-body">
                        <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-flex mb-3 fs-3">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Mis Solicitudes</h4>
                        <p class="text-muted small mb-4">Consulte el historial de compras de equipos, dispensadores y el estado de sus instalaciones en curso.</p>
                        <a href="{{ route('client.requests.index') }}" class="btn btn-primary px-4 py-2 rounded-pill fw-bold">
                            Ver Solicitudes <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Agendamientos / Citas -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center hover-card">
                    <div class="card-body">
                        <div class="bg-success-subtle text-success p-3 rounded-circle d-inline-flex mb-3 fs-3">
                            <i class="bi bi-calendar-check"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-2">Mis Agendamientos</h4>
                        <p class="text-muted small mb-4">Revise las citas programadas de auditoría y mantenimiento vinculadas a sus puntos instalados.</p>
                        <a href="{{ route('client.appointments.index') }}" class="btn btn-success px-4 py-2 rounded-pill fw-bold">
                            Ver Agendamientos <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>