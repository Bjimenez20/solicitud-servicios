<!DOCTYPE html>
<html lang="es">

<x-head />

<body class="bg-light">
    <x-navbar />

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary mb-1 px-3 py-1 rounded-pill fw-bold">Gestión de Bodega</span>
                <h2 class="fw-bold text-dark mb-0">Agendar Fecha, Hora e Instalador</h2>
                <p class="text-muted small mb-0">Selecciona primero el día y la hora; luego el sistema listará los instaladores disponibles para la solicitud #{{ $serviceRequest->id }}.</p>
            </div>
            <a href="{{ route('bodega.requests.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Volver al listado
            </a>
        </div>

        @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
        </div>
        @endif

        <form action="{{ route('bodega.requests.storeSchedule', $serviceRequest->id) }}" method="POST" id="scheduleForm">
            @csrf
            <input type="hidden" name="scheduled_date" id="selectedDate" value="{{ old('scheduled_date', $serviceRequest->scheduled_date) }}">
            <input type="hidden" name="scheduled_time" id="selectedTime" value="{{ old('scheduled_time', $serviceRequest->scheduled_time) }}">

            <div class="row g-4">
                <!-- COLUMNA IZQUIERDA Y CENTRO: Calendario, Horarios e Instalador -->
                <div class="col-lg-8">
                    <div class="row g-4 mb-4">
                        <!-- Calendario interactivo -->
                        <div class="col-md-7">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="fw-bold mb-0 text-dark" id="currentMonthYear">Noviembre de 2026</h5>
                                    <div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle me-1" id="prevMonth"><i class="bi bi-chevron-left"></i></button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle" id="nextMonth"><i class="bi bi-chevron-right"></i></button>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table text-center table-borderless mb-0 align-middle" style="font-size: 0.9rem;">
                                        <thead>
                                            <tr class="text-muted small">
                                                <th>DOM</th>
                                                <th>LUN</th>
                                                <th>MAR</th>
                                                <th>MIÉ</th>
                                                <th>JUE</th>
                                                <th>VIE</th>
                                                <th>SÁB</th>
                                            </tr>
                                        </thead>
                                        <tbody id="calendarDays"></tbody>
                                    </table>
                                </div>
                                <div class="mt-3 pt-3 border-top text-muted small">
                                    <i class="bi bi-calendar-check text-primary me-1"></i> Fecha elegida: <span id="selectedDateText" class="fw-bold text-dark">Ninguna</span>
                                </div>
                            </div>
                        </div>

                        <!-- Horarios Disponibles -->
                        <div class="col-md-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock text-primary me-2"></i> 1. Elige la Hora</h6>
                                    <div class="d-flex flex-column gap-2" id="timeSlotsContainer">
                                        @php
                                        $hours = ['07:00:00', '08:00:00', '09:00:00', '10:00:00', '11:00:00', '14:00:00', '15:00:00', '16:00:00'];
                                        @endphp
                                        @foreach($hours as $hour)
                                        <button type="button" class="time-slot-btn btn btn-outline-light text-dark border py-2 px-3 rounded-3 text-start fw-semibold {{ old('scheduled_time', $serviceRequest->scheduled_time) == $hour ? 'active-slot' : '' }}" data-time="{{ $hour }}">
                                            <i class="bi bi-clock-history me-2 text-muted"></i> {{ $hour }}
                                        </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selector de Instalador (Se llena dinámicamente desde la BD tras elegir fecha y hora) -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <label for="installer_id" class="fw-bold text-dark mb-2">
                            <i class="bi bi-person-badge text-primary me-2"></i> 2. Seleccionar Instalador Disponible *
                        </label>
                        <select name="installer_id" id="installer_id" class="form-select rounded-3 py-2" required disabled>
                            <option value="">Primero seleccione fecha y hora...</option>
                        </select>
                        <small class="text-muted mt-2 d-block" id="installerHelperText">Los instaladores disponibles aparecerán una vez elijas el día y la hora.</small>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm text-uppercase" style="background-color: #1b3a6b; border: none;">
                            <i class="bi bi-check2-circle me-1"></i> Guardar y Despachar
                        </button>
                    </div>
                </div>

                <!-- COLUMNA DERECHA: Resumen de Instalación -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-lg rounded-4 sticky-top bg-white" style="top: 20px;">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary-subtle text-primary p-3 rounded-4 me-3">
                                    <i class="bi bi-file-earmark-text fs-4"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">Resumen de Instalación</h6>
                                    <span class="text-muted small">Bodega / Despachos</span>
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            <div class="mb-3">
                                <span class="d-block text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem;">Distribuidor / Cliente</span>
                                <span class="fw-bold text-dark">{{ $serviceRequest->distributor_name }}</span>
                                <div class="text-muted small">{{ $serviceRequest->address }}, {{ $serviceRequest->city }}</div>
                            </div>

                            <div class="mb-3">
                                <span class="d-block text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem;">Equipos a Instalar</span>
                                <ul class="list-unstyled mb-0 small">
                                    @foreach($serviceRequest->items as $item)
                                    <li><span class="fw-bold">{{ $item->quantity }}x</span> {{ $item->product->name ?? 'Equipo' }}</li>
                                    @endforeach
                                </ul>
                            </div>

                            <div class="mb-3">
                                <span class="d-block text-uppercase text-muted fw-bold mb-1" style="font-size: 0.75rem;">Modalidad</span>
                                <span class="text-dark small"><i class="bi bi-geo-alt text-danger me-1"></i> Presencial en punto</span>
                            </div>

                            <div class="p-3 bg-light rounded-3 border-0 mt-4">
                                <div class="d-flex align-items-start">
                                    <i class="bi bi-info-circle-fill text-primary me-2 mt-1"></i>
                                    <p class="small text-muted mb-0">Al guardar, se asignará el instalador seleccionado, se fijará el horario y la solicitud pasará a estado <strong>despachado</strong>.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <style>
        .time-slot-btn.active-slot {
            background-color: #1b3a6b !important;
            color: white !important;
            border-color: #1b3a6b !important;
        }

        .time-slot-btn.active-slot i {
            color: #ffc107 !important;
        }

        .calendar-day {
            cursor: pointer;
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .calendar-day:hover {
            background-color: #e9ecef;
        }

        .calendar-day.selected {
            background-color: #1b3a6b;
            color: white;
            font-weight: bold;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const timeButtons = document.querySelectorAll('.time-slot-btn');
            const selectedTimeInput = document.getElementById('selectedTime');
            const selectedDateInput = document.getElementById('selectedDate');
            const installerSelect = document.getElementById('installer_id');
            const installerHelperText = document.getElementById('installerHelperText');
            const preselectedInstallerId = "{{ old('installer_id', $serviceRequest->installer_id) }}";

            function fetchAvailableInstallers() {
                const date = selectedDateInput.value;
                const time = selectedTimeInput.value;

                if (date && time) {
                    installerSelect.innerHTML = '<option value="">Cargando instaladores disponibles...</option>';
                    installerSelect.disabled = true;

                    fetch(`{{ route('bodega.requests.availableInstallers') }}?date=${date}&time=${encodeURIComponent(time)}`)
                        .then(response => response.json())
                        .then(data => {
                            installerSelect.innerHTML = '<option value="">Seleccione un instalador disponible...</option>';
                            if (data.installers && data.installers.length > 0) {
                                data.installers.forEach(installer => {
                                    let option = document.createElement('option');
                                    option.value = installer.id;
                                    option.textContent = installer.name;
                                    if (preselectedInstallerId && String(installer.id) === String(preselectedInstallerId)) {
                                        option.selected = true;
                                    }
                                    installerSelect.appendChild(option);
                                });
                                installerSelect.disabled = false;
                                installerHelperText.textContent = 'Seleccione un instalador libre para este horario.';
                                installerHelperText.className = 'text-success mt-2 d-block';
                            } else {
                                installerSelect.innerHTML = '<option value="">No hay instaladores disponibles para este horario</option>';
                                installerHelperText.textContent = 'Todos los instaladores están ocupados en esta fecha y hora. Por favor elija otro horario.';
                                installerHelperText.className = 'text-danger mt-2 d-block';
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            installerSelect.innerHTML = '<option value="">Error al cargar instaladores</option>';
                            installerHelperText.textContent = 'Ocurrió un error al consultar la disponibilidad.';
                            installerHelperText.className = 'text-danger mt-2 d-block';
                        });
                }
            }

            timeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    timeButtons.forEach(b => b.classList.remove('active-slot'));
                    this.classList.add('active-slot');
                    selectedTimeInput.value = this.getAttribute('data-time');
                    fetchAvailableInstallers();
                });
            });

            let currentDate = new Date();
            if (selectedDateInput.value) {
                currentDate = new Date(selectedDateInput.value + 'T00:00:00');
            }

            function renderCalendar(date) {
                const year = date.getFullYear();
                const month = date.getMonth();

                const monthsNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                document.getElementById('currentMonthYear').innerText = `${monthsNames[month]} de ${year}`;

                const firstDayIndex = new Date(year, month, 1).getDay();
                const lastDay = new Date(year, month + 1, 0).getDate();

                const tbody = document.getElementById('calendarDays');
                tbody.innerHTML = '';

                let row = document.createElement('tr');
                let dayCount = 1;

                for (let i = 0; i < firstDayIndex; i++) {
                    let cell = document.createElement('td');
                    row.appendChild(cell);
                    dayCount++;
                }

                for (let i = 1; i <= lastDay; i++) {
                    if (dayCount > 7) {
                        tbody.appendChild(row);
                        row = document.createElement('tr');
                        dayCount = 1;
                    }

                    let cell = document.createElement('td');
                    let span = document.createElement('span');
                    span.className = 'calendar-day';
                    span.innerText = i;

                    let formattedMonth = String(month + 1).padStart(2, '0');
                    let formattedDay = String(i).padStart(2, '0');
                    let dateStr = `${year}-${formattedMonth}-${formattedDay}`;
                    span.setAttribute('data-date', dateStr);

                    if (selectedDateInput.value === dateStr) {
                        span.classList.add('selected');
                        updateDateText(dateStr);
                    }

                    span.addEventListener('click', function() {
                        document.querySelectorAll('.calendar-day').forEach(d => d.classList.remove('selected'));
                        this.classList.add('selected');
                        selectedDateInput.value = dateStr;
                        updateDateText(dateStr);
                        fetchAvailableInstallers();
                    });

                    cell.appendChild(span);
                    row.appendChild(cell);
                    dayCount++;
                }
                tbody.appendChild(row);
            }

            function updateDateText(dateStr) {
                const options = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const d = new Date(dateStr + 'T00:00:00');
                document.getElementById('selectedDateText').innerText = d.toLocaleDateString('es-ES', options);
            }

            renderCalendar(currentDate);

            document.getElementById('prevMonth').addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar(currentDate);
            });

            document.getElementById('nextMonth').addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar(currentDate);
            });

            // Cargar instaladores automáticamente si ya hay fecha y hora seleccionadas (ej. al editar o tras error de validación)
            if (selectedDateInput.value && selectedTimeInput.value) {
                fetchAvailableInstallers();
            }
        });
    </script>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>