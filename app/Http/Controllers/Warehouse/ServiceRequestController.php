<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Mail\ServiceScheduledMail;
use App\Models\Installer;
use App\Models\ServiceRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $requests = ServiceRequest::with(['client', 'provider.user', 'installer', 'items.product', 'creator'])
            ->where(function ($query) {
                $query->whereIn('status', ['confirmada', 'despachado'])
                    ->orWhere(function ($sub) {
                        $sub->whereIn('status', ['agendado', 'Agendado']);
                    });
            })
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('bodega.requests.index', compact('requests'));
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'status' => 'required|in:despachado,realizado',
        ]);

        if ($request->status === 'despachado') {
            if ($serviceRequest->status !== 'confirmada') {
                return back()->withErrors([
                    'error' => 'Solo se pueden despachar solicitudes que hayan sido confirmadas previamente.'
                ]);
            }

            $serviceRequest->update([
                'status' => 'despachado',
            ]);

            return back()->with('success', "La solicitud #{$serviceRequest->id} ha sido marcada como despachada.");
        }

        if ($request->status === 'realizado') {
            $currentStatus = strtolower((string) $serviceRequest->status);

            if ($currentStatus !== 'agendado') {
                return back()->withErrors([
                    'error' => 'Solo se pueden marcar como realizados los servicios que se encuentren en estado agendado.'
                ]);
            }

            $serviceRequest->update([
                'status' => 'realizado',
            ]);

            return back()->with('success', "El mantenimiento/servicio #{$serviceRequest->id} ha sido confirmado como realizado.");
        }

        return back();
    }

    public function scheduleForm(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['client', 'provider', 'installer', 'items.product', 'creator']);

        return view('bodega.requests.schedule', compact('serviceRequest'));
    }

    public function storeSchedule(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'installer_id'   => 'required|exists:installers,id',
        ]);

        $formattedTime = Carbon::parse($data['scheduled_time'])->format('H:i:s');

        $exists = ServiceRequest::where('scheduled_date', $data['scheduled_date'])
            ->where('scheduled_time', $formattedTime)
            ->where('installer_id', $data['installer_id'])
            ->where('id', '!=', $serviceRequest->id)
            ->whereIn('status', ['agendado', 'Agendado', 'despachado', 'instalado', 'confirmada'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'installer_id' => 'El instalador seleccionado ya cuenta con un agendamiento en este horario exacto. Elija otro.'
            ]);
        }

        $serviceRequest->update([
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $formattedTime,
            'installer_id'   => $data['installer_id'],
            'status'         => 'agendado',
        ]);

        $serviceRequest->refresh()->load(['client', 'provider.user', 'installer', 'items.product', 'creator']);

        $recipients = collect([
            $serviceRequest->client?->email,
            $serviceRequest->creator?->email,
            $serviceRequest->provider?->email,
            $serviceRequest->provider?->user?->email,
        ])
            ->filter(fn($email) => !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        $mailSent = false;

        if (!empty($recipients)) {
            try {
                Mail::to($recipients)->send(new ServiceScheduledMail($serviceRequest));
                $mailSent = true;
            } catch (\Throwable $e) {
                Log::warning("No se pudo enviar el correo de confirmación para la solicitud #{$serviceRequest->id}: " . $e->getMessage());
            }
        }

        $message = $mailSent
            ? '¡Fecha, hora final e instalador asignados y correo de confirmación enviado con éxito!'
            : '¡Fecha, hora final e instalador asignados con éxito! (Revisa la configuración de correo si no se envió la notificación).';

        return redirect()->route('bodega.requests.index')
            ->with('success', $message);
    }

    public function getAvailableInstallers(Request $request)
    {
        $date = $request->input('date');
        $time = $request->input('time');

        if (!$date || !$time) {
            return response()->json(['installers' => []]);
        }

        $formattedTime = Carbon::parse($time)->format('H:i:s');

        $busyInstallerIds = ServiceRequest::where('scheduled_date', $date)
            ->where('scheduled_time', $formattedTime)
            ->whereIn('status', ['agendado', 'Agendado', 'despachado', 'instalado', 'confirmada'])
            ->whereNotNull('installer_id')
            ->pluck('installer_id')
            ->toArray();

        $availableInstallers = Installer::whereNotIn('id', $busyInstallerIds)
            ->get(['id', 'name']);

        return response()->json(['installers' => $availableInstallers]);
    }
}
