<!DOCTYPE html>
<html lang="es">

<x-head />

<body class="bg-light">

    <!-- Navbar Global Uniforme -->
    <x-navbar />

    <!-- Contenido Principal Estilo E-commerce -->
    <div class="container py-5">

        <!-- Cabecera de la sección -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold text-dark mb-1">Mis Solicitudes</h2>
                <p class="text-muted small mb-0">Historial y seguimiento de sus instalaciones y mantenimientos solicitados.</p>
            </div>
            <a href="{{ route('provider.requests.create') }}" class="btn btn-primary fw-bold px-4 py-2 rounded-3 shadow-sm d-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-2"></i> Nueva Solicitud
            </a>
        </div>

        <!-- Alerta de éxito -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Alerta de error -->
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Tarjeta contenedora de la tabla -->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark fs-6"><i class="bi bi-card-list me-2 text-primary"></i> Listado de Pedidos y Servicios</h5>
                @if(method_exists($requests, 'total'))
                <span class="badge bg-light text-muted border rounded-pill px-3 py-2 small">
                    Mostrando {{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} de {{ $requests->total() }}
                </span>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-muted tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Productos / Equipos</th>
                                <th class="py-3 px-4">Dirección</th>
                                <th class="py-3 px-4">Programación</th>
                                <th class="py-3 px-4">Instalación Programada</th>
                                <th class="py-3 px-4">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            @php
                            $addressDisplay = $req->client?->address ?? $req->address ?? 'N/A';
                            $cityDisplay = $req->client?->city ?? $req->city ?? 'N/A';
                            @endphp
                            <tr>
                                <td class="py-3 px-4">
                                    <!-- Botón interactivo que despliega el modal con los productos del carrito -->
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#providerMyModal{{ $req->id }}">
                                        <i class="bi bi-box-seam me-1"></i> Ver Productos ({{ $req->items->sum('quantity') }})
                                    </button>

                                    <!-- Modal de Detalle de Productos y Confirmación de Instalación -->
                                    <div class="modal fade" id="providerMyModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header bg-light border-0">
                                                    <h5 class="modal-title fw-bold text-dark fs-6">
                                                        <i class="bi bi-bag-check text-primary me-2"></i> Detalle de Equipos - Solicitud #{{ $req->id }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <p class="text-muted small mb-3">
                                                        <strong>Dirección:</strong> {{ $addressDisplay }}<br>
                                                        <strong>Ciudad:</strong> {{ $cityDisplay }}
                                                    </p>

                                                    <div class="table-responsive mb-3">
                                                        <table class="table table-sm align-middle">
                                                            <thead class="table-light text-uppercase fs-7 text-muted">
                                                                <tr>
                                                                    <th>Producto / Dispensador</th>
                                                                    <th class="py-3 px-4">Cantidad</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($req->items as $item)
                                                                <tr>
                                                                    <td class="fw-semibold text-dark">{{ $item->product?->name ?? 'Producto no disponible' }}</td>
                                                                    <td class="py-3 px-4 fw-bold text-primary">{{ $item->quantity }}</td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <!-- CONDICIÓN: Botón para confirmar instalación únicamente si está en estado despachado -->
                                                    @if($req->status === 'despachado')
                                                    <div class="alert alert-info border-0 shadow-sm rounded-3 py-2 px-3 small text-info-emphasis mb-3">
                                                        <i class="bi bi-info-circle-fill me-1"></i> Su pedido ha sido despachado. Por favor, confirme una vez finalizada la instalación.
                                                    </div>
                                                    <form action="{{ route('provider.requests.updateStatus', $req) }}" method="POST" class="d-grid">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="hidden" name="status" value="instalado">
                                                        <button type="submit" class="btn btn-success fw-bold py-2 rounded-pill shadow-sm">
                                                            <i class="bi bi-check2-all me-1"></i> Confirmar Instalación
                                                        </button>
                                                    </form>
                                                    @elseif($req->status === 'instalado' || $req->status === 'finalizada' || $req->status === 'realizado')
                                                    <div class="alert alert-success text-center small rounded-pill py-2 mb-0">
                                                        <i class="bi bi-check-circle-fill me-1"></i> Instalación Confirmada
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer border-0">
                                                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 text-secondary small">
                                    <i class="bi bi-geo-alt me-1 text-danger"></i> {{ $addressDisplay }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge bg-light text-dark border px-2 py-1 text-uppercase fw-bold fs-7">
                                        {{ $req->type }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-secondary small">
                                    @if($req->scheduled_date)
                                    <div class="small fw-bold text-success">
                                        <i class="bi bi-calendar-check me-1"></i> {{ \Carbon\Carbon::parse($req->scheduled_date)->format('d/m/Y') }}
                                    </div>
                                    <div class="small text-muted">{{ $req->scheduled_time }}</div>
                                    @if($req->installer?->name)
                                    <div class="small text-primary fw-semibold mt-1">
                                        <i class="bi bi-person-badge me-1"></i> {{ $req->installer->name }}
                                    </div>
                                    @endif
                                    @else
                                    <span class="badge bg-secondary-subtle text-secondary">Por programar</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    @if($req->status === 'pendiente')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-clock-history me-1"></i> Pendiente
                                    </span>
                                    @elseif($req->status === 'confirmada')
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-check-circle me-1"></i> Confirmada
                                    </span>
                                    @elseif($req->status === 'despachado')
                                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-truck me-1"></i> Despachado
                                    </span>
                                    @elseif($req->status === 'cancelada')
                                    <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-x-circle me-1"></i> Cancelada
                                    </span>
                                    @else
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-check-circle-fill me-1"></i> {{ ucfirst($req->status) }}
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-inbox display-4"></i></div>
                                    <h6 class="fw-bold text-dark">No hay solicitudes registradas</h6>
                                    <p class="text-muted small mb-3">Aún no cuenta con pedidos de instalación o mantenimiento.</p>
                                    <a href="{{ route('provider.requests.create') }}" class="btn btn-sm btn-outline-primary fw-bold">Crear mi primera solicitud</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>