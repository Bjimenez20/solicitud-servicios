@php
$currentUser = auth()->user();
$isProviderUser = method_exists($currentUser, 'isProvider') ? $currentUser->isProvider() : false;

$providerProfile = $provider
?? \App\Models\Provider::where('user_id', $currentUser->id)->first()
?? ($providers->first() ?? \App\Models\Provider::first());

$resolvedProviderId = old('provider_id', $providerProfile?->id ?? '');

$userNit = $currentUser->nit
?? $currentUser->document
?? $currentUser->tax_id
?? null;

$userBusinessName = $currentUser->business_name
?? $currentUser->company_name
?? $currentUser->name
?? '';

$defaultDistributorName = old(
'distributor_name',
$providerProfile?->business_name ?? $userBusinessName
);

$defaultDistributorNit = old(
'distributor_nit',
$providerProfile?->nit ?? ($userNit ?? '')
);

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
                            <input type="hidden" name="provider_id" value="{{ $resolvedProviderId }}">

                            <!-- Sección 1: Información Comercial -->
                            <h6 class="text-uppercase text-muted fw-bold fs-7 mb-3"><i class="bi bi-info-circle me-1"></i> 1. Información Comercial</h6>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="distributor_name" class="form-label fw-semibold small">Razón social Distribuidor *</label>
                                    <input type="text"
                                        id="distributor_name"
                                        name="distributor_name"
                                        value="{{ $defaultDistributorName }}"
                                        class="form-control rounded-3 bg-light"
                                        readonly
                                        required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="distributor_nit" class="form-label fw-semibold small">NIT Distribuidor *</label>
                                    <input type="text"
                                        id="distributor_nit"
                                        name="distributor_nit"
                                        value="{{ $defaultDistributorNit }}"
                                        placeholder="Ej: 900123456-7"
                                        class="form-control rounded-3 bg-light"
                                        readonly
                                        required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="client_nit" class="form-label fw-semibold small">NIT Cliente *</label>
                                    <div class="input-group">
                                        <input type="text"
                                            id="client_nit"
                                            name="client_nit"
                                            value="{{ old('client_nit') }}"
                                            placeholder="Ej: 800123456-1"
                                            class="form-control rounded-start-3"
                                            required>
                                        <button type="button" id="btnLookupNit" class="btn btn-outline-primary rounded-end-3" title="Validar y buscar NIT">
                                            <i class="bi bi-search"></i> Validar
                                        </button>
                                    </div>
                                    <div id="nitFeedback" class="form-text small mt-1">
                                        Ingrese el NIT con o sin dígito de verificación.
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="associated_client" class="form-label fw-semibold small">Cliente asociado *</label>
                                    <input type="text"
                                        id="associated_client"
                                        name="associated_client"
                                        value="{{ old('associated_client') }}"
                                        placeholder="Razón social o nombre del cliente"
                                        class="form-control rounded-3"
                                        required>
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
                                <div class="col-md-4 mb-3">
                                    <label for="negotiation_date" class="form-label fw-semibold small">Fecha negociación *</label>
                                    <input type="date" id="negotiation_date" name="negotiation_date" value="{{ old('negotiation_date') }}" class="form-control rounded-3" required>
                                </div>

                                <!-- Constructor de Dirección Estandarizada -->
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-semibold small">Dirección de Instalación *</label>

                                    <div class="p-3 bg-light border rounded-3">
                                        <div class="row g-2">
                                            <div class="col-sm-3">
                                                <label for="addr_road_type" class="form-label text-muted mb-1" style="font-size: 0.72rem;">Vía *</label>
                                                <select id="addr_road_type" class="form-select form-select-sm addr-part" required>
                                                    <option value="">Vía...</option>
                                                    <option value="CR">CR (Carrera)</option>
                                                    <option value="CL">CL (Calle)</option>
                                                    <option value="AV">AV (Avenida)</option>
                                                    <option value="AVCRA">AVCRA (Av. Carrera)</option>
                                                    <option value="AVCL">AVCL (Av. Calle)</option>
                                                    <option value="DIG">DIG (Diagonal)</option>
                                                    <option value="TV">TV (Transversal)</option>
                                                </select>
                                            </div>

                                            <div class="col-sm-3">
                                                <label for="addr_main_num" class="form-label text-muted mb-1" style="font-size: 0.72rem;">Número *</label>
                                                <input type="text" id="addr_main_num" class="form-control form-control-sm addr-part" placeholder="Ej: 3 o 72" required>
                                            </div>

                                            <div class="col-sm-3">
                                                <label for="addr_orientation" class="form-label text-muted mb-1" style="font-size: 0.72rem;">Bis / Orientación</label>
                                                <select id="addr_orientation" class="form-select form-select-sm addr-part">
                                                    <option value="">Ninguno</option>
                                                    <option value="BIS">BIS</option>
                                                    <option value="ESTE">ESTE</option>
                                                    <option value="OESTE">OESTE</option>
                                                    <option value="SUR">SUR</option>
                                                    <option value="NORTE">NORTE</option>
                                                </select>
                                            </div>

                                            <div class="col-sm-3">
                                                <label for="addr_cross_num" class="form-label text-muted mb-1" style="font-size: 0.72rem;">Cruce / Placa *</label>
                                                <input type="text" id="addr_cross_num" class="form-control form-control-sm addr-part" placeholder="Ej: 90D-40" required>
                                            </div>

                                            <div class="col-sm-5">
                                                <label for="addr_complement_type" class="form-label text-muted mb-1" style="font-size: 0.72rem;">Complemento (opcional)</label>
                                                <select id="addr_complement_type" class="form-select form-select-sm addr-part">
                                                    <option value="">Sin complemento</option>
                                                    <option value="Apto">Apto</option>
                                                    <option value="Oficina">Oficina</option>
                                                    <option value="Local">Local</option>
                                                    <option value="Bodega">Bodega</option>
                                                    <option value="Torre">Torre</option>
                                                    <option value="Piso">Piso</option>
                                                </select>
                                            </div>

                                            <div class="col-sm-7">
                                                <label for="addr_complement_val" class="form-label text-muted mb-1" style="font-size: 0.72rem;">Detalle complemento</label>
                                                <input type="text" id="addr_complement_val" class="form-control form-control-sm addr-part" placeholder="Ej: 201, Bloque B, Bodega 4">
                                            </div>
                                        </div>

                                        <div class="mt-2 pt-2 border-top d-flex justify-content-between align-items-center small">
                                            <span class="text-muted">Dirección estandarizada:</span>
                                            <span id="addressPreview" class="fw-bold text-primary">{{ old('address') ?: 'Complete vía y números' }}</span>
                                        </div>
                                    </div>

                                    <!-- Valor final enviado al backend -->
                                    <input type="hidden" id="address" name="address" value="{{ old('address') }}" required>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roadTypeEl = document.getElementById('addr_road_type');
            const mainNumEl = document.getElementById('addr_main_num');
            const orientationEl = document.getElementById('addr_orientation');
            const crossNumEl = document.getElementById('addr_cross_num');
            const compTypeEl = document.getElementById('addr_complement_type');
            const compValEl = document.getElementById('addr_complement_val');
            const hiddenAddressEl = document.getElementById('address');
            const previewEl = document.getElementById('addressPreview');

            function cleanToken(str) {
                return (str || '').trim().replace(/\s+/g, ' ').toUpperCase();
            }

            function buildStandardAddress() {
                const road = cleanToken(roadTypeEl.value);
                const mainNum = cleanToken(mainNumEl.value);
                const orientation = cleanToken(orientationEl.value);
                const crossNum = cleanToken(crossNumEl.value);
                const compType = (compTypeEl.value || '').trim();
                const compVal = (compValEl.value || '').trim().replace(/\s+/g, ' ');

                if (!road && !mainNum && !crossNum) {
                    hiddenAddressEl.value = '';
                    previewEl.textContent = 'Complete vía y números';
                    return;
                }

                let parts = [];
                if (road) parts.push(road);
                if (mainNum) parts.push(mainNum);
                if (orientation) parts.push(orientation);
                if (crossNum) {
                    parts.push('#');
                    parts.push(crossNum);
                }

                let formatted = parts.join(' ').replace(' # ', ' # ');
                if (compType && compVal) {
                    formatted += ` ${compType} ${compVal}`;
                } else if (compVal) {
                    formatted += ` ${compVal}`;
                }

                hiddenAddressEl.value = formatted.trim();
                previewEl.textContent = hiddenAddressEl.value || 'Complete vía y números';
            }

            function hydrateAddressFields(rawAddress) {
                if (!rawAddress) return;
                const match = rawAddress.trim().match(/^(CR|CL|AV|AVCRA|AVCL|DIG|TV)\s+([^\s#]+)(?:\s+(BIS|ESTE|OESTE|SUR|NORTE))?\s*#\s*([^\s]+)(?:\s+(.+))?$/i);
                if (match) {
                    roadTypeEl.value = match[1].toUpperCase();
                    mainNumEl.value = match[2].toUpperCase();
                    if (match[3]) {
                        orientationEl.value = match[3].toUpperCase();
                    }
                    crossNumEl.value = match[4].toUpperCase();
                    if (match[5]) {
                        const rest = match[5].trim();
                        const compMatch = rest.match(/^(Apto|Oficina|Local|Bodega|Torre|Piso)\s+(.+)$/i);
                        if (compMatch) {
                            compTypeEl.value = compMatch[1].charAt(0).toUpperCase() + compMatch[1].slice(1).toLowerCase();
                            compValEl.value = compMatch[2].trim();
                        } else {
                            compValEl.value = rest;
                        }
                    }
                    buildStandardAddress();
                }
            }

            if (hiddenAddressEl.value) {
                hydrateAddressFields(hiddenAddressEl.value);
            }

            document.querySelectorAll('.addr-part').forEach(el => {
                el.addEventListener('input', buildStandardAddress);
                el.addEventListener('change', buildStandardAddress);
            });

            // Validación DIAN + consulta de cliente por NIT
            const clientNitInput = document.getElementById('client_nit');
            const btnLookupNit = document.getElementById('btnLookupNit');
            const nitFeedback = document.getElementById('nitFeedback');
            const associatedClientInput = document.getElementById('associated_client');
            const citySelect = document.getElementById('city');
            const responsibleNameInput = document.getElementById('responsible_name');
            const responsiblePhoneInput = document.getElementById('responsible_phone');
            const responsibleEmailInput = document.getElementById('responsible_email');

            function calculateDianDV(nitBase) {
                const clean = String(nitBase).replace(/\D/g, '');
                if (!clean || clean.length > 15) return null;

                const weights = [3, 7, 13, 17, 19, 23, 29, 37, 41, 43, 47, 53, 59, 67, 71];
                let sum = 0;

                for (let i = 0; i < clean.length; i++) {
                    const digit = parseInt(clean.charAt(clean.length - 1 - i), 10);
                    sum += digit * weights[i];
                }

                const mod = sum % 11;
                return mod > 1 ? 11 - mod : mod;
            }

            function validateNitFormat(rawValue) {
                const trimmed = (rawValue || '').trim();
                if (!trimmed) return {
                    valid: false,
                    message: ''
                };

                if (trimmed.includes('-')) {
                    const [basePart, dvPart] = trimmed.split('-');
                    const cleanBase = (basePart || '').replace(/\D/g, '');
                    const cleanDv = (dvPart || '').replace(/\D/g, '');

                    if (cleanBase.length < 5 || cleanDv.length !== 1) {
                        return {
                            valid: false,
                            message: 'Formato de NIT incompleto.'
                        };
                    }

                    const expectedDv = calculateDianDV(cleanBase);
                    if (parseInt(cleanDv, 10) !== expectedDv) {
                        return {
                            valid: false,
                            message: `Dígito de verificación inválido (para ${cleanBase} corresponde -${expectedDv}).`
                        };
                    }

                    return {
                        valid: true,
                        formatted: `${cleanBase}-${expectedDv}`,
                        base: cleanBase
                    };
                }

                const digitsOnly = trimmed.replace(/\D/g, '');
                if (digitsOnly.length < 5) {
                    return {
                        valid: false,
                        message: 'El NIT debe tener al menos 5 dígitos.'
                    };
                }

                const dv = calculateDianDV(digitsOnly);
                return {
                    valid: true,
                    formatted: `${digitsOnly}-${dv}`,
                    base: digitsOnly
                };
            }

            async function lookupClientByNit() {
                const rawNit = clientNitInput.value.trim();
                if (!rawNit) return;

                const check = validateNitFormat(rawNit);
                clientNitInput.classList.remove('is-valid', 'is-invalid');
                associatedClientInput.classList.remove('is-valid');

                if (!check.valid) {
                    clientNitInput.classList.add('is-invalid');
                    nitFeedback.className = 'form-text small text-danger fw-semibold mt-1';
                    nitFeedback.innerHTML = `<i class="bi bi-x-circle-fill me-1"></i> ${check.message}`;
                    return;
                }

                clientNitInput.value = check.formatted;
                clientNitInput.classList.add('is-valid');
                nitFeedback.className = 'form-text small text-primary mt-1';
                nitFeedback.innerHTML = `<i class="bi bi-arrow-repeat me-1"></i> NIT válido (${check.formatted}). Consultando registros...`;

                try {
                    const response = await fetch(`{{ route('clients.lookup') }}?nit=${encodeURIComponent(check.formatted)}`, {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });
                    const result = await response.json();

                    if (result.found && result.client) {
                        const c = result.client;
                        const resolvedName = c.business_name || c.name || c.razon_social || '';

                        if (resolvedName && associatedClientInput) {
                            associatedClientInput.value = resolvedName;
                            associatedClientInput.classList.add('is-valid');
                            associatedClientInput.dispatchEvent(new Event('input', {
                                bubbles: true
                            }));
                            associatedClientInput.dispatchEvent(new Event('change', {
                                bubbles: true
                            }));
                        }

                        if (c.city && citySelect) {
                            const targetCity = c.city.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
                            Array.from(citySelect.options).forEach(opt => {
                                if (opt.value.toLowerCase() === targetCity) citySelect.value = opt.value;
                            });
                        }
                        if (c.contact_name && responsibleNameInput && !responsibleNameInput.value) {
                            responsibleNameInput.value = c.contact_name;
                        }
                        if (c.phone && responsiblePhoneInput && responsiblePhoneInput.value.trim() === '+57') {
                            responsiblePhoneInput.value = c.phone;
                        }
                        if (c.email && responsibleEmailInput && !responsibleEmailInput.value) {
                            responsibleEmailInput.value = c.email;
                        }

                        if (c.address) {
                            hiddenAddressEl.value = c.address;
                            previewEl.textContent = c.address;
                            hydrateAddressFields(c.address);
                        }

                        const origenTexto = result.source === 'datos_abiertos' ?
                            'Datos Abiertos (RUES)' :
                            (result.source === 'external_api' ? 'API externa' : 'base de datos');

                        nitFeedback.className = 'form-text small text-success fw-semibold mt-1';
                        nitFeedback.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> NIT válido (${origenTexto}): <strong>${resolvedName}</strong>.`;
                    } else {
                        nitFeedback.className = 'form-text small text-success mt-1';
                        nitFeedback.innerHTML = `<i class="bi bi-shield-check me-1"></i> Dígito de verificación DIAN válido. Escriba la razón social para registrarlo.`;
                    }
                } catch (error) {
                    nitFeedback.className = 'form-text small text-warning mt-1';
                    nitFeedback.innerHTML = `<i class="bi bi-exclamation-circle me-1"></i> NIT válido matemáticamente, pero no se pudo consultar la fuente externa.`;
                }
            }

            btnLookupNit?.addEventListener('click', lookupClientByNit);
            clientNitInput?.addEventListener('blur', lookupClientByNit);
            clientNitInput?.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    lookupClientByNit();
                }
            });
        });
    </script>
</body>

</html>