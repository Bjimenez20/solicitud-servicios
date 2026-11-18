<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    /**
     * Muestra el listado exclusivo de Solicitudes de Equipos / Instalaciones (Excluyendo agendamientos).
     */
    public function index()
    {
        $requests = ServiceRequest::whereNotIn('type', ['mantenimiento', 'auditoría', 'auditoria'])
            ->with(['client', 'items.product'])
            ->latest()
            ->get();

        return view('provider.requests.index', compact('requests'));
    }

    /**
     * Muestra el listado exclusivo de Agendamientos (Auditorías y Mantenimientos).
     */
    public function appointmentsIndex()
    {
        $appointments = ServiceRequest::whereIn('type', ['mantenimiento', 'auditoría', 'auditoria'])
            ->with(['client', 'items.product'])
            ->latest()
            ->get();

        return view('provider.requests.appointments-index', compact('appointments'));
    }

    /**
     * Actualiza el estado de una solicitud o agendamiento.
     */
    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'status' => 'required|in:pendiente,confirmada,cancelada,despachado,instalado,realizado',
        ]);

        $serviceRequest->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'El estado del registro #' . $serviceRequest->id . ' ha sido actualizado a ' . ucfirst($request->status) . '.');
    }

    // ==========================================
    // MÓDULO DE CREACIÓN DE SOLICITUDES
    // ==========================================

    /**
     * Paso 1: Muestra el formulario para ingresar datos iniciales de la solicitud.
     */
    public function create()
    {
        return view('client.requests.create');
    }

    /**
     * Almacena los datos del paso 1 en la sesión y redirige al catálogo.
     */
    public function storeStepOne(Request $request)
    {
        $validated = $request->validate([
            'distributor_name'  => 'required|string|max:255',
            'distributor_nit'   => 'required|string|max:50',
            'address'           => 'required|string|max:255',
            'city'              => 'required|string|max:100',
            'executive'         => 'required|string|max:255',
            'segment'           => 'required|string|max:100',
            'negotiation_date'  => 'required|date',
            'responsible_name'  => 'required|string|max:255',
            'responsible_phone' => 'required|string|max:50',
            'responsible_email' => 'required|email|max:255',
            'message_subject'   => 'nullable|string|max:255',
        ]);

        session(['service_request_data' => $validated]);

        return redirect()->route('provider.requests.catalog');
    }

    /**
     * Paso 2: Muestra el catálogo de productos y el carrito actual.
     */
    public function catalog()
    {
        if (!session()->has('service_request_data')) {
            return redirect()->route('provider.requests.create')
                ->with('error', 'Por favor complete primero los datos de la solicitud.');
        }

        $products = Product::all();
        $cart = session()->get('service_request_cart', []);

        return view('client.requests.catalog', compact('products', 'cart'));
    }

    /**
     * Agrega un producto al carrito almacenado en sesión.
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $cart = session()->get('service_request_cart', []);
        $productId = $request->product_id;

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $request->quantity;
        } else {
            $product = Product::findOrFail($productId);
            $cart[$productId] = [
                'name'     => $product->name,
                'price'    => $product->price ?? 0,
                'quantity' => $request->quantity,
            ];
        }

        session(['service_request_cart' => $cart]);

        return back()->with('success', 'Producto agregado al carrito.');
    }

    /**
     * Remueve un producto del carrito.
     */
    public function removeFromCart($productId)
    {
        $cart = session()->get('service_request_cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session(['service_request_cart' => $cart]);
        }

        return back()->with('success', 'Producto removido del carrito.');
    }

    /**
     * Paso 3: Confirma la orden y guarda la solicitud junto con sus ítems en la base de datos.
     */
    public function confirmOrder(Request $request)
    {
        $data = session('service_request_data');
        $cart = session('service_request_cart');

        if (!$data || empty($cart)) {
            return redirect()->route('provider.requests.create')
                ->with('error', 'No hay datos o productos en el carrito para procesar.');
        }

        $serviceRequest = ServiceRequest::create([
            'client_id'         => auth()->id(),
            'type'              => 'instalacion',
            'status'            => 'pendiente',
            'distributor_name'  => $data['distributor_name'],
            'distributor_nit'   => $data['distributor_nit'],
            'address'           => $data['address'],
            'city'              => $data['city'],
            'executive'         => $data['executive'],
            'segment'           => $data['segment'],
            'negotiation_date'  => $data['negotiation_date'],
            'responsible_name'  => $data['responsible_name'],
            'responsible_phone' => $data['responsible_phone'],
            'responsible_email' => $data['responsible_email'],
            'message_subject'   => $data['message_subject'] ?? 'Solicitud creada por Proveedor',
        ]);

        foreach ($cart as $productId => $item) {
            $serviceRequest->items()->create([
                'product_id' => $productId,
                'quantity'   => $item['quantity'],
            ]);
        }

        session()->forget(['service_request_data', 'service_request_cart']);

        return redirect()->route('provider.requests.index')
            ->with('success', '¡Solicitud creada exitosamente con ID #' . $serviceRequest->id . '!');
    }

    // ==========================================
    // MÓDULO DE AGENDAMIENTOS Y CITAS
    // ==========================================

    /**
     * Muestra la vista para seleccionar el tipo de servicio a agendar (Auditoría o Mantenimiento).
     */
    public function selectService()
    {
        return view('client.requests.select-service');
    }

    /**
     * Muestra la vista del calendario para reservar fecha y hora.
     */
    public function bookAppointment(Request $request)
    {
        $type = $request->query('tipo', 'Auditoría');
        return view('client.requests.book-appointment', compact('type'));
    }

    /**
     * Muestra el selector de instalaciones previas para vincular el mantenimiento o auditoría.
     */
    public function showInstallationSelector(Request $request)
    {
        $data = $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'type'           => 'required|string',
        ]);

        $installedRequests = ServiceRequest::where('status', 'instalado')
            ->with('items.product')
            ->latest()
            ->get();

        return view('client.requests.select-installation', [
            'installedRequests' => $installedRequests,
            'date'              => $data['scheduled_date'],
            'time'              => $data['scheduled_time'],
            'type'              => $data['type'],
        ]);
    }

    /**
     * Almacena el agendamiento vinculado en la base de datos.
     */
    public function storeMaintenance(Request $request)
    {
        $data = $request->validate([
            'scheduled_date'     => 'required|date',
            'scheduled_time'     => 'required|string',
            'type'               => 'required|string',
            'service_request_id' => 'required|exists:service_requests,id',
        ]);

        $originalRequest = ServiceRequest::with('items')->findOrFail($data['service_request_id']);

        $newRequest = ServiceRequest::create([
            'client_id'         => auth()->id(),
            'type'              => strtolower($data['type']),
            'scheduled_date'    => $data['scheduled_date'],
            'scheduled_time'    => $data['scheduled_time'],
            'address'           => $originalRequest->address,
            'city'              => $originalRequest->city,
            'status'            => 'confirmada', // Nace confirmada al ser creada directamente por el proveedor
            'distributor_name'  => $originalRequest->distributor_name ?? auth()->user()->name,
            'distributor_nit'   => $originalRequest->distributor_nit ?? 'N/A',
            'executive'         => $originalRequest->executive ?? 'Ejecutivo Proveedor',
            'segment'           => $originalRequest->segment ?? 'Empresarial',
            'negotiation_date'  => now(),
            'responsible_name'  => $originalRequest->responsible_name ?? auth()->user()->name,
            'responsible_phone' => $originalRequest->responsible_phone ?? '+57 3000000000',
            'responsible_email' => $originalRequest->responsible_email ?? auth()->user()->email,
            'message_subject'   => $data['type'] . ' vinculado a Instalación #' . $originalRequest->id,
        ]);

        foreach ($originalRequest->items as $item) {
            $newRequest->items()->create([
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
            ]);
        }

        return redirect()->route('provider.appointments.index')
            ->with('success', '¡Cita de ' . strtolower($data['type']) . ' agendada correctamente!');
    }
}
