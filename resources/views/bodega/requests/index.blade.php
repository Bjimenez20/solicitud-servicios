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
                <h2 class="fw-bold text-dark mb-1"><i class="bi bi-box-seam text-warning me-2"></i> Control de Agendamientos y Despachos</h2>
                <p class="text-muted small mb-0">Programe la fecha de instalación y gestione la salida de equipos desde bodega.</p>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark fs-6"><i class="bi bi-truck me-2 text-primary"></i> Bandeja de Despachos</h5>
                @if(method_exists($requests, 'total'))
                <span class="badge bg-light text-muted border rounded-pill px-3 py-2 small">
                    Mostrando {{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} de {{ $requests->total() }}
                </span>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white small text-uppercase">
                        <tr>
                            <th class="ps-4 py-3">ID</th>
                            <th>Cliente / Distribuidor</th>
                            <th>Dirección / Ciudad</th>
                            <th>Equipos</th>
                            <th>Instalación Programada</th>
                            <th>Estado</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                        @php
                        $clientProfile = $req->client;
                        $providerProfile = $req->provider;

                        $clientDisplayName = $clientProfile?->business_name
                        ?? $clientProfile?->user?->name
                        ?? 'Cliente N/A';

                        $providerDisplayName = $providerProfile?->business_name
                        ?? $providerProfile?->user?->name
                        ?? null;

                        $addressDisplay = $clientProfile?->address
                        ?? $providerProfile?->address
                        ?? 'N/A';

                        $cityDisplay = $clientProfile?->city
                        ?? $providerProfile?->city
                        ?? 'N/A';
                        @endphp
                        <tr>
                            <td class="ps-4 fw-bold text-primary">#{{ $req->id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $clientDisplayName }}</div>
                                @if($providerDisplayName)
                                <div class="text-muted small">Proveedor: {{ $providerDisplayName }}</div>
                                @elseif($clientProfile?->phone)
                                <div class="text-muted small">Tel: {{ $clientProfile->phone }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="small text-dark">{{ $addressDisplay }}</div>
                                <span class="badge bg-light text-secondary border">{{ $cityDisplay }}</span>
                            </td>
                            <td>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($req->items as $item)
                                    <li><span class="fw-bold">{{ $item->quantity }}x</span> {{ $item->product->name ?? 'Producto' }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td>
                                @if($req->scheduled_date)
                                <div class="small fw-bold text-success">
                                    <i class="bi bi-calendar-check me-1"></i> {{ \Carbon\Carbon::parse($req->scheduled_date)->format('d/m/Y') }}
                                </div>
                                <div class="small text-muted">{{ $req->scheduled_time }}</div>
                                @if($req->installer)
                                <div class="small text-primary fw-semibold mt-1">
                                    <i class="bi bi-person-badge me-1"></i> {{ $req->installer->name }}
                                </div>
                                @endif
                                @else
                                <span class="badge bg-secondary-subtle text-secondary">Sin agendar</span>
                                @endif
                            </td>
                            <td>
                                @php
                                $badgeClass = match($req->status) {
                                'pendiente' => 'bg-warning-subtle text-warning-emphasis',
                                'confirmada' => 'bg-info-subtle text-info-emphasis',
                                'despachado' => 'bg-primary-subtle text-primary',
                                'instalado' => 'bg-success-subtle text-success',
                                'cancelada' => 'bg-danger-subtle text-danger',
                                default => 'bg-secondary-subtle text-secondary',
                                };
                                @endphp
                                <span class="badge {{ $badgeClass }} rounded-pill px-3 py-2 text-uppercase">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                @if($req->status === 'confirmada')
                                <a href="{{ route('bodega.requests.scheduleForm', $req->id) }}" class="btn btn-sm btn-primary fw-bold rounded-3 px-3 shadow-sm">
                                    <i class="bi bi-calendar-plus me-1"></i> Agendar y Despachar
                                </a>
                                @elseif($req->status === 'despachado')
                                <span class="text-muted small">Sin acciones</span>
                                <!-- <a href="{{ route('bodega.requests.scheduleForm', $req->id) }}" class="btn btn-sm btn-outline-secondary rounded-3 me-1" title="Reprogramar cita">
                                    <i class="bi bi-calendar-event"></i>
                                </a>

                                <form action="{{ route('bodega.requests.updateStatus', $req->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="instalado">
                                    <button type="submit" class="btn btn-sm btn-success fw-bold rounded-3 px-3">
                                        <i class="bi bi-check2-all me-1"></i> Marcar Instalado
                                    </button>
                                </form> -->
                                @else
                                <span class="text-muted small">Sin acciones</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox display-6 d-block mb-2"></i>
                                No hay solicitudes pendientes de despacho en este momento.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($requests, 'hasPages') && $requests->hasPages())
            <div class="card-footer bg-white py-3 px-4 border-top d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <div class="text-muted small">
                    Página {{ $requests->currentPage() }} de {{ $requests->lastPage() }}
                </div>
                <div>
                    {{ $requests->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>