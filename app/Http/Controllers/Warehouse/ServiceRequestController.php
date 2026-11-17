<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

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
}
