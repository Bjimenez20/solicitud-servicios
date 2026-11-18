<!DOCTYPE html>
<html lang="es">

<x-head />

<style>
    .product-thumb {
        width: 50px;
        height: 50px;
        object-fit: cover;
        background-color: #f8f9fa;
    }
</style>


<body class="bg-light">

    <!-- Tu Navbar Global Uniforme -->
    <x-navbar />

    <!-- Contenido Principal Estilo E-commerce -->
    <div class="container py-5">

        <!-- Cabecera de la sección -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
            <div>
                <h2 class="fw-bold text-dark mb-1">Gestión de Solicitudes</h2>
                <p class="text-muted small mb-0">Panel de administración para validar, confirmar o rechazar servicios solicitados.</p>
            </div>
        </div>

        <!-- Alerta de éxito -->
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Tarjeta contenedora de la tabla -->
        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="mb-0 fw-bold text-dark fs-6"><i class="bi bi-tools me-2 text-primary"></i> Bandeja de Solicitudes de Clientes</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-muted tracking-wider">
                            <tr>
                                <th class="py-3 px-4">Cliente</th>
                                <th class="py-3 px-4">Dirección</th>
                                <th class="py-3 px-4">Programación</th>
                                <th class="py-3 px-4">Instalación Programada</th>
                                <th class="py-3 px-4">Estado</th>
                                <th class="py-3 px-4 text-center">Gestión / Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            <tr>
                                <!-- Cliente -->
                                <td class="py-3 px-4 fw-semibold text-dark">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary-subtle text-secondary p-2 rounded-3 me-3">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div>
                                            {{ $req->client->name ?? 'Cliente N/A' }}
                                            <div class="text-muted small fw-normal">NIT: {{ $req->client_nit ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tipo & Dirección -->
                                <td class="py-3 px-4">

                                    <div class="text-secondary small">
                                        <i class="bi bi-geo-alt text-danger me-1"></i> {{ $req->address }}
                                    </div>
                                </td>

                                <!-- Programación -->
                                <td class="py-3 px-4 text-secondary small">
                                    @if($req->type === 'mantenimiento' && $req->scheduled_date)
                                    <i class="bi bi-calendar-event me-1"></i> {{ \Carbon\Carbon::parse($req->scheduled_date)->format('d/m/Y') }} - {{ $req->scheduled_time }}
                                    @else
                                    <span class="badge bg-light text-dark border px-2 py-1 text-uppercase fw-bold fs-7 mb-1">
                                        {{ $req->type }}
                                    </span>
                                    @endif
                                </td>
                                <!-- Instalación Programada -->
                                <td class="py-3 px-4">
                                    @if($req->scheduled_date)
                                    <div class="small fw-bold text-success">
                                        <i class="bi bi-calendar-check me-1"></i> {{ \Carbon\Carbon::parse($req->scheduled_date)->format('d/m/Y') }}
                                    </div>
                                    <div class="small text-muted">{{ $req->scheduled_time }}</div>
                                    @else
                                    <span class="badge bg-secondary-subtle text-secondary">Sin agendar</span>
                                    @endif
                                </td>

                                <!-- Estado -->
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
                                    @else
                                    <span class="badge bg-success-subtle text-success-emphasis border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-x-circle me-1"></i> {{ ucfirst($req->status) }}
                                    </span>
                                    @endif
                                </td>

                                <!-- Botón Ver Detalle (Modal que incluye la lista de productos y botones de confirmación) -->
                                <td class="py-3 px-4 text-center">
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#detalleModal{{ $req->id }}">
                                        <i class="bi bi-eye me-1"></i> Ver Detalle ({{ $req->items->sum('quantity') }})
                                    </button>

                                    <!-- Modal de Detalle y Gestión -->
                                    <div class="modal fade" id="detalleModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header bg-light border-0 py-3 px-4">
                                                    <h5 class="modal-title fw-bold text-dark fs-6">
                                                        <i class="bi bi-box-seam text-primary me-2"></i> Detalle de Solicitud #{{ $req->id }} - {{ $req->client->name ?? 'Cliente' }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4 text-start">
                                                    <div class="row mb-3 bg-light p-3 rounded-3 small">
                                                        <div class="col-md-6">
                                                            <strong>Dirección:</strong> {{ $req->address }}<br>
                                                            <strong>Ciudad:</strong> {{ $req->city ?? 'N/A' }}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Responsable:</strong> {{ $req->responsible_name ?? 'N/A' }}<br>
                                                            <strong>Teléfono:</strong> {{ $req->responsible_phone ?? 'N/A' }}
                                                        </div>
                                                    </div>

                                                    <h6 class="fw-bold text-dark mb-3 small uppercase"><i class="bi bi-list-check me-1 text-primary"></i> Productos Seleccionados por el Cliente</h6>

                                                    <div class="table-responsive mb-4">
                                                        <table class="table table-sm align-middle">
                                                            <thead class="table-light text-uppercase fs-7 text-muted">
                                                                <tr>
                                                                    <th style="width: 70px;">Foto</th>
                                                                    <th>Producto / Dispensador</th>
                                                                    <th class="text-center">Cantidad</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($req->items as $item)
                                                                <tr>
                                                                    <td>
                                                                        <img src="{{ $item->product?->image ? asset('storage/' . $item->product->image) : 'https://placehold.co/100x100/e9ecef/495057?text=Equipos' }}"
                                                                            class="rounded-3 product-thumb shadow-sm" alt="Producto">
                                                                    </td>
                                                                    <td class="fw-semibold text-dark">
                                                                        {{ $item->product?->name ?? 'Producto no disponible' }}
                                                                    </td>
                                                                    <td class="text-center fw-bold text-primary fs-6">
                                                                        {{ $item->quantity }}
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    @if($req->status === 'pendiente')
                                                    <hr class="text-muted my-3">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <form action="{{ route('provider.requests.updateStatus', $req) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="cancelada">
                                                            <button type="submit" class="btn btn-outline-danger fw-bold px-4 rounded-pill shadow-sm">
                                                                <i class="bi bi-x-lg me-1"></i> Cancelar Solicitud
                                                            </button>
                                                        </form>

                                                        <form action="{{ route('provider.requests.updateStatus', $req) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="confirmada">
                                                            <button type="submit" class="btn btn-success fw-bold px-4 rounded-pill shadow-sm text-white">
                                                                <i class="bi bi-check-lg me-1"></i> Confirmar Solicitud
                                                            </button>
                                                        </form>
                                                    </div>
                                                    @else
                                                    <div class="alert alert-secondary text-center small mb-0 rounded-3">
                                                        <i class="bi bi-lock me-1"></i> Esta solicitud ya se encuentra <strong>{{ ucfirst($req->status) }}</strong> y no admite cambios.
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer border-0 bg-light py-2 px-4">
                                                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Cerrar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-clipboard-x display-4"></i></div>
                                    <h6 class="fw-bold text-dark">No hay solicitudes por gestionar</h6>
                                    <p class="text-muted small mb-0">En este momento no existen requerimientos pendientes de clientes.</p>
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
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>