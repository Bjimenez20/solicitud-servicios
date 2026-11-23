<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Provider;

class ServiceRequestController extends Controller
{
    private function currentClient(): Client
    {
        $user = auth()->user();

        return Client::firstOrCreate(
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
        $client = $this->currentClient();
        $userId = auth()->id();

        $requests = ServiceRequest::where(function ($query) use ($client, $userId) {
            $query->where('created_by_user_id', $userId)
                ->orWhere('client_id', $client->id);
        })
            ->whereNotIn('type', ['mantenimiento', 'auditoria'])
            ->with(['provider', 'installer', 'items.product'])
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('client.requests.index', compact('requests'));
    }

    public function appointmentsIndex()
    {
        $client = $this->currentClient();
        $userId = auth()->id();

        $appointments = ServiceRequest::where(function ($query) use ($client, $userId) {
            $query->where('created_by_user_id', $userId)
                ->orWhere('client_id', $client->id);
        })
            ->whereIn('type', ['mantenimiento', 'auditoria'])
            ->with(['provider', 'installer', 'items.product'])
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('client.requests.appointments-index', compact('appointments'));
    }

    public function create()
    {
        $providers = Provider::with('user')->orderBy('business_name')->get();
        $client = Client::where('user_id', auth()->id())->first();

        return view('client.requests.create', compact('providers', 'client'));
    }

    public function selectService()
    {
        return view('client.requests.select-service');
    }

    public function bookAppointment(Request $request)
    {
        $type = $request->query('tipo', 'Auditoría');

        return view('client.requests.book-appointment', compact('type'));
    }

    public function storeStepOne(Request $request)
    {
        $data = $request->validate([
            'provider_id'       => 'nullable|exists:providers,id',
            'distributor_name'  => 'nullable|string|max:255',
            'distributor_nit'   => 'nullable|string|max:50',
            'associated_client' => 'nullable|string|max:255',
            'client_nit'        => 'nullable|string|max:50',
            'city'              => 'nullable|string|max:100',
            'executive'         => 'nullable|string|max:100',
            'type'              => 'required|string|max:50',
            'segment'           => 'nullable|string|max:100',
            'negotiation_date'  => 'nullable|date',
            'address'           => 'nullable|string|max:255',
            'responsible_name'  => 'nullable|string|max:255',
            'responsible_phone' => 'nullable|string|max:50',
            'responsible_email' => 'nullable|email|max:255',
            'message_subject'   => 'nullable|string|max:255',
            'notes'             => 'nullable|string',
        ]);

        $data['type'] = $this->normalizeType($data['type']);
        session()->put('request_data', $data);

        return redirect()->route('client.requests.catalog');
    }

    public function catalog()
    {
        if (!session()->has('request_data')) {
            return redirect()->route('client.requests.create')
                ->withErrors(['error' => 'Por favor complete primero los datos del servicio.']);
        }

        $products = Product::all();
        $cart = session()->get('request_cart', []);

        return view('client.requests.catalog', compact('products', 'cart'));
    }

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

    public function removeFromCart($productId)
    {
        $cart = session()->get('request_cart', []);
        unset($cart[$productId]);
        session()->put('request_cart', $cart);

        return back()->with('success', 'Producto retirado de la selección.');
    }

    public function confirmOrder()
    {
        $requestData = session()->get('request_data');
        $cart = session()->get('request_cart', []);

        if (!$requestData || empty($cart)) {
            return back()->withErrors(['error' => 'Debe agregar al menos un producto antes de confirmar.']);
        }

        $client = $this->currentClient();

        $serviceRequest = ServiceRequest::create([
            'created_by_user_id' => auth()->id(),
            'client_id'          => $client->id,
            'provider_id'        => $requestData['provider_id'] ?? null,
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

        session()->forget(['request_data', 'request_cart']);

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

        $client = $this->currentClient();

        ServiceRequest::create([
            'created_by_user_id' => auth()->id(),
            'client_id'          => $client->id,
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
        $client = $this->currentClient();

        if ($serviceRequest->client_id !== $client->id && $serviceRequest->created_by_user_id !== auth()->id()) {
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
        ]);

        $client = $this->currentClient();
        $date = $request->scheduled_date;
        $time = $request->scheduled_time;
        $type = $request->type;

        $installedRequests = ServiceRequest::where('client_id', $client->id)
            ->where('status', 'instalado')
            ->with(['client.user', 'provider', 'items.product'])
            ->get();

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

        $client = $this->currentClient();

        $originalRequest = ServiceRequest::where('client_id', $client->id)
            ->with('items')
            ->findOrFail($data['service_request_id']);
        $data['scheduled_time'] = Carbon::parse($data['scheduled_time'])->format('H:i:s');

        $newRequest = ServiceRequest::create([
            'created_by_user_id' => auth()->id(),
            'client_id'          => $client->id,
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
