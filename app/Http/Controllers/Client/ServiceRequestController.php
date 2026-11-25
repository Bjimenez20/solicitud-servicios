<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceRequestController extends Controller
{
    private function normalizeType(string $type): string
    {
        return Str::of($type)->ascii()->lower()->value();
    }

    /**
     * Cliente elige "Jornada AM" o "Jornada PM" (o una hora exacta).
     * Bodega asigna la hora final y exacta al agendar.
     */
    private function normalizeScheduledTime(string $time): string
    {
        $clean = trim($time);

        return match (strtoupper($clean)) {
            'JORNADA AM', 'AM' => '08:00:00',
            'JORNADA PM', 'PM' => '14:00:00',
            default            => Carbon::parse($clean)->format('H:i:s'),
        };
    }

    public function index()
    {
        $requests = ServiceRequest::where('created_by_user_id', auth()->id())
            ->whereNotIn('type', ['mantenimiento', 'auditoria'])
            ->with(['client', 'provider', 'installer', 'items.product'])
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('client.requests.index', compact('requests'));
    }

    public function appointmentsIndex()
    {
        $appointments = ServiceRequest::where('created_by_user_id', auth()->id())
            ->whereIn('type', ['mantenimiento', 'auditoria'])
            ->with(['client', 'provider', 'installer', 'items.product'])
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('client.requests.appointments-index', compact('appointments'));
    }

    public function create()
    {
        $currentUser = auth()->user();
        $provider = Provider::where('user_id', $currentUser->id)->first();
        $providers = Provider::orderBy('business_name')->get();

        return view('client.requests.create', compact('provider', 'providers'));
    }

    public function selectService()
    {
        return view('client.requests.select-service');
    }

    public function bookAppointment(Request $request)
    {
        $type = $request->query('tipo', 'Mantenimiento');
        $clients = Client::orderBy('business_name')->get();

        return view('client.requests.book-appointment', compact('type', 'clients'));
    }

    public function storeStepOne(Request $request)
    {
        $data = $request->validate([
            'provider_id'       => 'required|exists:providers,id',
            'distributor_name'  => 'nullable|string|max:255',
            'distributor_nit'   => 'nullable|string|max:50',
            'associated_client' => 'required|string|max:255',
            'client_nit'        => 'required|string|max:50',
            'city'              => 'required|string|max:100',
            'executive'         => 'nullable|string|max:100',
            'type'              => 'required|string|max:50',
            'segment'           => 'nullable|string|max:100',
            'negotiation_date'  => 'nullable|date',
            'address'           => 'required|string|max:255',
            'responsible_name'  => 'required|string|max:255',
            'responsible_phone' => 'required|string|max:50',
            'responsible_email' => 'required|email|max:255',
            'message_subject'   => 'required|string|max:255',
            'notes'             => 'nullable|string',
        ]);

        $data['type'] = $this->normalizeType($data['type'] ?? 'instalacion');
        session()->put('service_request_data', $data);

        return redirect()->route('client.requests.catalog');
    }

    public function catalog(Request $request)
    {
        if (!session()->has('service_request_data')) {
            return redirect()->route('client.requests.create')
                ->with('error', 'Por favor complete primero los datos de la solicitud.');
        }

        $query = Product::query();

        if ($search = trim((string) $request->query('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('name')->paginate(6)->withQueryString();
        $cart = session()->get('service_request_cart', []);

        return view('client.requests.catalog', compact('products', 'cart'));
    }

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);
        $cart = session()->get('service_request_cart', []);

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

        session()->put('service_request_cart', $cart);

        return back()->with('success', "Se agregaron {$request->quantity} unidad(es) de {$product->name} al carrito.");
    }

    public function updateCart(Request $request, $productId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = session()->get('service_request_cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = (int) $request->quantity;
            session()->put('service_request_cart', $cart);
        }

        return back()->with('success', 'Cantidad actualizada en el carrito.');
    }

    public function removeFromCart($productId)
    {
        $cart = session()->get('service_request_cart', []);
        unset($cart[$productId]);
        session()->put('service_request_cart', $cart);

        return back()->with('success', 'Producto retirado de la selección.');
    }

    public function confirmOrder()
    {
        $requestData = session()->get('service_request_data');
        $cart = session()->get('service_request_cart', []);

        if (!$requestData) {
            return redirect()->route('client.requests.create')
                ->with('error', 'Por favor complete primero los datos de la solicitud.');
        }

        if (empty($cart)) {
            return back()->withErrors(['error' => 'Debe agregar al menos un producto antes de confirmar.']);
        }

        $client = Client::updateOrCreate(
            ['nit' => $requestData['client_nit']],
            [
                'business_name' => $requestData['associated_client'],
                'city'          => $requestData['city'],
                'address'       => $requestData['address'],
                'phone'         => $requestData['responsible_phone'],
                'email'         => $requestData['responsible_email'],
            ]
        );

        $serviceRequest = ServiceRequest::create([
            'created_by_user_id' => auth()->id(),
            'client_id'          => $client->id,
            'provider_id'        => $requestData['provider_id'],
            'type'               => $this->normalizeType($requestData['type'] ?? 'instalacion'),
            'message_subject'    => $requestData['message_subject'] ?? 'Solicitud de equipos',
            'notes'              => $requestData['notes'] ?? null,
            'status'             => 'pendiente',
        ]);

        foreach ($cart as $item) {
            $serviceRequest->items()->create([
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
            ]);
        }

        session()->forget(['service_request_data', 'service_request_cart']);

        return redirect()->route('client.requests.index')
            ->with('success', '¡Solicitud confirmada! El proveedor ya puede ver el detalle de los equipos.');
    }

    public function storeAppointment(Request $request)
    {
        $data = $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'type'           => 'required|string',
            'provider_id'    => 'nullable|exists:providers,id',
            'notes'          => 'nullable|string',
        ]);

        $data['scheduled_time'] = $this->normalizeScheduledTime($data['scheduled_time']);

        ServiceRequest::create([
            'created_by_user_id' => auth()->id(),
            'client_id'          => null,
            'provider_id'        => $data['provider_id'] ?? null,
            'type'               => $this->normalizeType($data['type']),
            'scheduled_date'     => $data['scheduled_date'],
            'scheduled_time'     => $data['scheduled_time'],
            'message_subject'    => 'Agendamiento de ' . $data['type'],
            'notes'              => $data['notes'] ?? null,
            'status'             => 'pendiente',
        ]);

        return redirect()->route('client.requests.index')
            ->with('success', '¡Cita de ' . $data['type'] . ' agendada con éxito!');
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        if ($serviceRequest->created_by_user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para modificar esta solicitud.');
        }

        $request->validate([
            'status' => 'required|in:instalado,realizado,cancelada',
        ]);

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

    public function showInstallationSelector(Request $request)
    {
        $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'type'           => 'required|string',
            'client_id'      => 'nullable|exists:clients,id',
        ]);

        $date = $request->scheduled_date;
        $time = $request->scheduled_time;
        $type = $request->type;

        $query = ServiceRequest::where('status', 'instalado')
            ->with(['client', 'provider', 'items.product'])
            ->latest();

        if (!empty($request->client_id)) {
            $query->where('client_id', $request->client_id);
        } else {
            $query->where('created_by_user_id', auth()->id());
        }

        $installedRequests = $query->get();

        return view('client.requests.select-installation', compact('date', 'time', 'type', 'installedRequests'));
    }

    public function storeMaintenance(Request $request)
    {
        $data = $request->validate([
            'scheduled_date'     => 'required|date',
            'scheduled_time'     => 'required|string',
            'type'               => 'required|string',
            'service_request_id' => 'required|exists:service_requests,id',
            'notes'              => 'nullable|string',
        ]);

        $originalRequest = ServiceRequest::where('created_by_user_id', auth()->id())
            ->with('items')
            ->findOrFail($data['service_request_id']);

        $data['scheduled_time'] = $this->normalizeScheduledTime($data['scheduled_time']);

        $newRequest = ServiceRequest::create([
            'created_by_user_id' => auth()->id(),
            'client_id'          => $originalRequest->client_id,
            'provider_id'        => $originalRequest->provider_id,
            'type'               => $this->normalizeType($data['type']),
            'scheduled_date'     => $data['scheduled_date'],
            'scheduled_time'     => $data['scheduled_time'],
            'message_subject'    => $data['type'] . ' vinculado a Instalación #' . $originalRequest->id,
            'notes'              => $data['notes'] ?? $originalRequest->notes,
            'status'             => 'pendiente',
        ]);

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
