<?php

use App\Http\Controllers\Client\ServiceRequestController as ClientRequestController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Provider\ServiceRequestController as ProviderRequestController;
use App\Http\Controllers\Warehouse\ServiceRequestController as WarehouseRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // Redirección automática al iniciar sesión según el rol
    Route::get('/dashboard', function () {
        $user = auth()->user();

        if ($user->role === 'bodega') {
            return redirect()->route('bodega.requests.index');
        }

        // Tanto Cliente como Proveedor entran a la vista de bienvenida
        return view('client.welcome');
    })->name('dashboard');

    // Perfil de usuario (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas del Cliente
    Route::middleware(['role:cliente'])->prefix('cliente')->name('client.')->group(function () {

        Route::get('/gestion', function () {
            return view('client.requests.hub');
        })->name('requests.hub');

        // 1. Módulo de Solicitudes (Compras / Equipos / Instalación)
        Route::get('/solicitudes', [ClientRequestController::class, 'index'])->name('requests.index');
        Route::get('/solicitudes/crear', [ClientRequestController::class, 'create'])->name('requests.create');
        Route::post('/solicitudes/datos', [ClientRequestController::class, 'storeStepOne'])->name('requests.stepOne');
        Route::get('/catalogo', [ClientRequestController::class, 'catalog'])->name('requests.catalog');
        Route::post('/carrito/agregar', [ClientRequestController::class, 'addToCart'])->name('cart.add');
        Route::delete('/carrito/quitar/{productId}', [ClientRequestController::class, 'removeFromCart'])->name('cart.remove');
        Route::post('/solicitudes/confirmar', [ClientRequestController::class, 'confirmOrder'])->name('requests.confirm');
        Route::patch('/solicitudes/{serviceRequest}/estado', [ClientRequestController::class, 'updateStatus'])->name('requests.updateStatus');

        // 2. Módulo de Agendamientos / Citas (Auditorías y Mantenimientos)
        Route::get('/agendamientos', [ClientRequestController::class, 'appointmentsIndex'])->name('appointments.index');
        Route::get('/agendamientos/seleccionar-tipo', [ClientRequestController::class, 'selectService'])->name('services.select');
        Route::get('/agendamientos/reservar', [ClientRequestController::class, 'bookAppointment'])->name('services.book');
        Route::post('/agendamientos/guardar', [ClientRequestController::class, 'storeAppointment'])->name('services.store');
        Route::post('/agendamientos/seleccionar-instalacion', [ClientRequestController::class, 'showInstallationSelector'])->name('services.selectInstallation');
        Route::post('/agendamientos/guardar-mantenimiento', [ClientRequestController::class, 'storeMaintenance'])->name('services.storeMaintenance');
    });

    // Rutas del Proveedor
    Route::middleware(['role:proveedor'])->prefix('proveedor')->name('provider.')->group(function () {

        Route::get('/gestion', function () {
            return view('provider.requests.hub');
        })->name('requests.hub');

        // Solicitudes
        Route::get('/solicitudes', [ProviderRequestController::class, 'index'])->name('requests.index');
        Route::get('/solicitudes/crear', [ProviderRequestController::class, 'create'])->name('requests.create');
        Route::post('/solicitudes/datos', [ProviderRequestController::class, 'storeStepOne'])->name('requests.stepOne');
        Route::get('/catalogo', [ProviderRequestController::class, 'catalog'])->name('requests.catalog');
        Route::post('/carrito/agregar', [ProviderRequestController::class, 'addToCart'])->name('cart.add');
        Route::delete('/carrito/quitar/{productId}', [ProviderRequestController::class, 'removeFromCart'])->name('cart.remove');
        Route::post('/solicitudes/confirmar', [ProviderRequestController::class, 'confirmOrder'])->name('requests.confirm');
        Route::patch('/solicitudes/{serviceRequest}/estado', [ProviderRequestController::class, 'updateStatus'])->name('requests.updateStatus');
        Route::get('/mis-solicitudes', [ProviderRequestController::class, 'myRequests'])->name('requests.mine');
        // Agendamientos
        Route::get('/agendamientos', [ProviderRequestController::class, 'appointmentsIndex'])->name('appointments.index');
        Route::get('/agendamientos/seleccionar-tipo', [ProviderRequestController::class, 'selectService'])->name('services.select');
        Route::get('/agendamientos/reservar', [ProviderRequestController::class, 'bookAppointment'])->name('services.book');
        Route::post('/agendamientos/guardar', [ProviderRequestController::class, 'storeAppointment'])->name('services.store');
        Route::post('/agendamientos/seleccionar-instalacion', [ProviderRequestController::class, 'showInstallationSelector'])->name('services.selectInstallation');
        Route::post('/agendamientos/guardar-mantenimiento', [ProviderRequestController::class, 'storeMaintenance'])->name('services.storeMaintenance');
    });

    // Rutas de Bodega
    Route::middleware(['role:bodega'])->prefix('bodega')->name('bodega.')->group(function () {
        Route::get('/solicitudes', [WarehouseRequestController::class, 'index'])->name('requests.index');
        Route::get('/solicitudes/{serviceRequest}/agendar', [WarehouseRequestController::class, 'scheduleForm'])->name('requests.scheduleForm');
        Route::post('/solicitudes/{serviceRequest}/agendar', [WarehouseRequestController::class, 'storeSchedule'])->name('requests.storeSchedule');
        Route::patch('/solicitudes/{serviceRequest}/estado', [WarehouseRequestController::class, 'updateStatus'])->name('requests.updateStatus');
        Route::get('/instaladores-disponibles', [WarehouseRequestController::class, 'getAvailableInstallers'])->name('requests.availableInstallers');
    });
});

require __DIR__ . '/auth.php';
