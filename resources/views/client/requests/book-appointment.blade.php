@php
$isProviderUser = auth()->user()->isProvider();
$selectInstallationRoute = $isProviderUser
? route('provider.services.selectInstallation')
: route('client.services.selectInstallation');
$backToSelectRoute = route('client.services.select');
$tipoServicio = request()->query('tipo', 'Auditoría');
@endphp
<!DOCTYPE html>
<html lang="es">

<x-head />

<style>
    :root {
        --primary-corporate: #1b3a6b;
        --primary-hover: #132b4e;
        --bg-soft: #f8fafc;
    }

    body {
        background-color: var(--bg-soft);
        color: #334155;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    #calendar {
        background: white;
        padding: 24px;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
    }

    .fc {
        font-family: inherit;
    }

    .fc .fc-toolbar-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        text-transform: capitalize;
    }

    .fc .fc-button-primary {
        background-color: white;
        border-color: #cbd5e1;
        color: #475569;
        border-radius: 0.5rem;
        padding: 6px 12px;
        font-weight: 600;
        box-shadow: none;
        transition: all 0.2s;
    }

    .fc .fc-button-primary:hover {
        background-color: #f1f5f9;
        border-color: #94a3b8;
        color: #1e293b;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active,
    .fc .fc-button-primary:not(:disabled):active {
        background-color: var(--primary-corporate);
        border-color: var(--primary-corporate);
        color: white;
    }

    .fc-col-header-cell-cushion {
        color: #64748b;
        font-weight: 600;
        text-decoration: none;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding-bottom: 8px !important;
    }

    .fc-daygrid-day-number {
        color: #334155;
        font-weight: 500;
        text-decoration: none;
        padding: 8px !important;
    }

    .fc-daygrid-day.fc-day-today {
        background-color: #eff6ff !important;
    }

    .fc-daygrid-day.selected-date {
        background-color: var(--primary-corporate) !important;
        color: white !important;
        border-radius: 8px;
    }

    .fc-daygrid-day.selected-date .fc-daygrid-day-number {
        color: white !important;
    }

    .fc-daygrid-day:hover {
        background-color: #f8fafc;
        cursor: pointer;
    }

    /* Botones de Horas */
    .time-slot {
        border: 1px solid #cbd5e1;
        background-color: white;
        color: #334155;
        transition: all 0.2s ease;
        font-weight: 500;
        border-radius: 0.5rem;
    }

    .time-slot:hover:not(:disabled) {
        border-color: var(--primary-corporate);
        color: var(--primary-corporate);
        background-color: #f8fafc;
    }

    .time-slot.active {
        background-color: var(--primary-corporate);
        color: white;
        border-color: var(--primary-corporate);
        box-shadow: 0 4px 6px -1px rgba(27, 58, 107, 0.2);
    }

    /* Estilo para horas pasadas bloqueadas */
    .time-slot:disabled {
        background-color: #f1f5f9;
        border-color: #e2e8f0;
        color: #94a3b8;
        cursor: not-allowed;
        text-decoration: line-through;
    }

    .btn-corporate {
        background-color: var(--primary-corporate);
        color: white;
        border-radius: 0.75rem;
        transition: background-color 0.2s, transform 0.1s;
    }

    .btn-corporate:hover {
        background-color: var(--primary-hover);
        color: white;
        transform: translateY(-1px);
    }

    .summary-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
</style>

<body>

    <!-- Navbar Global -->
    <x-navbar />

    <div class="container py-5">

        <!-- Barra superior de navegación interna y pasos -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <a href="{{ $backToSelectRoute }}" class="text-decoration-none text-muted fw-semibold small d-flex align-items-center">
                <i class="bi bi-arrow-left me-2"></i> Volver a selección de citas
            </a>
            <div class="text-muted small">
                <span class="text-dark fw-bold">Fecha y hora</span>
                <i class="bi bi-chevron-right mx-2 text-muted" style="font-size: 0.75rem;"></i> Seleccionar Instalación
                <i class="bi bi-chevron-right mx-2 text-muted" style="font-size: 0.75rem;"></i> Reservado
            </div>
        </div>

        @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0 rounded-4 mb-4">
            <div class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Por favor corrija los siguientes errores:</div>
            <ul class="mb-0 small">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="row g-4">
            <!-- COLUMNA IZQUIERDA: Calendario Real y Horas -->
            <div class="col-lg-8">
                <div class="mb-4">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-bold mb-2">Paso 1 de 2</span>
                    <h2 class="fw-bold text-dark">Elige la fecha y la hora</h2>
                    <p class="text-muted small">
                        Selecciona el cliente, el día y el horario para agendar la {{ strtolower($tipoServicio) }} sobre una instalación existente.
                    </p>
                </div>

                <form action="{{ $selectInstallationRoute }}" method="POST" id="appointmentForm">
                    @csrf

                    <div class="mb-4 p-3 p-md-4 rounded-4 bg-white border shadow-sm">
                        <label for="clientSearchInput" class="form-label small fw-bold text-dark mb-1">
                            <i class="bi bi-building-check text-primary me-1"></i> Cliente a relacionar con la instalación *
                        </label>
                        <p class="text-muted small mb-2">Al continuar, se filtrarán las instalaciones registradas de este cliente.</p>

                        <!-- Buscador en tiempo real -->
                        <input type="text"
                            id="clientSearchInput"
                            class="form-control form-control-sm rounded-3 mb-2"
                            placeholder="Escribe nombre del cliente, NIT, ciudad o dirección..."
                            autocomplete="off">

                        <!-- Selector normalizado de clientes -->
                        <select name="client_id" id="clientSelect" class="form-select rounded-3 @error('client_id') is-invalid @enderror" required>
                            <option value="">-- Selecciona un cliente --</option>
                            @foreach($clients ?? [] as $client)
                            @php
                            $clientName = $client->business_name ?? ('Cliente #' . $client->id);
                            $nit = $client->nit ?? '';
                            $city = $client->city ?? '';
                            $address = $client->address ?? '';
                            @endphp
                            <option value="{{ $client->id }}"
                                data-search="{{ strtolower($clientName . ' ' . $nit . ' ' . $city . ' ' . $address) }}"
                                data-summary-name="{{ $clientName }}"
                                data-summary-city="{{ $city }}"
                                data-summary-address="{{ $address }}"
                                {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $clientName }}@if($nit) — NIT: {{ $nit }}@endif @if($city) ({{ $city }})@endif
                            </option>
                            @endforeach
                        </select>
                        @error('client_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-muted d-block mt-1" style="font-size: 0.72rem;" id="clientSearchCount">
                            {{ count($clients ?? []) }} clientes disponibles
                        </small>
                    </div>

                    <input type="hidden" name="scheduled_date" id="selectedDateInput" required>
                    <input type="hidden" name="scheduled_time" id="selectedTimeInput" required>
                    <input type="hidden" name="type" value="{{ $tipoServicio }}">

                    <div class="row g-4">
                        <!-- Calendario Interactivo -->
                        <div class="col-md-7">
                            <div id="calendar"></div>

                            <div class="alert bg-white border mt-3 py-2 px-3 small text-muted shadow-sm rounded-3 d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-calendar-event text-primary me-2"></i>Fecha elegida:</span>
                                <strong id="displaySelectedDate" class="text-dark">Ninguna</strong>
                            </div>
                        </div>

                        <!-- Selector de Horas y Confirmación -->
                        <div class="col-md-5">
                            <div class="bg-white p-4 border rounded-4 shadow-sm h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-clock me-2 text-primary"></i>Horarios Disponibles</h6>

                                    <!-- Botones de hora con atributo data-time para validación automática -->
                                    <div class="d-flex flex-column gap-2 mb-4" id="timeSlotsContainer">
                                        <div class="row g-2">
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="07:00 AM" onclick="selectTime(this, '07:00 AM')">07:00 AM</button></div>
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="08:00 AM" onclick="selectTime(this, '08:00 AM')">08:00 AM</button></div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="09:00 AM" onclick="selectTime(this, '09:00 AM')">09:00 AM</button></div>
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="10:00 AM" onclick="selectTime(this, '10:00 AM')">10:00 AM</button></div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="11:00 AM" onclick="selectTime(this, '11:00 AM')">11:00 AM</button></div>
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="02:00 PM" onclick="selectTime(this, '02:00 PM')">02:00 PM</button></div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="03:00 PM" onclick="selectTime(this, '03:00 PM')">03:00 PM</button></div>
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="04:00 PM" onclick="selectTime(this, '04:00 PM')">04:00 PM</button></div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small text-muted fw-semibold">Servicio solicitado:</label>
                                        <select class="form-select rounded-3 shadow-none bg-light text-dark fw-bold border-0" disabled>
                                            <option selected>{{ $tipoServicio }}</option>
                                        </select>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-corporate w-100 py-3 fw-bold shadow-sm" id="submitBtn">
                                    <i class="bi bi-arrow-right-circle me-2"></i> Continuar
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- COLUMNA DERECHA: Resumen de la Reserva Estilizado -->
            <div class="col-lg-4">
                <div class="summary-card p-4 sticky-top" style="top: 2rem;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary-subtle text-primary p-2 rounded-3 me-3">
                            <i class="bi bi-journal-check fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold text-dark mb-0 fs-6">Resumen de Cita</h5>
                            <span class="text-muted small">Overall TrackField</span>
                        </div>
                    </div>

                    <hr class="text-muted opacity-25 my-3">

                    <div class="mb-3">
                        <span class="text-uppercase text-muted fs-8 fw-bold d-block mb-1">Tipo de Servicio</span>
                        <span class="fw-bold text-dark fs-5">{{ $tipoServicio }}</span>
                    </div>

                    <div class="mb-3">
                        <span class="text-uppercase text-muted fs-8 fw-bold d-block mb-1">Cliente Seleccionado</span>
                        <div id="summaryClientName" class="fw-semibold text-dark small">Sin seleccionar</div>
                        <div id="summaryClientLocation" class="text-muted small"></div>
                    </div>

                    <div class="mb-3">
                        <span class="text-uppercase text-muted fs-8 fw-bold d-block mb-1">Horario Elegido</span>
                        <div id="summarySchedule" class="text-secondary small">
                            <i class="bi bi-calendar-check text-primary me-1"></i> <span id="summaryTimeText">Por definir</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="text-uppercase text-muted fs-8 fw-bold d-block mb-1">Duración estimada</span>
                        <div class="d-flex align-items-center text-secondary small">
                            <i class="bi bi-stopwatch text-muted me-2"></i> 1 hora
                        </div>
                    </div>

                    <div class="alert alert-light border rounded-3 p-3 small text-muted mb-0">
                        <i class="bi bi-shield-check text-success me-1"></i> En el siguiente paso elegirás la instalación del cliente para vincular los equipos.
                    </div>
                </div>
            </div>

        </div>
    </div>
    <x-footer />
    <!-- FullCalendar JS CDN -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

    <script>
        function parseTimeTo24h(timeStr) {
            const match = (timeStr || '').trim().match(/^(\d{1,2}):(\d{2})(?:\s*(AM|PM))?$/i);
            if (!match) return {
                hour: 0,
                minute: 0
            };

            let hour = parseInt(match[1], 10);
            const minute = parseInt(match[2], 10);
            const period = (match[3] || '').toUpperCase();

            if (period === 'PM' && hour < 12) hour += 12;
            if (period === 'AM' && hour === 12) hour = 0;

            return {
                hour,
                minute
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const now = new Date();
            const todayStr = now.getFullYear() + '-' +
                String(now.getMonth() + 1).padStart(2, '0') + '-' +
                String(now.getDate()).padStart(2, '0');

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                },
                selectable: true,
                dateClick: function(info) {
                    const selectedDate = info.dateStr;

                    if (selectedDate < todayStr) {
                        alert('No puedes agendar citas en fechas pasadas.');
                        return;
                    }

                    document.getElementById('selectedDateInput').value = selectedDate;

                    const options = {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        weekday: 'long'
                    };
                    const formattedDate = new Date(selectedDate + 'T00:00:00').toLocaleDateString('es-ES', options);
                    document.getElementById('displaySelectedDate').innerText = formattedDate;

                    document.querySelectorAll('.fc-daygrid-day').forEach(day => {
                        day.classList.remove('selected-date');
                    });
                    info.dayEl.classList.add('selected-date');

                    validateAvailableTimes(selectedDate);
                    updateSummaryTime();
                }
            });

            calendar.render();

            document.getElementById('selectedDateInput').value = todayStr;
            const options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'long'
            };
            const formattedToday = new Date(todayStr + 'T00:00:00').toLocaleDateString('es-ES', options);
            document.getElementById('displaySelectedDate').innerText = formattedToday;

            validateAvailableTimes(todayStr);
            updateSummaryTime();

            const searchInput = document.getElementById('clientSearchInput');
            const selectEl = document.getElementById('clientSelect');
            const countText = document.getElementById('clientSearchCount');
            const summaryNameEl = document.getElementById('summaryClientName');
            const summaryLocEl = document.getElementById('summaryClientLocation');

            function updateClientSummary() {
                if (!selectEl || !summaryNameEl) return;
                const opt = selectEl.options[selectEl.selectedIndex];
                if (!opt || !opt.value) {
                    summaryNameEl.textContent = 'Sin seleccionar';
                    if (summaryLocEl) summaryLocEl.textContent = '';
                    return;
                }
                summaryNameEl.textContent = opt.dataset.summaryName || opt.text;
                const locParts = [opt.dataset.summaryAddress, opt.dataset.summaryCity].filter(Boolean);
                if (summaryLocEl) summaryLocEl.textContent = locParts.join(' — ');
            }

            if (searchInput && selectEl) {
                const allOptions = Array.from(selectEl.options).slice(1);

                searchInput.addEventListener('input', function() {
                    const term = this.value.trim().toLowerCase();
                    let visibleCount = 0;

                    allOptions.forEach(opt => {
                        const haystack = opt.getAttribute('data-search') || '';
                        const matches = !term || haystack.includes(term);
                        opt.hidden = !matches;
                        if (matches) visibleCount++;
                    });

                    if (countText) {
                        countText.textContent = term ?
                            `${visibleCount} coincidencia(s) encontrada(s)` :
                            `${allOptions.length} clientes disponibles`;
                    }
                });

                selectEl.addEventListener('change', updateClientSummary);
                updateClientSummary();
            }
        });

        function validateAvailableTimes(selectedDate) {
            const now = new Date();
            const todayStr = now.getFullYear() + '-' +
                String(now.getMonth() + 1).padStart(2, '0') + '-' +
                String(now.getDate()).padStart(2, '0');
            const currentHour = now.getHours();
            const currentMinutes = now.getMinutes();

            const timeButtons = document.querySelectorAll('.time-slot');
            let firstAvailableBtn = null;

            timeButtons.forEach(btn => {
                const timeStr = btn.getAttribute('data-time');
                const {
                    hour: btnHour,
                    minute: btnMinute
                } = parseTimeTo24h(timeStr);

                let isPast = false;
                if (selectedDate === todayStr) {
                    if (btnHour < currentHour || (btnHour === currentHour && btnMinute <= currentMinutes)) {
                        isPast = true;
                    }
                }

                if (isPast) {
                    btn.disabled = true;
                    btn.classList.remove('active');
                } else {
                    btn.disabled = false;
                    if (!firstAvailableBtn) {
                        firstAvailableBtn = btn;
                    }
                }
            });

            const activeBtn = document.querySelector('.time-slot.active');
            if (!activeBtn || activeBtn.disabled) {
                if (firstAvailableBtn) {
                    selectTime(firstAvailableBtn, firstAvailableBtn.getAttribute('data-time'));
                } else {
                    document.getElementById('selectedTimeInput').value = "";
                    updateSummaryTime();
                }
            }
        }

        function updateSummaryTime() {
            const timeEl = document.getElementById('summaryTimeText');
            const dateVal = document.getElementById('selectedDateInput')?.value || '';
            const timeVal = document.getElementById('selectedTimeInput')?.value || '';
            if (!timeEl) return;

            if (dateVal && timeVal) {
                timeEl.textContent = `${dateVal} a las ${timeVal}`;
            } else if (dateVal) {
                timeEl.textContent = `${dateVal} (Sin hora disponible)`;
            } else {
                timeEl.textContent = 'Por definir';
            }
        }

        function selectTime(button, timeStr) {
            if (button.disabled) return;
            document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('active'));
            button.classList.add('active');
            document.getElementById('selectedTimeInput').value = timeStr;
            updateSummaryTime();
        }

        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            const clientSelect = document.getElementById('clientSelect');
            if (clientSelect && !clientSelect.value) {
                e.preventDefault();
                alert('Por favor, selecciona un cliente para relacionar con la instalación.');
                clientSelect.focus();
                return;
            }

            const timeVal = document.getElementById('selectedTimeInput').value;
            if (!timeVal) {
                e.preventDefault();
                alert('Por favor, selecciona un horario válido disponible.');
            }
        });
    </script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>