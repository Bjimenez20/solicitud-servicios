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

    @php
    $tipoServicio = request()->query('tipo', 'Auditoría');
    @endphp

    <div class="container py-5">

        <!-- Barra superior de navegación interna y pasos -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <a href="{{ route('client.services.select') }}" class="text-decoration-none text-muted fw-semibold small d-flex align-items-center">
                <i class="bi bi-arrow-left me-2"></i> Volver a selección de citas
            </a>
            <div class="text-muted small">
                <span class="text-dark fw-bold">Fecha y hora</span>
                <i class="bi bi-chevron-right mx-2 text-muted" style="font-size: 0.75rem;"></i> Detalles
                <i class="bi bi-chevron-right mx-2 text-muted" style="font-size: 0.75rem;"></i> Reservado
            </div>
        </div>

        <div class="row g-4">

            <!-- COLUMNA IZQUIERDA: Calendario Real y Horas -->
            <div class="col-lg-8">
                <div class="mb-4">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill fw-bold mb-2">Paso 1 de 2</span>
                    <h2 class="fw-bold text-dark">Elige la fecha y la hora</h2>
                    <p class="text-muted small">Selecciona el día en el calendario y elige un horario disponible para tu {{ strtolower($tipoServicio) }}.</p>
                </div>

                <form action="{{ route('client.services.selectInstallation') }}" method="POST" id="appointmentForm">
                    @csrf

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
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="07:00" onclick="selectTime(this, '07:00')">07:00 AM</button></div>
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="08:00" onclick="selectTime(this, '08:00')">08:00 AM</button></div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="09:00" onclick="selectTime(this, '09:00')">09:00 AM</button></div>
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="10:00" onclick="selectTime(this, '10:00')">10:00 AM</button></div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="11:00" onclick="selectTime(this, '11:00')">11:00 AM</button></div>
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="14:00" onclick="selectTime(this, '14:00')">02:00 PM</button></div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="15:00" onclick="selectTime(this, '15:00')">03:00 PM</button></div>
                                            <div class="col-6"><button type="button" class="btn time-slot py-2 w-100 fs-7" data-time="16:00" onclick="selectTime(this, '16:00')">04:00 PM</button></div>
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
                        <span class="text-uppercase text-muted fs-8 fw-bold d-block mb-1">Modalidad</span>
                        <div class="d-flex align-items-center text-secondary small">
                            <i class="bi bi-geo-alt text-danger me-2"></i> Presencial en punto principal
                        </div>
                    </div>

                    <div class="mb-4">
                        <span class="text-uppercase text-muted fs-8 fw-bold d-block mb-1">Duración estimada</span>
                        <div class="d-flex align-items-center text-secondary small">
                            <i class="bi bi-stopwatch text-muted me-2"></i> 1 hora
                        </div>
                    </div>

                    <div class="alert alert-light border rounded-3 p-3 small text-muted mb-0">
                        <i class="bi bi-shield-check text-success me-1"></i> Al confirmar, el espacio quedará registrado en el sistema para su validación técnica.
                    </div>
                </div>
            </div>

        </div>
    </div>
    <x-footer />
    <!-- FullCalendar JS CDN -->
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            let todayStr = new Date().toISOString().split('T')[0];

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                headerToolbar: {
                    left: 'prev,next',
                    center: 'title',
                    right: 'today'
                },
                selectable: true,
                dateClick: function(info) {
                    let selectedDate = info.dateStr;

                    if (selectedDate < todayStr) {
                        alert('No puedes agendar citas en fechas pasadas.');
                        return;
                    }

                    document.getElementById('selectedDateInput').value = selectedDate;

                    let options = {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        weekday: 'long'
                    };
                    let formattedDate = new Date(selectedDate + 'T00:00:00').toLocaleDateString('es-ES', options);
                    document.getElementById('displaySelectedDate').innerText = formattedDate;

                    document.querySelectorAll('.fc-daygrid-day').forEach(day => {
                        day.classList.remove('selected-date');
                    });
                    info.dayEl.classList.add('selected-date');

                    // Validar horas disponibles al cambiar de fecha
                    validateAvailableTimes(selectedDate);
                }
            });

            calendar.render();

            // Inicializar con el día de hoy
            document.getElementById('selectedDateInput').value = todayStr;
            let options = {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                weekday: 'long'
            };
            let formattedToday = new Date(todayStr + 'T00:00:00').toLocaleDateString('es-ES', options);
            document.getElementById('displaySelectedDate').innerText = formattedToday;

            validateAvailableTimes(todayStr);
        });

        // Función para bloquear o desbloquear horas según la hora actual si es el día de hoy
        function validateAvailableTimes(selectedDate) {
            let todayStr = new Date().toISOString().split('T')[0];
            let now = new Date();
            let currentHour = now.getHours();
            let currentMinutes = now.getMinutes();

            let timeButtons = document.querySelectorAll('.time-slot');
            let firstAvailableBtn = null;

            timeButtons.forEach(btn => {
                let timeStr = btn.getAttribute('data-time'); // Ej: "14:00"
                let [btnHour, btnMinute] = timeStr.split(':').map(Number);

                let isPast = false;

                // Si la fecha seleccionada es hoy, comparamos la hora del botón con la hora actual
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
                        firstAvailableBtn = btn; // Guardamos el primer horario disponible para seleccionarlo por defecto
                    }
                }
            });

            // Seleccionar automáticamente el primer horario disponible si el actual quedó bloqueado
            let activeBtn = document.querySelector('.time-slot.active');
            if (!activeBtn || activeBtn.disabled) {
                if (firstAvailableBtn) {
                    selectTime(firstAvailableBtn, firstAvailableBtn.getAttribute('data-time'));
                } else {
                    // Si ya pasaron todas las horas del día de hoy
                    document.getElementById('selectedTimeInput').value = "";
                }
            }
        }

        function selectTime(button, timeStr) {
            if (button.disabled) return;
            document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('active'));
            button.classList.add('active');
            document.getElementById('selectedTimeInput').value = timeStr;
        }

        // Validación extra al enviar el formulario por seguridad
        document.getElementById('appointmentForm').addEventListener('submit', function(e) {
            let timeVal = document.getElementById('selectedTimeInput').value;
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