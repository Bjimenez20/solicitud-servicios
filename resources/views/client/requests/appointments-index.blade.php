<!DOCTYPE html>
<html lang="es">

<x-head />

<body class="bg-light">

    <x-navbar />

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1">Mis Agendamientos</h2>
                <p class="text-muted small mb-0">Control y seguimiento de sus citas de mantenimiento y auditoría programadas.</p>
            </div>
            <a href="{{ route('client.services.select') }}" class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm">
                <i class="bi bi-calendar-plus me-2"></i> Agendar Nueva Cita
            </a>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="mb-0 fw-bold text-dark fs-6"><i class="bi bi-calendar-event me-2 text-success"></i> Listado de Citas Programadas</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-muted">
                            <tr>
                                <th class="py-3 px-4">Servicio</th>
                                <th class="py-3 px-4">Fecha y Hora</th>
                                <th class="py-3 px-4">Ubicación / Dirección</th>
                                <th class="py-3 px-4">Equipos Intervenidos</th>
                                <th class="py-3 px-4">Estado</th>
                                <th class="py-3 px-4 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $app)
                            <tr>
                                <td class="py-3 px-4">
                                    <span class="badge bg-info-subtle text-info-emphasis border px-3 py-2 rounded-pill fw-bold text-uppercase">
                                        {{ $app->type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 fw-semibold text-dark">
                                    <i class="bi bi-clock me-1 text-primary"></i>
                                    {{ \Carbon\Carbon::parse($app->scheduled_date)->format('d/m/Y') }} - {{ $app->scheduled_time }}
                                </td>
                                <td class="py-3 px-4 text-secondary small">
                                    <i class="bi bi-geo-alt text-danger me-1"></i> {{ $app->address }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="text-secondary small">
                                        {{ $app->items->sum('quantity') }} equipos vinculados
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    @if($app->status === 'pendiente')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-hourglass-split me-1"></i> Pendiente de aprobación
                                    </span>
                                    @elseif($app->status === 'confirmada')
                                    <span class="badge bg-primary-subtle text-primary border px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-calendar-check me-1"></i> Cita Confirmada
                                    </span>
                                    @elseif($app->status === 'realizado')
                                    <span class="badge bg-success-subtle text-success border px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i> Realizado
                                    </span>
                                    @else
                                    <span class="badge bg-danger-subtle text-danger border px-3 py-2 rounded-pill fw-bold">
                                        {{ ucfirst($app->status) }}
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    @if($app->status === 'confirmada')
                                    <form action="{{ route('client.requests.updateStatus', $app->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="realizado">
                                        <button type="submit" class="btn btn-sm btn-success fw-bold rounded-3 shadow-sm" onclick="return confirm('¿Confirmas que el técnico ya realizó este servicio en tu punto?')">
                                            <i class="bi bi-check2-circle me-1"></i> Confirmar Realizado
                                        </button>
                                    </form>
                                    @elseif($app->status === 'realizado')
                                    <span class="text-success small fw-semibold"><i class="bi bi-shield-check me-1"></i> Servicio completado</span>
                                    @else
                                    <span class="text-muted small fst-italic">Esperando confirmación</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-calendar-x display-4"></i></div>
                                    <h6 class="fw-bold text-dark">No tienes agendamientos activos</h6>
                                    <p class="text-muted small mb-3">Aún no cuenta con citas de auditoría o mantenimiento registradas.</p>
                                    <a href="{{ route('client.services.select') }}" class="btn btn-sm btn-success fw-bold">Agendar mi primera cita</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>