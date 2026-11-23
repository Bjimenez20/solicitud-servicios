@php
$currentUser = auth()->user();
$isProviderUser = $currentUser->isProvider();

$providerProfile = $provider ?? ($isProviderUser ? \App\Models\Provider::where('user_id', $currentUser->id)->first() : null);
$clientProfile = $client ?? (!$isProviderUser ? \App\Models\Client::where('user_id', $currentUser->id)->first() : null);

$defaultDistributorName = $providerProfile?->business_name ?? ($isProviderUser ? $currentUser->name : '');
$defaultDistributorNit = $providerProfile?->nit ?? '';

$defaultAssociatedClient = !$isProviderUser
? ($clientProfile?->business_name ?? $currentUser->name)
: '';
$defaultClientNit = !$isProviderUser ? ($clientProfile?->nit ?? '') : '';
$defaultCity = !$isProviderUser ? ($clientProfile?->city ?? 'Bogota') : 'Bogota';
$defaultAddress = !$isProviderUser ? ($clientProfile?->address ?? '') : '';

$normalizeCity = function ($value) {
$clean = strtolower(trim(iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', (string) $value) ?: (string) $value));
return match ($clean) {
'bogota' => 'Bogota',
'medellin' => 'Medellin',
'cali' => 'Cali',
'barranquilla' => 'Barranquilla',
default => $value,
};
};

$selectedCity = old('city', $normalizeCity($defaultCity));

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

                            <!-- Sección 1 -->
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
                                            {{ old('provider_id') == $prov->id ? 'selected' : '' }}>
                                            {{ $provName }}
                                            @if($prov->nit) — NIT: {{ $prov->nit }} @endif
                                        </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="distributor_name" name="distributor_name" value="{{ old('distributor_name') }}">
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
                                    @if($isProviderUser)
                                    <label for="client_id" class="form-label fw-semibold small">Cliente asociado *</label>
                                    <select id="client_id" name="client_id" class="form-select rounded-3 @error('client_id') is-invalid @enderror" required>
                                        <option value="">Seleccione un cliente...</option>
                                        @foreach($clients ?? [] as $cli)
                                        @php
                                        $clientName = $cli->business_name ?? $cli->user?->name ?? 'Cliente #' . $cli->id;
                                        @endphp
                                        <option value="{{ $cli->id }}"
                                            data-name="{{ $clientName }}"
                                            data-nit="{{ $cli->nit ?? '' }}"
                                            data-city="{{ $cli->city ?? '' }}"
                                            data-address="{{ $cli->address ?? '' }}"
                                            data-phone="{{ $cli->phone ?? '' }}"
                                            data-email="{{ $cli->email ?? $cli->user?->email ?? '' }}"
                                            {{ old('client_id') == $cli->id ? 'selected' : '' }}>
                                            {{ $clientName }}
                                            @if($cli->nit) — NIT: {{ $cli->nit }} @endif
                                        </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="associated_client" name="associated_client" value="{{ old('associated_client') }}">
                                    @error('client_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @else
                                    <label for="associated_client" class="form-label fw-semibold small">Cliente asociado</label>
                                    <input type="text" id="associated_client" name="associated_client" value="{{ old('associated_client', $defaultAssociatedClient) }}" class="form-control rounded-3">
                                    @endif
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="client_nit" class="form-label fw-semibold small">NIT Cliente</label>
                                    <input type="text" id="client_nit" name="client_nit" value="{{ old('client_nit', $defaultClientNit) }}" class="form-control rounded-3">
                                </div>
                            </div>

                            <hr class="my-4 text-muted">

                            <!-- Sección 2 -->
                            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3"><i class="bi bi-geo-alt me-1"></i> 2. Ubicación y Negociación</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label fw-semibold small">Ciudad de Instalación *</label>
                                    <select id="city" name="city" class="form-select rounded-3" required>
                                        <option value="Bogota" {{ $selectedCity == 'Bogota' ? 'selected' : '' }}>Bogotá</option>
                                        <option value="Medellin" {{ $selectedCity == 'Medellin' ? 'selected' : '' }}>Medellín</option>
                                        <option value="Cali" {{ $selectedCity == 'Cali' ? 'selected' : '' }}>Cali</option>
                                        <option value="Barranquilla" {{ $selectedCity == 'Barranquilla' ? 'selected' : '' }}>Barranquilla</option>
                                        @if(!in_array($selectedCity, ['Bogota', 'Medellin', 'Cali', 'Barranquilla', '']) && $selectedCity)
                                        <option value="{{ $selectedCity }}" selected>{{ $selectedCity }}</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Ejecutivo que lo atiende *</label>
                                    <select name="executive" class="form-select rounded-3" required>
                                        <option value="">Seleccione ejecutivo...</option>
                                        <option value="Ejecutivo 1" {{ old('executive') == 'Ejecutivo 1' ? 'selected' : '' }}>Ejecutivo 1</option>
                                        <option value="Ejecutivo 2" {{ old('executive') == 'Ejecutivo 2' ? 'selected' : '' }}>Ejecutivo 2</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Tipo de Servicio *</label>
                                    <select name="type" class="form-select rounded-3" required>
                                        <option value="Instalación" {{ old('type', 'Instalación') == 'Instalación' ? 'selected' : '' }}>Instalación</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Segmento del dispensador *</label>
                                    <select name="segment" class="form-select rounded-3" required>
                                        <option value="Educación" {{ old('segment') == 'Educación' ? 'selected' : '' }}>Educación</option>
                                        <option value="Salud" {{ old('segment') == 'Salud' ? 'selected' : '' }}>Salud</option>
                                        <option value="Empresarial" {{ old('segment') == 'Empresarial' ? 'selected' : '' }}>Empresarial</option>
                                        <option value="Industrial" {{ old('segment') == 'Industrial' ? 'selected' : '' }}>Industrial</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold small">Fecha negociación *</label>
                                    <input type="date" name="negotiation_date" value="{{ old('negotiation_date') }}" class="form-control rounded-3" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label fw-semibold small">Dirección *</label>
                                    <input type="text" id="address" name="address" value="{{ old('address', $defaultAddress) }}" class="form-control rounded-3" required>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const providerSelect = document.getElementById('provider_id');
            const distributorNameInput = document.getElementById('distributor_name');
            const distributorNitInput = document.getElementById('distributor_nit');

            if (providerSelect) {
                function fillProviderNit() {
                    const selectedOption = providerSelect.options[providerSelect.selectedIndex];

                    if (!selectedOption || !selectedOption.value) {
                        if (distributorNameInput && distributorNameInput.type === 'hidden') {
                            distributorNameInput.value = '';
                        }
                        if (distributorNitInput) {
                            distributorNitInput.value = '';
                        }
                        return;
                    }

                    if (distributorNameInput && distributorNameInput.type === 'hidden') {
                        distributorNameInput.value = selectedOption.dataset.name || '';
                    }
                    if (distributorNitInput) {
                        distributorNitInput.value = selectedOption.dataset.nit || '';
                    }
                }

                providerSelect.addEventListener('change', fillProviderNit);

                if (providerSelect.value && distributorNitInput && !distributorNitInput.value) {
                    fillProviderNit();
                }
            }

            const clientSelect = document.getElementById('client_id');
            const associatedClientInput = document.getElementById('associated_client');
            const clientNitInput = document.getElementById('client_nit');
            const citySelect = document.getElementById('city');
            const addressInput = document.getElementById('address');

            if (clientSelect) {
                function normalizeText(text) {
                    return (text || '')
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .trim()
                        .toLowerCase();
                }

                function fillClientFields() {
                    const selectedOption = clientSelect.options[clientSelect.selectedIndex];

                    if (!selectedOption || !selectedOption.value) {
                        if (associatedClientInput) associatedClientInput.value = '';
                        if (clientNitInput) clientNitInput.value = '';
                        if (addressInput) addressInput.value = '';
                        return;
                    }

                    if (associatedClientInput) associatedClientInput.value = selectedOption.dataset.name || '';
                    if (clientNitInput) clientNitInput.value = selectedOption.dataset.nit || '';
                    if (addressInput) addressInput.value = selectedOption.dataset.address || '';

                    const clientCityNormalized = normalizeText(selectedOption.dataset.city);
                    if (clientCityNormalized && citySelect) {
                        const matchedOption = Array.from(citySelect.options).find(opt =>
                            normalizeText(opt.value) === clientCityNormalized ||
                            normalizeText(opt.text) === clientCityNormalized
                        );

                        if (matchedOption) {
                            citySelect.value = matchedOption.value;
                        } else {
                            const dynamicOption = new Option(selectedOption.dataset.city, selectedOption.dataset.city, true, true);
                            citySelect.add(dynamicOption);
                        }
                    }
                }

                clientSelect.addEventListener('change', fillClientFields);

                if (clientSelect.value && clientNitInput && !clientNitInput.value && addressInput && !addressInput.value) {
                    fillClientFields();
                }
            }
        });
    </script>
</body>

</html>