<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use App\Models\Installer;

class ServiceRequestController extends Controller
{
    /**
     * Lista las solicitudes relevantes para bodega (Excluyendo agendamientos de mantenimiento y auditoría).
     */
    public function index()
    {
        // Trae únicamente solicitudes de compra/instalación confirmadas o despachadas
        $requests = ServiceRequest::with(['client', 'product'])
            ->whereIn('status', ['confirmada', 'despachado'])
            ->whereNotIn('type', ['mantenimiento', 'auditoría', 'auditoria']) // <-- FILTRO CLAVE
            ->latest()
            ->get();

        return view('bodega.requests.index', compact('requests'));
    }

    /**
     * Cambia el estado de la solicitud a 'despachado'.
     */
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

    /**
     * Muestra la vista del calendario para que bodega elija fecha y hora de instalación.
     */
    public function scheduleForm(ServiceRequest $serviceRequest)
    {
        return view('bodega.requests.schedule', compact('serviceRequest'));
    }

    /**
     * Guarda la fecha y hora de instalación y actualiza el estado a despachado (o el que consideres).
     */
    public function storeSchedule(Request $request, ServiceRequest $serviceRequest)
    {
        $data = $request->validate([
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'required|string',
            'installer_id'   => 'required|exists:installers,id',
        ]);

        // Validar estrictamente que el instalador no tenga cruce de horario
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

        // 1. Obtener los IDs de los instaladores que YA tienen una solicitud activa en esa fecha y hora exacta
        $busyInstallerIds = ServiceRequest::where('scheduled_date', $date)
            ->where('scheduled_time', $time)
            ->whereIn('status', ['despachado', 'instalado', 'confirmada'])
            ->whereNotNull('installer_id')
            ->pluck('installer_id')
            ->toArray();

        // 2. Consultar de la base de datos solo los instaladores activos que NO estén en la lista de ocupados
        $availableInstallers = Installer::where('is_active', true)
            ->whereNotIn('id', $busyInstallerIds)
            ->get(['id', 'name']);

        return response()->json(['installers' => $availableInstallers]);
    }
}
