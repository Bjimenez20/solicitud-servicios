<!DOCTYPE html>
<html lang="es">

<x-head />

<style>
    .hover-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .hover-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08) !important;
    }
</style>


<body class="bg-light">
    <x-navbar />

    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-bold mb-2">Portal Operativo</span>
            <h2 class="fw-bold text-dark">Gestión de Operaciones - Proveedor</h2>
            <p class="text-muted">Seleccione el módulo que desea aprobar y administrar.</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Tarjeta 1: Solicitudes de Compra / Instalación -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center hover-card">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="bg-primary-subtle text-primary p-3 rounded-circle d-inline-flex mb-3 fs-3">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Solicitudes de Instalación</h4>
                            <p class="text-muted small mb-4">Revise pedidos de equipos, valide datos comerciales y apruebe las órdenes para despacho de bodega.</p>
                        </div>
                        <div>
                            <a href="{{ route('provider.requests.index') }}" class="btn btn-primary px-4 py-2 rounded-pill fw-bold w-100" style="background-color: #1b3a6b; border: none;">
                                Gestionar Solicitudes <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta 2: Agendamientos / Citas Técnicas -->
            <div class="col-md-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 text-center hover-card">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <div class="bg-success-subtle text-success p-3 rounded-circle d-inline-flex mb-3 fs-3">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <h4 class="fw-bold text-dark mb-2">Agendamientos y Citas</h4>
                            <p class="text-muted small mb-4">Confirme fechas y horarios de visitas técnicas para mantenimientos preventivos y auditorías en punto.</p>
                        </div>
                        <div>
                            <a href="{{ route('provider.appointments.index') }}" class="btn btn-success px-4 py-2 rounded-pill fw-bold w-100">
                                Gestionar Agendamientos <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>