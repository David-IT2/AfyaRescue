<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use App\Services\EmergencyFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverEmergencyController extends Controller
{
    public function __construct(
        protected EmergencyFlowService $emergencyFlow
    ) {}

    /**
     * List emergencies assigned to the current driver.
     */
    public function index(Request $request): JsonResponse
    {
        $ambulanceIds = $request->user()->driverAmbulance()->pluck('id');
        $emergencies = Emergency::whereIn('ambulance_id', $ambulanceIds)
            ->with(['patient:id,name,phone', 'hospital:id,name', 'ambulance:id,plate_number'])
            ->whereNotIn('status', [Emergency::STATUS_CLOSED])
            ->orderByRaw("CASE status WHEN 'requested' THEN 1 WHEN 'assigned' THEN 2 WHEN 'enroute' THEN 3 WHEN 'arrived' THEN 4 ELSE 5 END")
            ->orderByDesc('created_at')
            ->get();
        return response()->json([
            'emergencies' => $emergencies->map(fn (Emergency $e) => [
                'id' => $e->id,
                'status' => $e->status,
                'latitude' => $e->latitude,
                'longitude' => $e->longitude,
                'address_text' => $e->address_text,
                'severity_label' => $e->severity_label,
                'patient' => $e->patient ? ['name' => $e->patient->name, 'phone' => $e->patient->phone] : null,
                'hospital' => $e->hospital ? ['name' => $e->hospital->name] : null,
            ]),
        ]);
    }

    /**
     * Update emergency status (assigned -> enroute -> arrived -> closed).
     */
    public function updateStatus(Request $request, Emergency $emergency): JsonResponse
    {
        $ambulanceIds = $request->user()->driverAmbulance()->pluck('id');
        if (! in_array($emergency->ambulance_id, $ambulanceIds)) {
            abort(403);
        }
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:enroute,arrived,closed'],
        ]);
        $updated = $this->emergencyFlow->updateStatus($emergency, $validated['status']);
        return response()->json(['message' => 'Status updated.', 'emergency' => ['id' => $updated->id, 'status' => $updated->status]]);
    }
}
