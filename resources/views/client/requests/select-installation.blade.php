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

    .card-installation {
        transition: all 0.2s ease;
        cursor: pointer;
        border: 2px solid #e2e8f0;
        background: white;
    }

    .card-installation:hover {
        border-color: var(--primary-corporate);
        transform: translateY(-2px);
    }

    .card-installation.selected {
        border-color: var(--primary-corporate);
        background-color: #eff6ff;
    }
</style>

<body>

    <x-navbar />

    <div class="container py-5">

        <!-- Barra de progreso -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <a href="{{ route('client.services.book') }}?tipo={{ $type }}" class="text-decoration-none text-muted fw-semibold small d-flex align-items-center">
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

        <form action="{{ route('client.services.storeMaintenance') }}" method="POST" id="linkInstallationForm">
            @csrf
            <input type="hidden" name="scheduled_date" value="{{ $date }}">
            <input type="hidden" name="scheduled_time" value="{{ $time }}">
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="service_request_id" id="selectedRequestId" required>

            <div class="row g-4 mb-4">
                @forelse($installedRequests as $req)
                <div class="col-md-6">
                    <div class="card card-installation p-4 rounded-4 shadow-sm h-100" onclick="selectInstallation(this, '{{ $req->id }}')">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill fw-bold mb-2">
                                    <i class="bi bi-check-circle-fill me-1"></i> Instalado
                                </span>
                                <h5 class="fw-bold text-dark mb-0">Solicitud #{{ $req->id }}</h5>
                                <span class="text-muted small">{{ $req->distributor_name ?? 'Punto Principal' }}</span>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input fs-5" type="radio" name="installation_radio" value="{{ $req->id }}" id="radio{{ $req->id }}">
                            </div>
                        </div>

                        <hr class="opacity-25 my-2">

                        <!-- Datos de Ubicación -->
                        <div class="bg-light p-3 rounded-3 mb-3 small">
                            <div class="mb-1">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                <strong>Dirección:</strong> {{ $req->address }}
                            </div>
                            <div>
                                <i class="bi bi-building text-primary me-1"></i>
                                <strong>Ciudad:</strong> {{ $req->city ?? 'No especificada' }}
                            </div>
                        </div>

                        <!-- Listado de Equipos / Productos vinculados -->
                        <div class="small">
                            <strong class="text-dark d-block mb-2"><i class="bi bi-box-seam text-primary me-1"></i> Equipos a intervenir:</strong>
                            <ul class="list-group list-group-flush border rounded-3">
                                @forelse($req->items as $item)
                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-3 bg-transparent">
                                    <span class="text-secondary fw-medium">{{ $item->product?->name ?? 'Equipo #' . $item->product_id }}</span>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill">Cant: {{ $item->quantity }}</span>
                                </li>
                                @empty
                                <li class="list-group-item text-muted fst-italic py-2 px-3 bg-transparent">Sin equipos detallados</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                        <div class="text-muted mb-2"><i class="bi bi-inbox display-4"></i></div>
                        <h5 class="fw-bold text-dark">No tienes instalaciones previas confirmadas</h5>
                        <p class="text-muted small mb-3">Para agendar un servicio sobre un punto instalado, primero debes tener una solicitud en estado <strong>Instalado</strong>.</p>
                        <div>
                            <a href="{{ route('client.requests.index') }}" class="btn btn-primary fw-bold px-4 rounded-pill">Ir a Mis Solicitudes</a>
                        </div>
                    </div>
                </div>
                @endforelse
            </div>

            @if($installedRequests->isNotEmpty())
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center bg-white p-4 rounded-4 shadow-sm border gap-3">
                <span class="text-muted small" id="selectionHelperText">
                    <i class="bi bi-info-circle text-primary me-1"></i> Haz clic sobre una tarjeta para seleccionar el punto de servicio.
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
        function selectInstallation(cardElement, requestId) {
            document.querySelectorAll('.card-installation').forEach(c => c.classList.remove('selected'));
            cardElement.classList.add('selected');

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