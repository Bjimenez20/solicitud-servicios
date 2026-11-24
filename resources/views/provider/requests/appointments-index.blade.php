<!DOCTYPE html>
<html lang="es">

<x-head />

<style>
    .pagination {
        margin-bottom: 0;
        gap: 4px;
    }

    .pagination .page-link {
        border-radius: 0.5rem;
        color: #1b3a6b;
        border: 1px solid #dee2e6;
        font-weight: 600;
        padding: 0.375rem 0.75rem;
    }

    .pagination .page-item.active .page-link {
        background-color: #1b3a6b;
        border-color: #1b3a6b;
        color: #ffffff;
    }

    .pagination .page-link:hover {
        background-color: #f1f5f9;
        color: #132b4e;
    }
</style>

<body class="bg-light">

    <x-navbar />

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('provider.requests.hub') }}" class="text-decoration-none text-muted small fw-semibold d-block mb-1">
                    <i class="bi bi-arrow-left me-1"></i> Volver al Panel de Gestión
                </a>
                <h2 class="fw-bold text-dark mb-0">Agendamientos Programados</h2>
                <p class="text-muted small mb-0">Aprobación y seguimiento de citas presenciales de mantenimiento y auditoría.</p>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark fs-6"><i class="bi bi-calendar-event me-2 text-success"></i> Citas Pendientes y Confirmadas</h5>
                @if(method_exists($appointments, 'total'))
                <span class="badge bg-light text-muted border rounded-pill px-3 py-2 small">
                    Mostrando {{ $appointments->firstItem() ?? 0 }}–{{ $appointments->lastItem() ?? 0 }} de {{ $appointments->total() }}
                </span>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-muted">
                            <tr>
                                <th class="py-3 px-4">ID / Servicio</th>
                                <th class="py-3 px-4">Cliente</th>
                                <th class="py-3 px-4">Fecha y Hora</th>
                                <th class="py-3 px-4">Dirección</th>
                                <th class="py-3 px-4">Equipos</th>
                                <th class="py-3 px-4">Estado</th>
                                <th class="py-3 px-4 text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($appointments as $app)
                            @php
                            $clientProfile = $app->client;
                            $clientNameDisplay = $clientProfile?->business_name
                            ?? $clientProfile?->user?->name
                            ?? 'Cliente N/A';
                            $addressDisplay = $clientProfile?->address ?? 'N/A';
                            $cityDisplay = $clientProfile?->city ?? 'N/A';
                            @endphp
                            <tr>
                                <td class="py-3 px-4">
                                    <div class="fw-bold text-dark">#{{ $app->id }}</div>
                                    <span class="badge bg-info-subtle text-info-emphasis border px-2 py-1 rounded-pill text-uppercase" style="font-size: 0.7rem;">
                                        {{ $app->type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="fw-semibold text-dark">{{ $clientNameDisplay }}</div>
                                </td>
                                <td class="py-3 px-4 fw-semibold text-primary">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ \Carbon\Carbon::parse($app->scheduled_date)->format('d/m/Y') }} - {{ $app->scheduled_time }}
                                </td>
                                <td class="py-3 px-4 text-secondary small">
                                    <div><i class="bi bi-geo-alt text-danger me-1"></i> {{ $addressDisplay }}</div>
                                    <div class="text-muted">{{ $cityDisplay }}</div>
                                </td>
                                <td class="py-3 px-4">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" data-bs-toggle="modal" data-bs-target="#itemsModal{{ $app->id }}">
                                        <i class="bi bi-box-seam me-1"></i> Ver ({{ $app->items->sum('quantity') }})
                                    </button>
                                </td>
                                <td class="py-3 px-4">
                                    @if($app->status === 'pendiente')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border px-3 py-2 rounded-pill fw-bold">Pendiente</span>
                                    @elseif($app->status === 'confirmada')
                                    <span class="badge bg-primary-subtle text-primary border px-3 py-2 rounded-pill fw-bold">Confirmada</span>
                                    @elseif($app->status === 'realizado')
                                    <span class="badge bg-success-subtle text-success border px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-check-all me-1"></i> Realizado
                                    </span>
                                    @elseif($app->status === 'Agendado')
                                    <span class="badge bg-info-subtle text-info border px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-calendar-event me-1"></i> Agendado
                                    </span>
                                    @else
                                    <span class="badge bg-danger-subtle text-danger border px-3 py-2 rounded-pill fw-bold">{{ ucfirst($app->status) }}</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4 text-end">
                                    @if($app->status === 'pendiente')
                                    <div class="d-inline-flex gap-2">
                                        <form action="{{ route('provider.requests.updateStatus', $app->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="confirmada">
                                            <button type="submit" class="btn btn-sm btn-success fw-bold rounded-3" title="Aprobar Cita">
                                                <i class="bi bi-check-lg"></i> Aprobar
                                            </button>
                                        </form>
                                        <form action="{{ route('provider.requests.updateStatus', $app->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="cancelada">
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold rounded-3" title="Rechazar Cita">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @elseif($app->status === 'confirmada')
                                    <span class="text-primary small fw-semibold"><i class="bi bi-clock me-1"></i> Servicio confirmado</span>
                                    @elseif($app->status === 'realizado')
                                    <span class="text-success small fw-semibold"><i class="bi bi-shield-check me-1"></i> Servicio completado</span>
                                    @elseif($app->status === 'Agendado')
                                    <span class="text-info small fst-italic"><i class="bi bi-calendar-event me-1"></i> Servicio agendado</span>
                                    @else
                                    <span class="text-danger small fst-italic">Servicio cancelado</span>
                                    @endif
                                </td>
                            </tr>

                            <!-- Modal para ver equipos vinculados -->
                            <div class="modal fade" id="itemsModal{{ $app->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow rounded-4">
                                        <div class="modal-header border-bottom">
                                            <h6 class="modal-title fw-bold">Equipos Vinculados - Cita #{{ $app->id }}</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <p class="small text-muted mb-3">{{ $app->message_subject }}</p>
                                            <ul class="list-group list-group-flush">
                                                @forelse($app->items as $item)
                                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                                    <span class="fw-medium">{{ $item->product?->name ?? 'Producto #' . $item->product_id }}</span>
                                                    <span class="badge bg-primary rounded-pill">Cant: {{ $item->quantity }}</span>
                                                </li>
                                                @empty
                                                <li class="list-group-item text-muted small px-0">No hay productos específicos asociados.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-calendar-x display-4 d-block mb-2"></i>
                                    No hay agendamientos registrados por el momento.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($appointments, 'hasPages') && $appointments->hasPages())
            <div class="card-footer bg-white py-3 px-4 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div class="text-muted small">
                    Página {{ $appointments->currentPage() }} de {{ $appointments->lastPage() }}
                </div>
                <div>
                    {{ $appointments->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>