<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    // Listado exclusivo de Solicitudes de Equipos / Instalaciones
    public function index()
    {
        $requests = ServiceRequest::whereNotIn('type', ['mantenimiento', 'auditoría', 'auditoria'])
            ->with(['client', 'items.product'])
            ->latest()
            ->get();

        return view('provider.requests.index', compact('requests'));
    }

    // Listado exclusivo de Agendamientos (Auditorías y Mantenimientos)
    public function appointmentsIndex()
    {
        $appointments = ServiceRequest::whereIn('type', ['mantenimiento', 'auditoría', 'auditoria'])
            ->with(['client', 'items.product'])
            ->latest()
            ->get();

        return view('provider.requests.appointments-index', compact('appointments'));
    }

    // Actualización de estado (Aprobar, Cancelar, etc.)
    public function updateStatus(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'status' => 'required|in:pendiente,confirmada,cancelada,despachado,instalado',
        ]);

        $serviceRequest->update([
            'status' => $request->status,
        ]);

        return back()->with('success', 'El estado del registro #' . $serviceRequest->id . ' ha sido actualizado a ' . ucfirst($request->status) . '.');
    }
}
