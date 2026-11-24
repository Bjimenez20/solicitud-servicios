@php
$currentUser = auth()->user();
$isProviderUser = $currentUser->isProvider();

$providerProfile = $provider ?? \App\Models\Provider::where('user_id', $currentUser->id)->first();
$firstProvider = $providers->first() ?? null;

$userNit = $currentUser->nit
?? $currentUser->document
?? $currentUser->tax_id
?? null;

$userBusinessName = $currentUser->business_name
?? $currentUser->company_name
?? $currentUser->name
?? '';

if ($isProviderUser) {
$defaultDistributorName = $providerProfile?->business_name ?? $userBusinessName;
$defaultDistributorNit = $providerProfile?->nit ?? ($userNit ?? '');
} else {
$defaultProviderObj = $providerProfile ?? $firstProvider;
$defaultDistributorName = $providerProfile?->business_name
?? ($defaultProviderObj?->business_name ?? $userBusinessName);
$defaultDistributorNit = $providerProfile?->nit
?? ($userNit ?? ($defaultProviderObj?->nit ?? ''));
}

$selectedProviderId = old('provider_id', $providerProfile?->id ?? $firstProvider?->id ?? '');

$actionRoute = $isProviderUser
? route('provider.requests.stepOne')
: route('client.requests.stepOne');
$backRoute = $isProviderUser
? route('provider.requests.index')
: route('client.requests.index');
@endphp

<!DOCTYPE html>
<html lang="es">

<x-head />

<body class="bg-light">

    <!-- Navbar Global Uniforme -->
    <x-navbar />

    <!-- Contenido Principal -->
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <!-- Encabezado -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="fw-bold text-dark mb-1">Nueva Solicitud de Dispensadores</h2>
                        <p class="text-muted small mb-0">Complete la información comercial y del cliente para programar el servicio.</p>
                    </div>
                    <a href="{{ $backRoute }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Volver al listado
                    </a>
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
                        <form action="{{ $actionRoute }}" method="POST">
                            @csrf

                            <!-- Sección 1: Información Comercial -->
                            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3"><i class="bi bi-info-circle me-1"></i> 1. Información Comercial</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    @if($isProviderUser)
                                    <label for="distributor_name" class="form-label fw-semibold small">Razón social Distribuidor *</label>
                                    <input type="text" id="distributor_name" name="distributor_name" value="{{ old('distributor_name', $defaultDistributorName) }}" class="form-control rounded-3" required>
                                    @else
                                    <label for="provider_id" class="form-label fw-semibold small">Razón social Distribuidor *</label>
                                    <select id="provider_id" name="provider_id" class="form-select rounded-3 @error('provider_id') is-invalid @enderror" required>
                                        <option value="">Seleccione un proveedor...</option>
                                        @foreach($providers ?? [] as $prov)
                                        @php
                                        $provName = $prov->business_name ?? $prov->user?->name ?? 'Proveedor #' . $prov->id;
                                        @endphp
                                        <option value="{{ $prov->id }}"
                                            data-name="{{ $provName }}"
                                            data-nit="{{ $prov->nit ?? '' }}"
                                            {{ (string) $selectedProviderId === (string) $prov->id ? 'selected' : '' }}>
                                            {{ $provName }}
                                            @if($prov->nit) — NIT: {{ $prov->nit }} @endif
                                        </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="distributor_name" name="distributor_name" value="{{ old('distributor_name', $defaultDistributorName) }}">
                                    @error('provider_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @endif
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="distributor_nit" class="form-label fw-semibold small">NIT Distribuidor *</label>
                                    <input type="text" id="distributor_nit" name="distributor_nit" value="{{ old('distributor_nit', $defaultDistributorNit) }}" placeholder="Ej: 900123456-7" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="associated_client" class="form-label fw-semibold small">Cliente asociado *</label>
                                    <input type="text" id="associated_client" name="associated_client" value="{{ old('associated_client') }}" placeholder="Razón social o nombre del cliente" class="form-control rounded-3" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="client_nit" class="form-label fw-semibold small">NIT Cliente *</label>
                                    <input type="text" id="client_nit" name="client_nit" value="{{ old('client_nit') }}" placeholder="Ej: 800123456-1" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <!-- Sección 2: Ubicación y Negociación -->
                            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3"><i class="bi bi-geo-alt me-1"></i> 2. Ubicación y Negociación</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label fw-semibold small">Ciudad de Instalación *</label>
                                    <select id="city" name="city" class="form-select rounded-3" required>
                                        <option value="">Seleccione una ciudad...</option>
                                        <option value="Bogota" {{ old('city') == 'Bogota' ? 'selected' : '' }}>Bogotá</option>
                                        <option value="Medellin" {{ old('city') == 'Medellin' ? 'selected' : '' }}>Medellín</option>
                                        <option value="Cali" {{ old('city') == 'Cali' ? 'selected' : '' }}>Cali</option>
                                        <option value="Barranquilla" {{ old('city') == 'Barranquilla' ? 'selected' : '' }}>Barranquilla</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="executive" class="form-label fw-semibold small">Ejecutivo que lo atiende *</label>
                                    <select id="executive" name="executive" class="form-select rounded-3" required>
                                        <option value="">Seleccione ejecutivo...</option>
                                        <option value="Ejecutivo 1" {{ old('executive') == 'Ejecutivo 1' ? 'selected' : '' }}>Ejecutivo 1</option>
                                        <option value="Ejecutivo 2" {{ old('executive') == 'Ejecutivo 2' ? 'selected' : '' }}>Ejecutivo 2</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="type" class="form-label fw-semibold small">Tipo de Servicio *</label>
                                    <select id="type" name="type" class="form-select rounded-3" required>
                                        <option value="Instalación" {{ old('type', 'Instalación') == 'Instalación' ? 'selected' : '' }}>Instalación</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="segment" class="form-label fw-semibold small">Segmento del dispensador *</label>
                                    <select id="segment" name="segment" class="form-select rounded-3" required>
                                        <option value="">Seleccione segmento...</option>
                                        <option value="Educación" {{ old('segment') == 'Educación' ? 'selected' : '' }}>Educación</option>
                                        <option value="Salud" {{ old('segment') == 'Salud' ? 'selected' : '' }}>Salud</option>
                                        <option value="Empresarial" {{ old('segment') == 'Empresarial' ? 'selected' : '' }}>Empresarial</option>
                                        <option value="Industrial" {{ old('segment') == 'Industrial' ? 'selected' : '' }}>Industrial</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="negotiation_date" class="form-label fw-semibold small">Fecha negociación *</label>
                                    <input type="date" id="negotiation_date" name="negotiation_date" value="{{ old('negotiation_date') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label fw-semibold small">Dirección *</label>
                                    <input type="text" id="address" name="address" value="{{ old('address') }}" placeholder="Dirección del punto de instalación" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <!-- Sección 3: Contacto y Mensaje -->
                            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3"><i class="bi bi-person-badge me-1"></i> 3. Contacto y Mensaje</h6>

                            <div class="mb-3">
                                <label for="responsible_name" class="form-label fw-semibold small">Responsable de dispensadores *</label>
                                <input type="text" id="responsible_name" name="responsible_name" value="{{ old('responsible_name') }}" class="form-control rounded-3" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="responsible_phone" class="form-label fw-semibold small">Teléfono de responsable *</label>
                                    <input type="text" id="responsible_phone" name="responsible_phone" value="{{ old('responsible_phone', '+57 ') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="responsible_email" class="form-label fw-semibold small">Correo de responsable *</label>
                                    <input type="email" id="responsible_email" name="responsible_email" value="{{ old('responsible_email') }}" class="form-control rounded-3" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label for="message_subject" class="form-label fw-semibold small">Asunto del mensaje *</label>
                                <input type="text" id="message_subject" name="message_subject" value="{{ old('message_subject') }}" placeholder="Breve descripción..." class="form-control rounded-3" required>
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

    @if(!$isProviderUser)
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const providerSelect = document.getElementById('provider_id');
            const distributorNameInput = document.getElementById('distributor_name');
            const distributorNitInput = document.getElementById('distributor_nit');

            function fillProviderData() {
                const selectedOption = providerSelect.options[providerSelect.selectedIndex];

                if (!selectedOption || !selectedOption.value) {
                    if (distributorNameInput) distributorNameInput.value = '';
                    if (distributorNitInput) distributorNitInput.value = '';
                    return;
                }

                if (distributorNameInput) distributorNameInput.value = selectedOption.dataset.name || '';
                if (distributorNitInput) distributorNitInput.value = selectedOption.dataset.nit || '';
            }

            if (providerSelect) {
                providerSelect.addEventListener('change', fillProviderData);

                if (providerSelect.value) {
                    fillProviderData();
                }
            }
        });
    </script>
    @endif
</body>

</html>