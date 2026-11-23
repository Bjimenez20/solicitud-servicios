<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Installer;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ServiceRequestController extends Controller
{
    public function index()
    {
        $requests = ServiceRequest::with(['client.user', 'provider.user', 'installer', 'items.product'])
            ->whereIn('status', ['confirmada', 'despachado'])
            ->whereNotIn('type', ['mantenimiento', 'auditoria'])
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return view('bodega.requests.index', compact('requests'));
    }

    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'status' => 'required|in:despachado',
        ]);

        if ($serviceRequest->status !== 'confirmada') {
            return back()->withErrors(['error' => 'Solo se pueden despachar solicitudes que hayan sido confirmadas previamente.']);
        }

        $serviceRequest->update([
            'status' => 'despachado',
        ]);

        return back()->with('success', "La solicitud #{$serviceRequest->id} ha sido marcada como despachada.");
    }

    public function scheduleForm(ServiceRequest $serviceRequest)
    {
        $serviceRequest->load(['client', 'provider', 'installer', 'items.product']);

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
            ->where('scheduled_time', $data['scheduled_time'])
            ->where('installer_id', $data['installer_id'])
            ->where('id', '!=', $serviceRequest->id)
            ->whereIn('status', ['despachado', 'instalado', 'confirmada'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'installer_id' => 'El instalador seleccionado ya cuenta con un agendamiento en este horario exacto. Elija otro.'
            ]);
        }

        $serviceRequest->update([
            'scheduled_date' => $data['scheduled_date'],
            'scheduled_time' => $data['scheduled_time'],
            'installer_id'   => $data['installer_id'],
            'status'         => 'despachado',
        ]);

        return redirect()->route('bodega.requests.index')
            ->with('success', '¡Instalación agendada, instalador asignado y solicitud despachada con éxito!');
    }

    public function getAvailableInstallers(Request $request)
    {
        $date = $request->input('date');
        $time = $request->input('time');

        if (!$date || !$time) {
            return response()->json(['installers' => []]);
        }

        $formattedTime = \Carbon\Carbon::parse($time)->format('H:i:s');

        $busyInstallerIds = ServiceRequest::where('scheduled_date', $date)
            ->where('scheduled_time', $time)
            ->whereIn('status', ['despachado', 'instalado', 'confirmada'])
            ->whereNotNull('installer_id')
            ->pluck('installer_id')
            ->toArray();

        $availableInstallers = Installer::whereNotIn('id', $busyInstallerIds)
            ->get(['id', 'name']);

        return response()->json(['installers' => $availableInstallers]);
    }
}
