<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use App\Models\AuditLog;
use App\Services\EmergencyReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HospitalEmergencyController extends Controller
{
    public function __construct(
        protected EmergencyReportingService $reporting
    ) {}

    /**
     * List emergencies for the hospital (hospital_admin sees own hospital; super_admin sees all or filter).
     * Query params: hospital_id, patient_id, status, from, to.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $hospitalIdScope = $user->role === 'hospital_admin' ? $user->hospital_id : null;
        if ($user->role === 'super_admin' && $request->integer('hospital_id') > 0) {
            $hospitalIdScope = $request->integer('hospital_id');
        }
        $filters = [
            'patient_id' => $request->integer('patient_id') ?: null,
            'status' => $request->query('status'),
            'hospital_id' => $user->role === 'super_admin' ? $request->query('hospital_id') : null,
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ];
        $query = $this->reporting->filteredQuery($filters, $hospitalIdScope)
            ->with(['patient:id,name,phone', 'ambulance:id,plate_number,driver_id', 'ambulance.driver:id,name,phone', 'hospital:id,name,slug']);
        $emergencies = $query
            ->orderByRaw("CASE status WHEN 'requested' THEN 1 WHEN 'assigned' THEN 2 WHEN 'enroute' THEN 3 WHEN 'arrived' THEN 4 WHEN 'closed' THEN 5 ELSE 6 END")
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
                'severity_category' => $e->severity_category,
                'eta_minutes' => $e->eta_minutes,
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
                'doctor_notes' => $emergency->doctor_notes,
                'admission_info' => $emergency->admission_info,
                'discharge_summary' => $emergency->discharge_summary,
            ],
        ]);
    }

    /**
     * Update emergency notes (doctor_notes, admission_info, discharge_summary). Hospital admin only for own hospital.
     */
    public function updateNotes(Request $request, Emergency $emergency): JsonResponse
    {
        if ($request->user()->role === 'hospital_admin' && $request->user()->hospital_id !== $emergency->hospital_id) {
            abort(403);
        }
        $request->validate([
            'doctor_notes' => ['nullable', 'string', 'max:5000'],
            'admission_info' => ['nullable', 'string', 'max:5000'],
            'discharge_summary' => ['nullable', 'string', 'max:5000'],
        ]);
        $old = $emergency->only(['doctor_notes', 'admission_info', 'discharge_summary']);
        $emergency->update($request->only(['doctor_notes', 'admission_info', 'discharge_summary']));
        AuditLog::log('emergency.notes_updated', Emergency::class, $emergency->id, $old, $emergency->only(['doctor_notes', 'admission_info', 'discharge_summary']));
        return response()->json(['message' => 'Notes updated.', 'emergency_id' => $emergency->id]);
    }
}
