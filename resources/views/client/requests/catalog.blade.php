@php
$isProvider = auth()->user()->isProvider();
$routePrefix = $isProvider ? 'provider.' : 'client.';
$totalUnits = collect($cart)->sum('quantity');
$totalProductsCount = method_exists($products, 'total') ? $products->total() : $products->count();
@endphp

<!DOCTYPE html>
<html lang="es">

<x-head />
<style>
    :root {
        --corp-primary: #1b3a6b;
        --corp-primary-hover: #132b4e;
    }

    .product-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: 1px solid #e2e8f0 !important;
    }

    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(15, 23, 42, 0.12) !important;
    }

    .product-img-wrapper {
        position: relative;
        height: 200px;
        background: #ffffff;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        padding: 12px;
    }

    .product-img {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .product-card:hover .product-img {
        transform: scale(1.04);
    }

    .in-cart-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 2;
    }

    /* Stepper + / - estilo e-commerce */
    .qty-stepper {
        display: inline-flex;
        align-items: center;
        border: 1px solid #cbd5e1;
        border-radius: 0.65rem;
        background-color: #f8fafc;
        overflow: hidden;
        height: 40px;
    }

    .qty-stepper.qty-stepper-sm {
        height: 30px;
        border-radius: 0.5rem;
    }

    .qty-stepper button {
        width: 34px;
        height: 100%;
        border: none;
        background: transparent;
        color: #1e293b;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background-color 0.15s ease, color 0.15s ease;
    }

    .qty-stepper.qty-stepper-sm button {
        width: 26px;
        font-size: 0.8rem;
    }

    .qty-stepper button:hover {
        background-color: #e2e8f0;
        color: var(--corp-primary);
    }

    .qty-stepper input[type="number"] {
        width: 44px;
        height: 100%;
        border: none;
        background: #ffffff;
        text-align: center;
        font-weight: 700;
        color: #0f172a;
        font-size: 0.9rem;
        -moz-appearance: textfield;
    }

    .qty-stepper.qty-stepper-sm input[type="number"] {
        width: 36px;
        font-size: 0.8rem;
    }

    .qty-stepper input[type="number"]::-webkit-outer-spin-button,
    .qty-stepper input[type="number"]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .qty-stepper input[type="number"]:focus {
        outline: none;
    }

    .btn-add-cart {
        background-color: var(--corp-primary);
        border-color: var(--corp-primary);
        color: #ffffff;
        height: 40px;
        border-radius: 0.65rem;
        font-size: 0.875rem;
        transition: background-color 0.2s ease;
    }

    .btn-add-cart:hover {
        background-color: var(--corp-primary-hover);
        border-color: var(--corp-primary-hover);
        color: #ffffff;
    }

    .pagination {
        margin-bottom: 0;
        gap: 4px;
    }

    .pagination .page-link {
        border-radius: 0.5rem;
        color: var(--corp-primary);
        border: 1px solid #dee2e6;
        font-weight: 600;
        padding: 0.4rem 0.8rem;
    }

    .pagination .page-item.active .page-link {
        background-color: var(--corp-primary);
        border-color: var(--corp-primary);
        color: #ffffff;
    }
</style>

<body class="bg-light">

    <x-navbar />

    <div class="container py-5">

        <!-- Encabezado y botón volver -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary mb-1 px-3 py-1 rounded-pill fw-bold">Paso 2 de 2</span>
                <h2 class="fw-bold text-dark mb-0">Catálogo de Equipos</h2>
                <p class="text-muted small mb-0">Seleccione las cantidades de dispensadores requeridos para su solicitud.</p>
            </div>
            <a href="{{ route($routePrefix . 'requests.create') }}" class="btn btn-outline-secondary rounded-pill px-4 align-self-start align-self-md-center">
                <i class="bi bi-arrow-left me-1"></i> Editar Datos del Servicio
            </a>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            {{ $errors->first() }}
        </div>
        @endif

        <div class="row g-4">
            <!-- COLUMNA IZQUIERDA: Tienda / Cuadrícula de Productos -->
            <div class="col-lg-8">

                <!-- Barra superior de búsqueda y contador estilo e-commerce -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                        <form action="{{ route($routePrefix . 'requests.catalog') }}" method="GET" class="flex-grow-1">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 text-muted rounded-start-pill ps-3">
                                    <i class="bi bi-search"></i>
                                </span>
                                <input type="text"
                                    name="q"
                                    id="catalogSearchInput"
                                    value="{{ request('q') }}"
                                    class="form-control border-start-0 rounded-end-pill shadow-none"
                                    placeholder="Buscar dispensador por nombre o descripción...">
                                @if(request('q'))
                                <a href="{{ route($routePrefix . 'requests.catalog') }}" class="btn btn-link text-muted text-decoration-none small">
                                    Limpiar
                                </a>
                                @endif
                            </div>
                        </form>

                        <span class="badge bg-light text-secondary border rounded-pill px-3 py-2 small">
                            {{ $totalProductsCount }} producto(s)
                        </span>
                    </div>
                </div>

                <!-- Cuadrícula de productos -->
                <div class="row g-4" id="productsGrid">
                    @forelse($products as $product)
                    @php
                    $qtyInCart = $cart[$product->id]['quantity'] ?? 0;
                    $stockVal = (int) ($product->stock ?? 0);
                    @endphp
                    <div class="col-sm-6 col-xl-4 product-item" data-search="{{ strtolower($product->name . ' ' . ($product->description ?? '')) }}">
                        <div class="card h-100 product-card shadow-sm rounded-4 overflow-hidden bg-white">
                            <!-- Imagen con contenedor limpio -->
                            <div class="product-img-wrapper">
                                @if($qtyInCart > 0)
                                <span class="badge bg-success rounded-pill px-3 py-2 shadow-sm in-cart-badge">
                                    <i class="bi bi-check2 me-1"></i>{{ $qtyInCart }} en carrito
                                </span>
                                @endif

                                <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://placehold.co/600x400/f1f5f9/475569?text=' . urlencode($product->name) }}"
                                    class="product-img" alt="{{ $product->name }}">
                            </div>

                            <div class="card-body d-flex flex-column justify-content-between p-3 p-md-4">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span class="text-uppercase text-primary fw-bold" style="font-size: 0.68rem; letter-spacing: 0.04em;">
                                            Dispensador
                                        </span>

                                        @if($isProvider)
                                        <span class="badge {{ $stockVal > 0 ? 'bg-light text-dark border' : 'bg-danger-subtle text-danger' }} rounded-pill px-2 py-1" style="font-size: 0.7rem;">
                                            Stock: {{ $stockVal }}
                                        </span>
                                        @endif
                                    </div>

                                    <h6 class="card-title fw-bold text-dark mt-1 mb-2">{{ $product->name }}</h6>
                                    <p class="card-text text-muted small mb-0" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ $product->description ?? 'Equipo disponible para instalación y mantenimiento.' }}
                                    </p>
                                </div>

                                <form action="{{ route($routePrefix . 'cart.add') }}" method="POST" class="d-flex align-items-center gap-2 pt-2 border-top">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                                    <!-- Selector + / - -->
                                    <div class="qty-stepper" aria-label="Selector de cantidad">
                                        <button type="button" class="btn-qty-minus" aria-label="Disminuir cantidad">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" name="quantity" value="1" min="1" max="999" class="qty-input" required>
                                        <button type="button" class="btn-qty-plus" aria-label="Aumentar cantidad">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>

                                    <button type="submit" class="btn btn-add-cart flex-grow-1 fw-bold px-2">
                                        <i class="bi bi-cart-plus me-1"></i> Agregar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 p-5 text-center text-muted">
                            <i class="bi bi-box-seam display-5 d-block mb-2"></i>
                            <p class="mb-0 fw-semibold">No se encontraron equipos con ese criterio.</p>
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Paginación Laravel (si $products es Paginator) -->
                @if(method_exists($products, 'hasPages') && $products->hasPages())
                <div class="card border-0 shadow-sm rounded-4 mt-4">
                    <div class="card-body py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                        <div class="text-muted small">
                            Mostrando <strong>{{ $products->firstItem() }}–{{ $products->lastItem() }}</strong> de <strong>{{ $products->total() }}</strong> equipos
                        </div>
                        <div>
                            {{ $products->links() }}
                        </div>
                    </div>
                </div>
                @else
                <div id="clientPaginationWrapper" class="card border-0 shadow-sm rounded-4 mt-4 d-none">
                    <div class="card-body py-3 px-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                        <div class="text-muted small" id="clientPaginationInfo"></div>
                        <nav aria-label="Paginación de productos">
                            <ul class="pagination mb-0" id="clientPaginationControls"></ul>
                        </nav>
                    </div>
                </div>
                @endif
            </div>

            <!-- COLUMNA DERECHA: Carrito / Resumen de Solicitud -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white py-3 px-4 rounded-top-4">
                        <h5 class="mb-0 fw-bold fs-6 d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-bag-check me-2 text-warning"></i> Equipos Seleccionados</span>
                            <span class="badge bg-warning text-dark rounded-pill">{{ $totalUnits }}</span>
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        @if(empty($cart))
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-cart-x display-5 d-block mb-2"></i>
                            <p class="small mb-0">Aún no ha agregado equipos a su solicitud.</p>
                        </div>
                        @else
                        <div class="d-flex justify-content-between text-muted small pb-2 mb-2 border-bottom">
                            <span>Referencias: <strong>{{ count($cart) }}</strong></span>
                            <span>Total unidades: <strong>{{ $totalUnits }}</strong></span>
                        </div>

                        <ul class="list-group list-group-flush mb-4">
                            @foreach($cart as $id => $item)
                            <li class="list-group-item px-0 py-3">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <h6 class="mb-2 fw-bold text-dark small">{{ $item['name'] }}</h6>
                                    <form action="{{ route($routePrefix . 'cart.remove', $id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle p-1" title="Quitar del carrito">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>

                                <!-- Sumar o restar cantidad directamente en el carrito -->
                                <form action="{{ route($routePrefix . 'cart.update', $id) }}" method="POST" class="d-flex align-items-center gap-2 cart-update-form">
                                    @csrf
                                    @method('PATCH')

                                    <div class="qty-stepper qty-stepper-sm" aria-label="Ajustar cantidad en carrito">
                                        <button type="button" class="btn-qty-minus" aria-label="Restar unidad">
                                            <i class="bi bi-dash"></i>
                                        </button>
                                        <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="999" class="qty-input" required>
                                        <button type="button" class="btn-qty-plus" aria-label="Sumar unidad">
                                            <i class="bi bi-plus"></i>
                                        </button>
                                    </div>

                                    <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                        Actualizar
                                    </button>
                                </form>
                            </li>
                            @endforeach
                        </ul>

                        <form action="{{ route($routePrefix . 'requests.confirm') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold rounded-3 shadow">
                                <i class="bi bi-check2-circle me-1"></i> Confirmar Solicitud
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-footer />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Controles + y - en tarjetas del catálogo y en ítems del carrito
            document.querySelectorAll('.qty-stepper').forEach(stepper => {
                const minusBtn = stepper.querySelector('.btn-qty-minus');
                const plusBtn = stepper.querySelector('.btn-qty-plus');
                const input = stepper.querySelector('.qty-input');
                const updateForm = stepper.closest('.cart-update-form');

                minusBtn.addEventListener('click', () => {
                    const current = parseInt(input.value, 10) || 1;
                    if (current > 1) {
                        input.value = current - 1;
                        if (updateForm) updateForm.submit();
                    }
                });

                plusBtn.addEventListener('click', () => {
                    const current = parseInt(input.value, 10) || 1;
                    const max = parseInt(input.max, 10) || 999;
                    if (current < max) {
                        input.value = current + 1;
                        if (updateForm) updateForm.submit();
                    }
                });

                input.addEventListener('change', () => {
                    const val = parseInt(input.value, 10);
                    if (isNaN(val) || val < 1) input.value = 1;
                    if (updateForm) updateForm.submit();
                });
            });

            // 2. Paginación de respaldo en cliente si el controlador envía Collection en lugar de Paginator
            const clientWrapper = document.getElementById('clientPaginationWrapper');
            if (clientWrapper) {
                const items = Array.from(document.querySelectorAll('.product-item'));
                const searchInput = document.getElementById('catalogSearchInput');
                const infoEl = document.getElementById('clientPaginationInfo');
                const controlsEl = document.getElementById('clientPaginationControls');
                const perPage = 6;
                let currentPage = 1;

                function getFilteredItems() {
                    const term = (searchInput?.value || '').trim().toLowerCase();
                    if (!term) return items;
                    return items.filter(el => (el.dataset.search || '').includes(term));
                }

                function renderPage(page) {
                    const filtered = getFilteredItems();
                    const total = filtered.length;
                    const totalPages = Math.max(1, Math.ceil(total / perPage));
                    currentPage = Math.min(Math.max(1, page), totalPages);

                    const start = (currentPage - 1) * perPage;
                    const end = start + perPage;

                    items.forEach(el => el.classList.add('d-none'));
                    filtered.slice(start, end).forEach(el => el.classList.remove('d-none'));

                    if (total > perPage) {
                        clientWrapper.classList.remove('d-none');
                        infoEl.innerHTML = `Mostrando <strong>${start + 1}–${Math.min(end, total)}</strong> de <strong>${total}</strong> equipos`;

                        controlsEl.innerHTML = '';
                        for (let i = 1; i <= totalPages; i++) {
                            const li = document.createElement('li');
                            li.className = `page-item ${i === currentPage ? 'active' : ''}`;
                            const btn = document.createElement('button');
                            btn.type = 'button';
                            btn.className = 'page-link';
                            btn.textContent = i;
                            btn.addEventListener('click', () => renderPage(i));
                            li.appendChild(btn);
                            controlsEl.appendChild(li);
                        }
                    } else {
                        clientWrapper.classList.add('d-none');
                    }
                }

                if (searchInput) {
                    searchInput.addEventListener('input', () => renderPage(1));
                }

                renderPage(1);
            }
        });
    </script>
</body>

</html>