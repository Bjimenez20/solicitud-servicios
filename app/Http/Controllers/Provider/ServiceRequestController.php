<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ServiceRequestController extends Controller
{
    private function currentProvider(): Provider
    {
        $user = auth()->user();

        return Provider::firstOrCreate(
            ['user_id' => $user->id],
            [
                'business_name' => $user->name,
                'email'         => $user->email,
            ]
        );
    }

    private function normalizeType(string $type): string
    {
        return Str::of($type)->ascii()->lower()->value();
    }

    public function index()
    {
        $requests = ServiceRequest::whereNotIn('type', ['mantenimiento', 'auditoria'])
            ->with(['client.user', 'provider.user', 'installer', 'items.product'])
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('provider.requests.index', compact('requests'));
    }

    public function appointmentsIndex()
    {
        $appointments = ServiceRequest::whereIn('type', ['mantenimiento', 'auditoria'])
            ->with(['client.user', 'provider.user', 'installer', 'items.product'])
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('provider.requests.appointments-index', compact('appointments'));
    }

    public function storeAppointment(Request $request)
    {
        $data = $request->validate([
            'client_id'      => 'required|exists:clients,id',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'type'           => 'required|string',
            'notes'          => 'nullable|string',
        ]);

        $provider = $this->currentProvider();

        ServiceRequest::create([
            'created_by_user_id' => auth()->id(),
            'client_id'          => $data['client_id'],
            'provider_id'        => $provider->id,
            'type'               => $this->normalizeType($data['type']),
            'scheduled_date'     => $data['scheduled_date'],
            'scheduled_time'     => $data['scheduled_time'],
            'message_subject'    => 'Agendamiento de ' . $data['type'],
            'notes'              => $data['notes'] ?? null,
            'status'             => 'pendiente',
        ]);

        return redirect()->route('provider.requests.index')
            ->with('success', '¡Cita de ' . $data['type'] . ' agendada con éxito!');
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $provider = $this->currentProvider();

        $request->validate([
            'status'         => 'required|in:pendiente,confirmada,cancelada,despachado,instalado,realizado',
            'provider_notes' => 'nullable|string',
        ]);

        $serviceRequest->update([
            'provider_id'    => $serviceRequest->provider_id ?? $provider->id,
            'status'         => $request->status,
            'provider_notes' => $request->provider_notes ?? $serviceRequest->provider_notes,
        ]);

        return back()->with('success', 'El estado del registro #' . $serviceRequest->id . ' ha sido actualizado a ' . ucfirst($request->status) . '.');
    }

    public function create()
    {
        $clients = Client::with('user')->orderBy('business_name')->get();
        $provider = Provider::where('user_id', auth()->id())->first();

        return view('client.requests.create', compact('clients', 'provider'));
    }

    public function storeStepOne(Request $request)
    {
        $validated = $request->validate([
            'client_id'       => 'required|exists:clients,id',
            'type'            => 'nullable|string|max:50',
            'message_subject' => 'nullable|string|max:255',
            'notes'           => 'nullable|string',
        ]);

        $validated['type'] = $this->normalizeType($validated['type'] ?? 'instalacion');
        session(['service_request_data' => $validated]);

        return redirect()->route('provider.requests.catalog');
    }

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
                'quantity' => $request->quantity,
            ];
        }

        session(['service_request_cart' => $cart]);

        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function removeFromCart($productId)
    {
        $cart = session()->get('service_request_cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session(['service_request_cart' => $cart]);
        }

        return back()->with('success', 'Producto removido del carrito.');
    }

    public function confirmOrder(Request $request)
    {
        $data = session('service_request_data');
        $cart = session('service_request_cart');

        if (!$data || empty($cart)) {
            return redirect()->route('provider.requests.create')
                ->with('error', 'No hay datos o productos en el carrito para procesar.');
        }

        $provider = $this->currentProvider();

        $serviceRequest = ServiceRequest::create([
            'created_by_user_id' => auth()->id(),
            'client_id'          => $data['client_id'],
            'provider_id'        => $provider->id,
            'type'               => $this->normalizeType($data['type'] ?? 'instalacion'),
            'status'             => 'pendiente',
            'message_subject'    => $data['message_subject'] ?? 'Solicitud creada por Proveedor',
            'notes'              => $data['notes'] ?? null,
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

    public function showInstallationSelector(Request $request)
    {
        $provider = $this->currentProvider();

        $data = $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'type'           => 'required|string',
            'client_id'      => 'nullable|exists:clients,id',
        ]);

        $query = ServiceRequest::where('status', 'instalado')
            ->where('status', 'instalado')
            ->with(['client.user', 'provider', 'items.product'])
            ->latest();

        if (!empty($data['client_id'])) {
            $query->where('client_id', $data['client_id']);
        }

        $installedRequests = $query->get();

        return view('client.requests.select-installation', [
            'installedRequests' => $installedRequests,
            'date'              => $data['scheduled_date'],
            'time'              => $data['scheduled_time'],
            'type'              => $data['type'],
        ]);
    }

    public function storeMaintenance(Request $request)
    {
        $provider = $this->currentProvider();

        $data = $request->validate([
            'scheduled_date'     => 'required|date',
            'scheduled_time'     => 'required|string',
            'type'               => 'required|string',
            'service_request_id' => 'required|exists:service_requests,id',
            'notes'              => 'nullable|string',
        ]);

        $originalRequest = ServiceRequest::with('items')->findOrFail($data['service_request_id']);
        $data['scheduled_time'] = Carbon::parse($data['scheduled_time'])->format('H:i:s');

        $newRequest = ServiceRequest::create([
            'created_by_user_id' => auth()->id(),
            'client_id'          => $originalRequest->client_id,
            'provider_id'        => $provider->id,
            'type'               => $this->normalizeType($data['type']),
            'scheduled_date'     => $data['scheduled_date'],
            'scheduled_time'     => $data['scheduled_time'],
            'status'             => 'confirmada',
            'message_subject'    => $data['type'] . ' vinculado a Instalación #' . $originalRequest->id,
            'notes'              => $data['notes'] ?? $originalRequest->notes,
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
