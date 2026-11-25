<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Product;
use App\Models\Provider;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    /**
     * Cliente y Proveedor envían "Jornada AM" o "Jornada PM" (o una hora exacta).
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
        $requests = ServiceRequest::whereNotIn('type', ['mantenimiento', 'auditoria'])
            ->with(['client', 'provider.user', 'installer', 'items.product', 'creator'])
            ->latest()
            ->paginate(5)
            ->withQueryString();

        return view('provider.requests.index', compact('requests'));
    }

    public function appointmentsIndex()
    {
        $appointments = ServiceRequest::whereIn('type', ['mantenimiento', 'auditoria'])
            ->with(['client', 'provider.user', 'installer', 'items.product'])
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

        $data['scheduled_time'] = $this->normalizeScheduledTime($data['scheduled_time']);
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
            'status'             => 'confirmada',
        ]);

        return redirect()->route('provider.requests.index')
            ->with('success', '¡Cita de ' . $data['type'] . ' agendada con éxito!');
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $provider = $this->currentProvider();

        $request->validate([
            'status'         => 'required|in:pendiente,confirmada,cancelada,despachado,instalado,agendado,realizado',
            'provider_notes' => 'nullable|string',
        ]);

        $newStatus = strtolower($request->status);
        $previousStatus = strtolower((string) $serviceRequest->status);
        $typeNormalized = $this->normalizeType((string) $serviceRequest->type);

        $outOfStockProducts = [];

        DB::transaction(function () use (
            $serviceRequest,
            $provider,
            $request,
            $newStatus,
            $previousStatus,
            $typeNormalized,
            &$outOfStockProducts
        ) {
            $serviceRequest->load('items.product');

            $shouldDeductStock = $typeNormalized === 'instalacion'
                && in_array($newStatus, ['confirmada', 'instalado'], true)
                && !in_array($previousStatus, ['confirmada', 'instalado'], true);

            if ($shouldDeductStock) {
                foreach ($serviceRequest->items as $item) {
                    $qty = (int) $item->quantity;
                    $product = Product::lockForUpdate()->find($item->product_id);

                    if ($product) {
                        $newStock = max(0, (int) $product->stock - $qty);
                        $product->update(['stock' => $newStock]);

                        if ($newStock === 0) {
                            $outOfStockProducts[] = $product->name;
                        }
                    }
                }
            }

            $serviceRequest->update([
                'provider_id'    => $serviceRequest->provider_id ?? $provider->id,
                'status'         => $newStatus,
                'provider_notes' => $request->provider_notes ?? $serviceRequest->provider_notes,
            ]);
        });

        $redirect = back()->with(
            'success',
            'El estado del registro #' . $serviceRequest->id . ' ha sido actualizado a ' . ucfirst($newStatus) . '.'
        );

        if (!empty($outOfStockProducts)) {
            $names = implode(', ', array_unique($outOfStockProducts));
            $redirect->with(
                'warning',
                "Alerta de inventario: ya no hay stock disponible para: {$names}. La instalación se confirmó sin inconvenientes."
            );
        }

        return $redirect;
    }

    public function create()
    {
        $provider = $this->currentProvider();
        $providers = Provider::orderBy('business_name')->get();
        return view('client.requests.create', compact('provider', 'providers'));
    }

    public function storeStepOne(Request $request)
    {
        $validated = $request->validate([
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

        $validated['type'] = $this->normalizeType($validated['type'] ?? 'instalacion');
        session(['service_request_data' => $validated]);

        return redirect()->route('provider.requests.catalog');
    }

    public function myRequests()
    {
        $requests = ServiceRequest::where('created_by_user_id', auth()->id())
            ->whereNotIn('type', ['mantenimiento', 'auditoria'])
            ->with(['client', 'provider', 'installer', 'items.product'])
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('provider.requests.mine', compact('requests'));
    }

    public function catalog(Request $request)
    {
        if (!session()->has('service_request_data')) {
            return redirect()->route('provider.requests.create')
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
        $typeNormalized = $this->normalizeType($data['type'] ?? 'instalacion');
        $outOfStockProducts = [];

        $serviceRequest = DB::transaction(function () use ($data, $cart, $provider, $typeNormalized, &$outOfStockProducts) {
            $client = Client::updateOrCreate(
                ['nit' => $data['client_nit']],
                [
                    'business_name' => $data['associated_client'],
                    'city'          => $data['city'],
                    'address'       => $data['address'],
                    'phone'         => $data['responsible_phone'],
                    'email'         => $data['responsible_email'],
                ]
            );

            $req = ServiceRequest::create([
                'created_by_user_id' => auth()->id(),
                'client_id'          => $client->id,
                'provider_id'        => $provider->id,
                'type'               => $typeNormalized,
                'status'             => 'confirmada',
                'message_subject'    => $data['message_subject'] ?? 'Solicitud creada por Proveedor',
                'notes'              => $data['notes'] ?? null,
            ]);

            foreach ($cart as $productId => $item) {
                $qty = (int) $item['quantity'];

                $req->items()->create([
                    'product_id' => $productId,
                    'quantity'   => $qty,
                ]);

                if ($typeNormalized === 'instalacion') {
                    $product = Product::lockForUpdate()->find($productId);

                    if ($product) {
                        $newStock = max(0, (int) $product->stock - $qty);
                        $product->update(['stock' => $newStock]);

                        if ($newStock === 0) {
                            $outOfStockProducts[] = $product->name;
                        }
                    }
                }
            }

            return $req;
        });

        session()->forget(['service_request_data', 'service_request_cart']);

        $redirect = redirect()->route('provider.requests.index')
            ->with('success', '¡Solicitud creada exitosamente con ID #' . $serviceRequest->id . '!');

        if (!empty($outOfStockProducts)) {
            $names = implode(', ', array_unique($outOfStockProducts));
            $redirect->with(
                'warning',
                "Alerta de inventario: ya no hay stock disponible para: {$names}. La solicitud se registró sin inconvenientes."
            );
        }

        return $redirect;
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
            ->with(['client', 'provider.user', 'items.product'])
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
        $data['scheduled_time'] = $this->normalizeScheduledTime($data['scheduled_time']);

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
