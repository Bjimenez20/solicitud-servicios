<!DOCTYPE html>
<html lang="es">

<x-head />

<body class="bg-light">

    <x-navbar />

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold text-dark mb-1"><i class="bi bi-box-seam text-warning me-2"></i> Control de Despachos</h2>
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
                        <tr>
                            <td class="ps-4 fw-bold text-primary">#{{ $req->id }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $req->distributor_name }}</div>
                                <div class="text-muted small">Resp: {{ $req->responsible_name }}</div>
                            </td>
                            <td>
                                <div class="small text-dark">{{ $req->address }}</div>
                                <span class="badge bg-light text-secondary border">{{ $req->city }}</span>
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
                                <!-- Obliga a pasar por el calendario antes de marcar como despachado -->
                                <a href="{{ route('bodega.requests.scheduleForm', $req->id) }}" class="btn btn-sm btn-primary fw-bold rounded-3 px-3 shadow-sm">
                                    <i class="bi bi-calendar-plus me-1"></i> Agendar y Despachar
                                </a>
                                @elseif($req->status === 'despachado')
                                <!-- Opción para reprogramar fecha si hubo algún cambio -->
                                <a href="{{ route('bodega.requests.scheduleForm', $req->id) }}" class="btn btn-sm btn-outline-secondary rounded-3 me-1" title="Reprogramar cita">
                                    <i class="bi bi-calendar-event"></i>
                                </a>

                                <form action="{{ route('bodega.requests.updateStatus', $req->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="instalado">
                                    <button type="submit" class="btn btn-sm btn-success fw-bold rounded-3 px-3">
                                        <i class="bi bi-check2-all me-1"></i> Marcar Instalado
                                    </button>
                                </form>
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
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>