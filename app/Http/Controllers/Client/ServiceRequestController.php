<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    // Listado exclusivo de Solicitudes de Equipos / Instalaciones
    public function index()
    {
        $requests = ServiceRequest::where('client_id', auth()->id())
            ->whereNotIn('type', ['mantenimiento', 'auditoría', 'auditoria'])
            ->with('items.product')
            ->latest()
            ->get();

        return view('client.requests.index', compact('requests'));
    }

    // Listado exclusivo de Agendamientos (Mantenimientos y Auditorías)
    public function appointmentsIndex()
    {
        $appointments = ServiceRequest::where('client_id', auth()->id())
            ->whereIn('type', ['mantenimiento', 'auditoría', 'auditoria'])
            ->with('items.product')
            ->latest()
            ->get();

        return view('client.requests.appointments-index', compact('appointments'));
    }

    // PASO 1: Mostrar formulario con todos tus campos (sin el select de producto)
    public function create()
    {
        return view('client.requests.create');
    }

    public function selectService()
    {
        return view('client.requests.select-service');
    }

    public function bookAppointment()
    {
        return view('client.requests.book-appointment');
    }

    // Guardar datos del formulario en Sesión y mandar a la tienda
    public function storeStepOne(Request $request)
    {
        $data = $request->validate([
            'distributor_name'    => 'required|string|max:255',
            'distributor_nit'     => 'required|string|max:50',
            'associated_client'   => 'nullable|string|max:255',
            'client_nit'          => 'nullable|string|max:50',
            'city'                => 'required|string|max:100',
            'executive'           => 'required|string|max:100',
            'type'                => 'required|string|max:50',
            'segment'             => 'required|string|max:100',
            'negotiation_date'    => 'required|date',
            'address'             => 'required|string|max:255',
            'responsible_name'    => 'required|string|max:255',
            'responsible_phone'   => 'required|string|max:50',
            'responsible_email'   => 'required|email|max:255',
            'message_subject'     => 'required|string|max:255',
        ]);

        session()->put('request_data', $data);

        return redirect()->route('client.requests.catalog');
    }

    // PASO 2: Vista tipo Tienda Online con fotos y carrito
    public function catalog()
    {
        // Si no ha llenado el formulario primero, lo devolvemos al paso 1
        if (!session()->has('request_data')) {
            return redirect()->route('client.requests.create')
                ->withErrors(['error' => 'Por favor complete primero los datos del servicio.']);
        }

        $products = Product::all();
        $cart = session()->get('request_cart', []);

        return view('client.requests.catalog', compact('products', 'cart'));
    }

    // Agregar o actualizar cantidad en el carrito
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('request_cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id]['quantity'] += $request->quantity;
        } else {
            $cart[$product->id] = [
                'product_id' => $product->id,
                'name'       => $product->name,
                'image'      => $product->image,
                'quantity'   => $request->quantity,
            ];
        }

        session()->put('request_cart', $cart);

        return back()->with('success', "Se agregaron {$request->quantity} unidad(es) de {$product->name} al carrito.");
    }

    // Quitar producto del carrito
    public function removeFromCart($productId)
    {
        $cart = session()->get('request_cart', []);
        unset($cart[$productId]);
        session()->put('request_cart', $cart);

        return back()->with('success', 'Producto retirado de la selección.');
    }

    // PASO 3: Confirmar solicitud y guardar cabecera + ítems en MySQL
    public function confirmOrder()
    {
        $requestData = session()->get('request_data');
        $cart = session()->get('request_cart', []);

        if (!$requestData || empty($cart)) {
            return back()->withErrors(['error' => 'Debe agregar al menos un producto antes de confirmar.']);
        }

        // Crear la solicitud principal con todos los datos del formulario
        $serviceRequest = ServiceRequest::create(array_merge($requestData, [
            'client_id' => auth()->id(),
            'status'    => 'pendiente',
        ]));

        // Guardar cada producto y cantidad en request_items
        foreach ($cart as $item) {
            $serviceRequest->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
            ]);
        }

        // Limpiar sesión
        session()->forget(['request_data', 'request_cart']);

        return redirect()->route('client.requests.index')
            ->with('success', '¡Solicitud confirmada! El proveedor ya puede ver el detalle de los equipos.');
    }

    public function storeAppointment(Request $request)
    {
        $data = $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'type'           => 'required|string', // Auditoría o Mantenimiento
        ]);

        ServiceRequest::create([
            'client_id'      => auth()->id(),
            'type'           => strtolower($data['type']),
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $data['scheduled_time'],
            'address'        => 'Presencial en punto principal',
            'city'           => 'Bogota',
            'status'         => 'pendiente',
            // Rellenamos campos obligatorios por defecto para cumplir con la BD
            'distributor_name'  => auth()->user()->name,
            'distributor_nit'   => 'N/A',
            'executive'         => 'Ejecutivo 1',
            'segment'           => 'Empresarial',
            'negotiation_date'  => now(),
            'responsible_name'  => auth()->user()->name,
            'responsible_phone' => '+57 3000000000',
            'responsible_email' => auth()->user()->email,
            'message_subject'   => 'Agendamiento de ' . $data['type'],
        ]);

        return redirect()->route('client.requests.index')
            ->with('success', '¡Cita de ' . $data['type'] . ' agendada con éxito!');
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        // Validar que la solicitud pertenezca al cliente en sesión
        if ($serviceRequest->client_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta solicitud.');
        }

        $request->validate([
            'status' => 'required|in:instalado,realizado,cancelada',
        ]);

        // Regla de negocio: solo marcar como realizado si ya fue confirmada por el proveedor
        if ($request->status === 'realizado' && $serviceRequest->status !== 'confirmada') {
            return back()->with('error', 'La cita debe estar confirmada por el proveedor antes de marcarla como realizada.');
        }

        $serviceRequest->update([
            'status' => $request->status,
        ]);

        $mensaje = $request->status === 'realizado'
            ? '¡Excelente! Has confirmado que el servicio fue realizado correctamente.'
            : 'Estado actualizado correctamente.';

        return back()->with('success', $mensaje);
    }

    // Recibe fecha y hora del calendario y muestra las solicitudes instaladas
    public function showInstallationSelector(Request $request)
    {
        $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'type'           => 'required|string',
        ]);

        $date = $request->scheduled_date;
        $time = $request->scheduled_time;
        $type = $request->type;

        // Buscar solicitudes del cliente que ya estén en estado 'instalado'
        $installedRequests = ServiceRequest::where('client_id', auth()->id())
            ->where('status', 'instalado')
            ->with('items.product')
            ->get();

        return view('client.requests.select-installation', compact('date', 'time', 'type', 'installedRequests'));
    }

    // Guarda el mantenimiento heredando automáticamente la dirección y datos de la instalación elegida
    public function storeMaintenance(Request $request)
    {
        $data = $request->validate([
            'scheduled_date'     => 'required|date',
            'scheduled_time'     => 'required|string',
            'type'               => 'required|string',
            'service_request_id' => 'required|exists:service_requests,id',
        ]);

        // Obtener la instalación original con sus productos
        $originalRequest = ServiceRequest::where('client_id', auth()->id())
            ->with('items')
            ->findOrFail($data['service_request_id']);

        // Crear la nueva solicitud heredando ubicación y datos comerciales
        $newRequest = ServiceRequest::create([
            'client_id'         => auth()->id(),
            'type'              => strtolower($data['type']), // 'mantenimiento' o 'auditoría'
            'scheduled_date'    => $data['scheduled_date'],
            'scheduled_time'    => $data['scheduled_time'],
            'address'           => $originalRequest->address,
            'city'              => $originalRequest->city,
            'status'            => 'pendiente',
            'distributor_name'  => $originalRequest->distributor_name ?? auth()->user()->name,
            'distributor_nit'   => $originalRequest->distributor_nit ?? 'N/A',
            'associated_client' => $originalRequest->associated_client ?? null,
            'client_nit'        => $originalRequest->client_nit ?? null,
            'executive'         => $originalRequest->executive ?? 'Ejecutivo 1',
            'segment'           => $originalRequest->segment ?? 'Empresarial',
            'negotiation_date'  => now(),
            'responsible_name'  => $originalRequest->responsible_name ?? auth()->user()->name,
            'responsible_phone' => $originalRequest->responsible_phone ?? '+57 3000000000',
            'responsible_email' => $originalRequest->responsible_email ?? auth()->user()->email,
            'message_subject'   => $data['type'] . ' vinculado a Instalación #' . $originalRequest->id,
        ]);

        // Copiar los productos de la instalación original a la nueva solicitud
        foreach ($originalRequest->items as $item) {
            $newRequest->items()->create([
                'product_id' => $item->product_id,
                'quantity'   => $item->quantity,
            ]);
        }

        return redirect()->route('client.requests.index')
            ->with('success', '¡Cita de ' . strtolower($data['type']) . ' agendada y vinculada a los equipos de la Solicitud #' . $originalRequest->id . '!');
    }
}
