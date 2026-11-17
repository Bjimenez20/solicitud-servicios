<!DOCTYPE html>
<html lang="es">

<x-head />

<body class="bg-light">

    <!-- Tu Navbar Global Uniforme -->
    <x-navbar />

    <!-- Contenido Principal -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- Encabezado de la Landing -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-dark mb-1">Nueva Solicitud de Dispensadores</h2>
                        <p class="text-muted small mb-0">Complete la información comercial para programar su servicio.</p>
                    </div>
                    <!-- <a href="{{ route('client.requests.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Volver al listado
                    </a> -->
                </div>

                <!-- Alerta de Errores -->
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

                <!-- Tarjeta del Formulario -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-gradient bg-primary text-white p-4">
                        <h5 class="mb-0 fw-bold"><i class="bi bi-file-earmark-text me-2"></i> Formulario de Registro</h5>
                        <p class="mb-0 text-white-50 small">Los campos marcados con asterisco (*) son obligatorios.</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form action="{{ route('client.requests.stepOne') }}" method="POST">
                            @csrf

                            <!-- Sección 1 -->
                            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3"><i class="bi bi-info-circle me-1"></i> 1. Información Comercial</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Razón social Distribuidor *</label>
                                    <input type="text" name="distributor_name" value="{{ old('distributor_name', auth()->user()->name) }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">NIT Distribuidor *</label>
                                    <input type="text" name="distributor_nit" value="{{ old('distributor_nit') }}" placeholder="Ej: 900123456-7" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Cliente asociado</label>
                                    <input type="text" name="associated_client" value="{{ old('associated_client') }}" class="form-control rounded-3">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">NIT Cliente</label>
                                    <input type="text" name="client_nit" value="{{ old('client_nit') }}" class="form-control rounded-3">
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <!-- Sección 2 -->
                            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3"><i class="bi bi-geo-alt me-1"></i> 2. Ubicación y Negociación</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Ciudad de Instalación *</label>
                                    <select name="city" class="form-select rounded-3" required>
                                        <option value="Bogota">Bogotá</option>
                                        <option value="Medellin">Medellín</option>
                                        <option value="Cali">Cali</option>
                                        <option value="Barranquilla">Barranquilla</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Ejecutivo que lo atiende *</label>
                                    <select name="executive" class="form-select rounded-3" required>
                                        <option value="">Seleccione ejecutivo...</option>
                                        <option value="Ejecutivo 1">Ejecutivo 1</option>
                                        <option value="Ejecutivo 2">Ejecutivo 2</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Tipo de Servicio *</label>
                                    <select name="type" class="form-select rounded-3" required>
                                        <option value="Instalación">Instalación</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Segmento del dispensador *</label>
                                    <select name="segment" class="form-select rounded-3" required>
                                        <option value="Educación">Educación</option>
                                        <option value="Salud">Salud</option>
                                        <option value="Empresarial">Empresarial</option>
                                        <option value="Industrial">Industrial</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Fecha negociación *</label>
                                    <input type="date" name="negotiation_date" value="{{ old('negotiation_date') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Dirección *</label>
                                    <input type="text" name="address" value="{{ old('address') }}" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <!-- Sección 3 -->
                            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3"><i class="bi bi-person-badge me-1"></i> 3. Contacto y Mensaje</h6>

                            <div class="mb-3">
                                <label class="form-label fw-semibold small">Responsable de dispensadores *</label>
                                <input type="text" name="responsible_name" value="{{ old('responsible_name') }}" class="form-control rounded-3" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Teléfono de responsable *</label>
                                    <input type="text" name="responsible_phone" value="{{ old('responsible_phone', '+57 ') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Correo de responsable *</label>
                                    <input type="email" name="responsible_email" value="{{ old('responsible_email') }}" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold small">Asunto del mensaje *</label>
                                <input type="text" name="message_subject" value="{{ old('message_subject') }}" placeholder="Breve descripción..." class="form-control rounded-3" required>
                            </div>

                            <!-- Botón de Envío -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg rounded-3 fw-bold shadow-sm">
                                    <i class="bi bi-send-check me-2"></i> Continuar a Selección de Productos →
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <x-footer />
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>