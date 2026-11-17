<!DOCTYPE html>
<html lang="es">

<x-head />
<style>
    .product-img {
        height: 190px;
        object-fit: cover;
        background-color: #f8f9fa;
    }
</style>


<body class="bg-light">

    <x-navbar />

    <div class="container py-5">

        <!-- Encabezado y botón volver -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary mb-1 px-3 py-1 rounded-pill fw-bold">Paso 2 de 2</span>
                <h2 class="fw-bold text-dark mb-0">Selección de Equipos</h2>
                <p class="text-muted small mb-0">Agregue los dispensadores o equipos requeridos para su solicitud.</p>
            </div>
            <a href="{{ route('client.requests.create') }}" class="btn btn-outline-secondary rounded-pill px-4">
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
                <div class="row g-4">
                    @foreach($products as $product)
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <!-- Foto del Producto -->
                            <img src="{{ $product->image ? asset('storage/' . $product->image) : 'https://placehold.co/600x400/e9ecef/495057?text=' . urlencode($product->name) }}"
                                class="card-img-top product-img" alt="{{ $product->name }}">

                            <div class="card-body d-flex flex-column justify-content-between p-4">
                                <div>
                                    <h5 class="card-title fw-bold text-dark mb-1">{{ $product->name }}</h5>
                                    <p class="card-text text-muted small mb-3">
                                        {{ $product->description ?? 'Equipo disponible para instalación y mantenimiento.' }}
                                    </p>
                                </div>

                                <form action="{{ route('client.cart.add') }}" method="POST" class="d-flex gap-2 align-items-center mt-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                                    <div class="input-group" style="width: 115px;">
                                        <span class="input-group-text bg-light border-end-0 small text-muted">Cant.</span>
                                        <input type="number" name="quantity" value="1" min="1" class="form-control text-center fw-bold">
                                    </div>

                                    <button type="submit" class="btn btn-primary flex-grow-1 fw-bold rounded-3">
                                        <i class="bi bi-cart-plus me-1"></i> Agregar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- COLUMNA DERECHA: Carrito / Resumen de Solicitud -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-white py-3 px-4 rounded-top-4">
                        <h5 class="mb-0 fw-bold fs-6 d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-bag-check me-2 text-warning"></i> Equipos Seleccionados</span>
                            <span class="badge bg-warning text-dark rounded-pill">{{ collect($cart)->sum('quantity') }}</span>
                        </h5>
                    </div>

                    <div class="card-body p-4">
                        @if(empty($cart))
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-cart-x display-5 d-block mb-2"></i>
                            <p class="small mb-0">Aún no ha agregado equipos a su solicitud.</p>
                        </div>
                        @else
                        <ul class="list-group list-group-flush mb-4">
                            @foreach($cart as $id => $item)
                            <li class="list-group-item px-0 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark small">{{ $item['name'] }}</h6>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill mt-1">
                                        Cantidad: {{ $item['quantity'] }}
                                    </span>
                                </div>
                                <form action="{{ route('client.cart.remove', $id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0 rounded-circle" title="Quitar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </li>
                            @endforeach
                        </ul>

                        <form action="{{ route('client.requests.confirm') }}" method="POST">
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
</body>

</html>