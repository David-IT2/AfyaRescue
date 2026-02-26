<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HospitalEmergencyController extends Controller
{
    /**
     * List emergencies for the hospital (hospital_admin sees own hospital; super_admin sees all or filter).
     */
    public function index(Request $request): JsonResponse
    {
        $query = Emergency::with(['patient:id,name,phone', 'ambulance:id,plate_number,driver_id', 'ambulance.driver:id,name,phone', 'hospital:id,name,slug']);
        if ($request->user()->role === 'hospital_admin' && $request->user()->hospital_id) {
            $query->where('hospital_id', $request->user()->hospital_id);
        }
        if ($request->user()->role === 'super_admin') {
            $hospitalId = $request->integer('hospital_id');
            if ($hospitalId > 0) {
                $query->where('hospital_id', $hospitalId);
            }
        }
        $emergencies = $query->orderByRaw("CASE status WHEN 'requested' THEN 1 WHEN 'assigned' THEN 2 WHEN 'enroute' THEN 3 WHEN 'arrived' THEN 4 WHEN 'closed' THEN 5 ELSE 6 END")
            ->orderByDesc('severity_score')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
        return response()->json([
            'emergencies' => $emergencies->map(fn (Emergency $e) => [
                'id' => $e->id,
                'status' => $e->status,
                'severity_score' => $e->severity_score,
                'severity_label' => $e->severity_label,
                'address_text' => $e->address_text,
                'requested_at' => $e->requested_at?->toIso8601String(),
                'patient' => $e->patient ? ['id' => $e->patient->id, 'name' => $e->patient->name, 'phone' => $e->patient->phone] : null,
                'ambulance' => $e->ambulance ? [
                    'id' => $e->ambulance->id,
                    'plate_number' => $e->ambulance->plate_number,
                    'driver' => $e->ambulance->driver ? ['name' => $e->ambulance->driver->name, 'phone' => $e->ambulance->driver->phone] : null,
                ] : null,
                'hospital' => $e->hospital ? ['id' => $e->hospital->id, 'name' => $e->hospital->name] : null,
            ]),
        ]);
    }

    public function show(Request $request, Emergency $emergency): JsonResponse
    {
        if ($request->user()->role === 'hospital_admin' && $request->user()->hospital_id !== $emergency->hospital_id) {
            abort(403);
        }
        $emergency->load(['patient', 'ambulance.driver', 'hospital', 'triageResponse']);
        return response()->json([
            'emergency' => [
                'id' => $emergency->id,
                'status' => $emergency->status,
                'severity_score' => $emergency->severity_score,
                'severity_label' => $emergency->severity_label,
                'latitude' => $emergency->latitude,
                'longitude' => $emergency->longitude,
                'address_text' => $emergency->address_text,
                'requested_at' => $emergency->requested_at?->toIso8601String(),
                'assigned_at' => $emergency->assigned_at?->toIso8601String(),
                'enroute_at' => $emergency->enroute_at?->toIso8601String(),
                'arrived_at' => $emergency->arrived_at?->toIso8601String(),
                'closed_at' => $emergency->closed_at?->toIso8601String(),
                'patient' => $emergency->patient,
                'ambulance' => $emergency->ambulance?->load('driver'),
                'hospital' => $emergency->hospital,
                'triage_responses' => $emergency->triageResponse?->responses,
            ],
        ]);
    }
}
