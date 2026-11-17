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

    <div class="container py-5">
        <div class="mb-4">
            <h2 class="fw-bold text-dark mb-1">Módulo de Bodega</h2>
            <p class="text-muted small mb-0">Listado de solicitudes listas para gestionar salida y despacho de dispensadores.</p>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h5 class="mb-0 fw-bold text-dark fs-6"><i class="bi bi-box-seam me-2 text-warning"></i> Solicitudes para Despacho</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-uppercase fs-7 text-muted tracking-wider">
                            <tr>
                                <th class="py-3 px-4">ID / Cliente</th>
                                <th class="py-3 px-4">Ciudad / Dirección</th>
                                <th class="py-3 px-4">Estado Actual</th>
                                <th class="py-3 px-4 text-center">Acción de Bodega</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            <tr>
                                <!-- ID / Cliente -->
                                <td class="py-3 px-4 fw-semibold text-dark">
                                    #{{ $req->id }} - {{ $req->client->name ?? 'Cliente' }}
                                    <div class="text-muted small fw-normal">NIT: {{ $req->client_nit ?? 'N/A' }}</div>
                                </td>

                                <!-- Ciudad / Dirección -->
                                <td class="py-3 px-4 text-secondary small">
                                    <i class="bi bi-geo-alt text-danger me-1"></i> {{ $req->city }} - {{ $req->address }}
                                </td>

                                <!-- Estado Actual -->
                                <td class="py-3 px-4">
                                    @if($req->status === 'confirmada')
                                    <span class="badge bg-success-subtle text-success border px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-check-circle me-1"></i> Confirmada
                                    </span>
                                    @elseif($req->status === 'despachado')
                                    <span class="badge bg-info-subtle text-info-emphasis border px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi bi-truck me-1"></i> Despachado
                                    </span>
                                    @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis border px-3 py-2 rounded-pill fw-bold">
                                        {{ ucfirst($req->status) }}
                                    </span>
                                    @endif
                                </td>

                                <!-- Acción de Bodega (Botón Ver Detalle & Modal de Productos) -->
                                <td class="py-3 px-4 text-center">
                                    <button type="button" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold shadow-sm text-dark" data-bs-toggle="modal" data-bs-target="#bodegaModal{{ $req->id }}">
                                        <i class="bi bi-eye me-1"></i> Ver Detalle ({{ $req->items->sum('quantity') }})
                                    </button>

                                    <!-- Modal de Detalle de Productos y Salida de Bodega -->
                                    <div class="modal fade" id="bodegaModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered modal-lg">
                                            <div class="modal-content border-0 shadow-lg rounded-4">
                                                <div class="modal-header bg-light border-0 py-3 px-4">
                                                    <h5 class="modal-title fw-bold text-dark fs-6">
                                                        <i class="bi bi-box-seam text-warning me-2"></i> Despacho - Solicitud #{{ $req->id }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4 text-start">
                                                    <div class="row mb-3 bg-light p-3 rounded-3 small">
                                                        <div class="col-md-6">
                                                            <strong>Cliente:</strong> {{ $req->client->name ?? 'N/A' }}<br>
                                                            <strong>Destino:</strong> {{ $req->city }} - {{ $req->address }}
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Responsable:</strong> {{ $req->responsible_name ?? 'N/A' }}<br>
                                                            <strong>Teléfono:</strong> {{ $req->responsible_phone ?? 'N/A' }}
                                                        </div>
                                                    </div>

                                                    <h6 class="fw-bold text-dark mb-3 small uppercase"><i class="bi bi-list-check me-1 text-warning"></i> Equipos a Despachar</h6>

                                                    <div class="table-responsive mb-4">
                                                        <table class="table table-sm align-middle">
                                                            <thead class="table-light text-uppercase fs-7 text-muted">
                                                                <tr>
                                                                    <th style="width: 70px;">Foto</th>
                                                                    <th>Producto / Dispensador</th>
                                                                    <th class="text-center">Cantidad a Despachar</th>
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
                                                                    <td class="text-center fw-bold text-warning-emphasis fs-6">
                                                                        {{ $item->quantity }}
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    @if($req->status !== 'despachado')
                                                    <hr class="text-muted my-3">
                                                    <div class="d-flex justify-content-end">
                                                        <form action="{{ route('bodega.requests.updateStatus', $req) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="despachado">
                                                            <button type="submit" class="btn btn-warning fw-bold px-4 rounded-pill shadow-sm text-dark">
                                                                <i class="bi bi-truck me-1"></i> Marcar Despachado
                                                            </button>
                                                        </form>
                                                    </div>
                                                    @else
                                                    <div class="alert alert-success text-center small mb-0 rounded-3">
                                                        <i class="bi bi-check-all me-1"></i> Esta solicitud ya fue <strong>Despachada exitosamente</strong>.
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
                                <td colspan="4" class="text-center py-5">
                                    <div class="text-muted mb-2"><i class="bi bi-box display-4"></i></div>
                                    <h6 class="fw-bold text-dark">No hay solicitudes pendientes de despacho</h6>
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