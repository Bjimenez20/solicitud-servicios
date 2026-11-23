@php
$isProvider = auth()->user()->isProvider();
$storeMaintenanceRoute = $isProvider
? route('provider.services.storeMaintenance')
: route('client.services.storeMaintenance');
$backToCalendarRoute = $isProvider
? route('provider.services.book', ['tipo' => $type])
: route('client.services.book', ['tipo' => $type]);
$emptyRequestsRoute = $isProvider
? route('provider.requests.index')
: route('client.requests.index');
@endphp
<!DOCTYPE html>
<html lang="es">

<x-head />

<style>
    :root {
        --primary-corporate: #1b3a6b;
    }

    body {
        background-color: #f8fafc;
        color: #334155;
    }

    .row-installation {
        transition: background-color 0.15s ease;
        cursor: pointer;
    }

    .row-installation:hover {
        background-color: #f1f5f9 !important;
    }

    .row-installation.selected {
        background-color: #eff6ff !important;
    }

    .row-installation.selected td:first-child {
        box-shadow: inset 4px 0 0 var(--primary-corporate);
    }
</style>

<body>

    <x-navbar />

    <div class="container py-5">

        <!-- Barra de progreso -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <a href="{{ $backToCalendarRoute }}" class="text-decoration-none text-muted fw-semibold small d-flex align-items-center">
                <i class="bi bi-arrow-left me-2"></i> Volver al calendario
            </a>
            <div class="text-muted small">
                <span>Fecha y hora</span>
                <i class="bi bi-chevron-right mx-2 text-muted" style="font-size: 0.75rem;"></i>
                <span class="text-dark fw-bold">Seleccionar Punto / Equipos</span>
                <i class="bi bi-chevron-right mx-2 text-muted" style="font-size: 0.75rem;"></i> Reservado
            </div>
        </div>

        <div class="mb-4">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-bold mb-2">Paso 2 de 2</span>
            <h2 class="fw-bold text-dark">Selecciona la instalación para {{ strtolower($type) }}</h2>
            <p class="text-muted small">
                Programación elegida: <strong>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</strong> a las <strong>{{ $time }}</strong>.
                Elige la solicitud instalada para heredar automáticamente su dirección, ciudad y equipos asociados.
            </p>
        </div>

        <form action="{{ $storeMaintenanceRoute }}" method="POST" id="linkInstallationForm">
            @csrf
            <input type="hidden" name="scheduled_date" value="{{ $date }}">
            <input type="hidden" name="scheduled_time" value="{{ $time }}">
            <input type="hidden" name="type" value="{{ $type }}">
            @if(isset($clientRecordId))
            <input type="hidden" name="client_record_id" value="{{ $clientRecordId }}">
            @endif
            <input type="hidden" name="service_request_id" id="selectedRequestId" required>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark fs-6">
                        <i class="bi bi-building-check me-2 text-primary"></i> Puntos Instalados Disponibles
                    </h5>
                    <span class="badge bg-light text-secondary border rounded-pill px-3">
                        {{ $installedRequests->count() }} disponibles
                    </span>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light text-uppercase small text-muted">
                                <tr>
                                    <th class="py-3 px-4 text-center" style="width: 60px;">Sel.</th>
                                    <th class="py-3 px-4">Solicitud / Punto</th>
                                    <th class="py-3 px-4">Ubicación</th>
                                    <th class="py-3 px-4">Equipos a Intervenir</th>
                                    <th class="py-3 px-4 text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($installedRequests as $req)
                                @php
                                $clientProfile = $req->client;
                                $puntoLabel = $clientProfile?->business_name
                                ?? $clientProfile?->user?->name
                                ?? 'Punto Principal';
                                $addressDisplay = $clientProfile?->address ?? 'No especificada';
                                $cityDisplay = $clientProfile?->city ?? 'No especificada';
                                @endphp
                                <tr class="row-installation" onclick="selectInstallation(this, '{{ $req->id }}')">
                                    <!-- Radio de selección -->
                                    <td class="py-3 px-4 text-center">
                                        <div class="form-check d-flex justify-content-center m-0">
                                            <input class="form-check-input fs-5"
                                                type="radio"
                                                name="installation_radio"
                                                value="{{ $req->id }}"
                                                id="radio{{ $req->id }}"
                                                onclick="event.stopPropagation(); selectInstallation(this.closest('tr'), '{{ $req->id }}')">
                                        </div>
                                    </td>

                                    <!-- Solicitud y Distribuidor -->
                                    <td class="py-3 px-4">
                                        <div class="fw-bold text-dark">Solicitud #{{ $req->id }}</div>
                                        <div class="text-muted small">{{ $puntoLabel }}</div>
                                    </td>

                                    <!-- Ubicación -->
                                    <td class="py-3 px-4">
                                        <div class="text-dark fw-medium mb-1">
                                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> {{ $addressDisplay }}
                                        </div>
                                        <div class="text-muted">
                                            <i class="bi bi-building text-primary me-1"></i> {{ $cityDisplay }}
                                        </div>
                                    </td>

                                    <!-- Equipos vinculados -->
                                    <td class="py-3 px-4">
                                        @if($req->items->isNotEmpty())
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($req->items as $item)
                                            <span class="badge bg-light text-dark border fw-normal py-1 px-2">
                                                {{ $item->product?->name ?? 'Equipo #' . $item->product_id }}
                                                <strong class="text-primary ms-1">(x{{ $item->quantity }})</strong>
                                            </span>
                                            @endforeach
                                        </div>
                                        @else
                                        <span class="text-muted small fst-italic">Sin equipos detallados</span>
                                        @endif
                                    </td>

                                    <!-- Estado -->
                                    <td class="py-3 px-4 text-center">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold">
                                            <i class="bi bi-check-circle-fill me-1"></i> Instalado
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="p-5 text-center">
                                        <div class="text-muted mb-2"><i class="bi bi-inbox display-4"></i></div>
                                        <h5 class="fw-bold text-dark">No hay instalaciones previas confirmadas</h5>
                                        <p class="text-muted small mb-3">
                                            Para agendar un servicio sobre un punto instalado, primero debe existir una solicitud en estado <strong>Instalado</strong>.
                                        </p>
                                        <a href="{{ $emptyRequestsRoute }}" class="btn btn-primary fw-bold px-4 rounded-pill">
                                            Ir a Solicitudes
                                        </a>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($installedRequests->isNotEmpty())
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center bg-white p-4 rounded-4 shadow-sm border gap-3">
                <span class="text-muted small" id="selectionHelperText">
                    <i class="bi bi-info-circle text-primary me-1"></i> Haz clic sobre una fila para seleccionar el punto de servicio.
                </span>
                <button type="submit" class="btn btn-primary px-5 py-3 fw-bold rounded-3 shadow-sm" style="background-color: #1b3a6b; border: none;">
                    Confirmar y Agendar {{ $type }} <i class="bi bi-check2-all ms-2"></i>
                </button>
            </div>
            @endif
        </form>
    </div>

    <x-footer />

    <script>
        function selectInstallation(rowElement, requestId) {
            document.querySelectorAll('.row-installation').forEach(r => r.classList.remove('selected'));
            rowElement.classList.add('selected');

            document.getElementById('radio' + requestId).checked = true;
            document.getElementById('selectedRequestId').value = requestId;
            document.getElementById('selectionHelperText').innerHTML = '<i class="bi bi-check-circle-fill text-success me-1"></i> Solicitud <strong>#' + requestId + '</strong> seleccionada.';
        }

        document.getElementById('linkInstallationForm')?.addEventListener('submit', function(e) {
            if (!document.getElementById('selectedRequestId').value) {
                e.preventDefault();
                alert('Por favor, selecciona una de tus instalaciones antes de confirmar.');
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>