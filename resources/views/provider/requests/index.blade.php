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

    .progress-ring {
        --size: 46px;
        --thickness: 5px;
        --progress: 25%;
        --ring-color: #ffc107;
        --track-color: #e9ecef;

        width: var(--size);
        height: var(--size);
        border-radius: 50%;
        background: conic-gradient(var(--ring-color) 0% var(--progress),
                var(--track-color) var(--progress) 100%);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        flex-shrink: 0;
    }

    .progress-ring::before {
        content: "";
        width: calc(var(--size) - (var(--thickness) * 2));
        height: calc(var(--size) - (var(--thickness) * 2));
        border-radius: 50%;
        background-color: #ffffff;
        position: absolute;
    }

    .progress-ring span {
        position: relative;
        z-index: 1;
        font-size: 0.7rem;
        font-weight: 700;
        color: #212529;
        line-height: 1;
    }

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

    <!-- Navbar Global Uniforme -->
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
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark fs-6"><i class="bi bi-tools me-2 text-primary"></i> Bandeja de Solicitudes de Clientes</h5>
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
                                <th class="py-3 px-4">Cliente</th>
                                <th class="py-3 px-4">Dirección</th>
                                <th class="py-3 px-4">Programación</th>
                                <th class="py-3 px-4">Instalación Programada</th>
                                <th class="py-3 px-4">Estado</th>
                                <th class="py-3 px-4 text-center">Progreso</th>
                                <th class="py-3 px-4 text-center">Gestión / Detalle</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            @php
                            $statusNormalized = strtolower((string) $req->status);
                            $typeNormalized = strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', (string) ($req->type ?? 'instalacion')));
                            $isServiceFlow = in_array($typeNormalized, ['mantenimiento', 'auditoria'], true);

                            $statusMetaMap = [
                            'pendiente' => ['hex' => '#ffc107', 'label' => 'Pendiente', 'icon' => 'bi-clock-history', 'badge' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle'],
                            'confirmada' => ['hex' => '#0d6efd', 'label' => 'Confirmada', 'icon' => 'bi-check-circle', 'badge' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle'],
                            'confirmado' => ['hex' => '#0d6efd', 'label' => 'Confirmado', 'icon' => 'bi-check-circle', 'badge' => 'bg-primary-subtle text-primary-emphasis border-primary-subtle'],
                            'despachado' => ['hex' => '#0dcaf0', 'label' => 'Despachado', 'icon' => 'bi-truck', 'badge' => 'bg-info-subtle text-info-emphasis border-info-subtle'],
                            'agendado' => ['hex' => '#0dcaf0', 'label' => 'Agendado', 'icon' => 'bi-calendar-check','badge' => 'bg-info-subtle text-info-emphasis border-info-subtle'],
                            'instalado' => ['hex' => '#198754', 'label' => 'Instalado', 'icon' => 'bi-house-check', 'badge' => 'bg-success-subtle text-success-emphasis border-success-subtle'],
                            'realizado' => ['hex' => '#198754', 'label' => 'Realizado', 'icon' => 'bi-patch-check', 'badge' => 'bg-success-subtle text-success-emphasis border-success-subtle'],
                            'cancelada' => ['hex' => '#dc3545', 'label' => 'Cancelada', 'icon' => 'bi-x-circle', 'badge' => 'bg-danger-subtle text-danger-emphasis border-danger-subtle'],
                            ];

                            $meta = $statusMetaMap[$statusNormalized] ?? [
                            'hex' => '#6c757d',
                            'label' => ucfirst($req->status),
                            'icon' => 'bi-info-circle',
                            'badge' => 'bg-secondary-subtle text-secondary-emphasis border-secondary-subtle',
                            ];

                            if ($statusNormalized === 'cancelada') {
                            $percent = 100;
                            } elseif ($isServiceFlow) {
                            $percent = match ($statusNormalized) {
                            'pendiente' => 25,
                            'confirmada', 'confirmado', 'agendado' => 50,
                            'realizado', 'instalado' => 100,
                            default => 0,
                            };
                            } else {
                            // Instalación: Pendiente (25%) -> Confirmado (50%) -> Despachado / Agendado (75%) -> Instalado (100%)
                            $percent = match ($statusNormalized) {
                            'pendiente' => 25,
                            'confirmada', 'confirmado' => 50,
                            'despachado', 'agendado' => 75,
                            'instalado', 'realizado' => 100,
                            default => 0,
                            };
                            }

                            $currentStatus = [
                            'percent' => $percent,
                            'hex' => $meta['hex'],
                            'label' => $meta['label'],
                            'icon' => $meta['icon'],
                            'badge' => $meta['badge'],
                            ];

                            $clientProfile = $req->client;
                            $clientDisplayName = $clientProfile?->business_name
                            ?? 'Cliente N/A';
                            $clientNitDisplay = $clientProfile?->nit ?? 'N/A';
                            $addressDisplay = $clientProfile?->address ?? 'N/A';
                            $cityDisplay = $clientProfile?->city ?? 'N/A';
                            $responsibleDisplay = $clientProfile?->contact_name ?? $clientDisplayName;
                            $phoneDisplay = $clientProfile?->phone ?? 'N/A';

                            $providerProfile = $req->provider;
                            $providerDisplayName = $providerProfile?->business_name
                            ?? $providerProfile?->name
                            ?? 'No registrado';
                            $providerNitDisplay = $providerProfile?->nit ?? null;

                            $tariff = $req->tariff_summary;
                            @endphp
                            <tr>
                                <!-- Cliente -->
                                <td class="py-3 px-4 fw-semibold text-dark">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary-subtle text-secondary p-2 rounded-3 me-3">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div>
                                            {{ $clientDisplayName }}
                                            <div class="text-muted small fw-normal">NIT: {{ $clientNitDisplay }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tipo & Dirección -->
                                <td class="py-3 px-4">
                                    <div class="text-secondary small">
                                        <i class="bi bi-geo-alt text-danger me-1"></i> {{ $addressDisplay }}
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
                                    @if($req->installer?->name)
                                    <div class="small text-primary fw-semibold mt-1">
                                        <i class="bi bi-person-badge me-1"></i> {{ $req->installer->name }}
                                    </div>
                                    @endif
                                    @else
                                    <span class="badge bg-secondary-subtle text-secondary">Sin agendar</span>
                                    @endif
                                </td>

                                <!-- Estado -->
                                <td class="py-3 px-4">
                                    <span class="badge {{ $currentStatus['badge'] }} border px-3 py-2 rounded-pill fw-bold">
                                        <i class="bi {{ $currentStatus['icon'] }} me-1"></i> {{ $currentStatus['label'] }}
                                    </span>
                                </td>

                                <!-- Progreso Circular -->
                                <td class="py-3 px-4 text-center">
                                    <div class="progress-ring"
                                        style="--progress: {{ $currentStatus['percent'] }}%; --ring-color: {{ $currentStatus['hex'] }};"
                                        role="progressbar"
                                        aria-label="Progreso de solicitud"
                                        aria-valuenow="{{ $currentStatus['percent'] }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                        title="{{ $currentStatus['label'] }} ({{ $currentStatus['percent'] }}%)">
                                        <span>{{ $currentStatus['percent'] }}%</span>
                                    </div>
                                </td>

                                <!-- Botón Ver Detalle -->
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
                                                        <i class="bi bi-box-seam text-primary me-2"></i> Detalle de Solicitud #{{ $req->id }} - {{ $clientDisplayName }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4 text-start">
                                                    <div class="d-flex align-items-center gap-3 mb-3 bg-light p-3 rounded-3">
                                                        <div class="progress-ring"
                                                            style="--progress: {{ $currentStatus['percent'] }}%; --ring-color: {{ $currentStatus['hex'] }};">
                                                            <span>{{ $currentStatus['percent'] }}%</span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="small fw-bold text-dark">Estado actual: {{ $currentStatus['label'] }}</div>
                                                            @if($isServiceFlow)
                                                            <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.72rem;">
                                                                <span>1. Pendiente (25%)</span>
                                                                <span>2. Agendado (50%)</span>
                                                                <span>3. Realizado (100%)</span>
                                                            </div>
                                                            @else
                                                            <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 0.72rem;">
                                                                <span>1. Pendiente (25%)</span>
                                                                <span>2. Confirmado (50%)</span>
                                                                <span>3. Despachado / Agendado (75%)</span>
                                                                <span>4. Instalado (100%)</span>
                                                            </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    <!-- Proveedor asociado (desde tabla providers) -->
                                                    <div class="row mb-3 bg-light p-3 rounded-3 small border-start border-4 border-primary">
                                                        <div class="col-12 fw-bold text-dark mb-1">
                                                            <i class="bi bi-building-check text-primary me-1"></i>Distribuidor / Origen
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Distribuidor:</strong> {{ $providerDisplayName }}<br>
                                                            @if($providerNitDisplay)
                                                            <strong>NIT Distribuidor:</strong> {{ $providerNitDisplay }}<br>
                                                            @endif
                                                            <strong>Fecha de creación:</strong> {{ $req->created_at ? $req->created_at->format('d/m/Y h:i A') : 'N/A' }}
                                                        </div>
                                                    </div>

                                                    <!-- Datos del cliente / punto -->
                                                    <div class="row mb-3 bg-light p-3 rounded-3 small border-start border-4 border-primary">
                                                        <div class="col-12 fw-bold text-dark mb-1">
                                                            <i class="bi bi-person-check-fill text-primary me-1"></i> Información de instalación
                                                        </div>
                                                        <div class="row mb-3 bg-light p-3 rounded-3 small">
                                                            <div class="col-md-6">
                                                                <strong>Cliente a instalar:</strong> {{ $responsibleDisplay }}<br>
                                                                <strong>Teléfono:</strong> {{ $phoneDisplay }}
                                                            </div>
                                                            <div class="col-md-6">
                                                                <strong>Dirección:</strong> {{ $addressDisplay }}<br>
                                                                <strong>Ciudad:</strong> {{ $cityDisplay }}
                                                            </div>
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

                                                    <!-- Tarifario de Transporte e Instalación (para instalaciones) -->
                                                    @if($typeNormalized === 'instalacion')
                                                    <div class="border rounded-4 p-3 bg-light mb-4">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <h6 class="fw-bold text-dark mb-0 small text-uppercase">
                                                                <i class="bi bi-receipt-cutoff text-primary me-1"></i> Tarifario de Transporte e Instalación
                                                            </h6>
                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3">
                                                                {{ $tariff['total_units'] }} {{ $tariff['total_units'] === 1 ? 'dispensador' : 'dispensadores' }}
                                                            </span>
                                                        </div>

                                                        <div class="d-flex align-items-center justify-content-between bg-white border rounded-3 p-3 mb-3">
                                                            <div class="d-flex align-items-center gap-3">
                                                                <div class="bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                                                    <i class="bi {{ $tariff['vehicle_icon'] }} fs-5"></i>
                                                                </div>
                                                                <div>
                                                                    <div class="text-muted" style="font-size: 0.72rem;">Vehículo asignado para transporte</div>
                                                                    <div class="fw-bold text-dark">{{ $tariff['vehicle_type'] }}</div>
                                                                </div>
                                                            </div>
                                                            <span class="badge bg-light text-secondary border px-2 py-1 small">
                                                                @if($tariff['total_units'] <= 4)
                                                                    1 – 4 uds
                                                                    @elseif($tariff['total_units'] <=9)
                                                                    5 – 9 uds
                                                                    @else
                                                                    10+ uds
                                                                    @endif
                                                                    </span>
                                                        </div>

                                                        <div class="bg-white border rounded-3 p-3 small">
                                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                                <span class="text-muted">Transporte ({{ $tariff['vehicle_type'] }}):</span>
                                                                <span class="fw-semibold text-dark">${{ number_format($tariff['transport_rate'], 0, ',', '.') }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between py-2 border-bottom">
                                                                <span class="text-muted">Instalación ({{ $tariff['total_units'] }} × ${{ number_format($tariff['unit_installation_price'], 0, ',', '.') }}):</span>
                                                                <span class="fw-semibold text-dark">${{ number_format($tariff['installation_total'], 0, ',', '.') }}</span>
                                                            </div>
                                                            <div class="d-flex justify-content-between pt-2 fw-bold text-success fs-6">
                                                                <span>Total del servicio:</span>
                                                                <span>${{ number_format($tariff['grand_total'], 0, ',', '.') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    @if($statusNormalized === 'pendiente')
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
                                                        <i class="bi bi-lock me-1"></i> Esta solicitud ya se encuentra <strong>{{ $currentStatus['label'] }}</strong> y no admite cambios.
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
                                <td colspan="7" class="text-center py-5">
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