<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Emergency;
use App\Models\User;
use App\Services\EmergencyFlowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmergencyController extends Controller
{
    public function __construct(
        protected EmergencyFlowService $emergencyFlow
    ) {}

    /**
     * Submit new emergency (patient).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address_text' => ['nullable', 'string', 'max:500'],
            'triage' => ['required', 'array'],
            'triage.conscious' => ['nullable', 'boolean'],
            'triage.breathing' => ['nullable', 'string', 'in:normal,difficult,absent'],
            'triage.bleeding' => ['nullable', 'string', 'in:none,minor,severe'],
            'triage.chest_pain' => ['nullable', 'boolean'],
            'triage.stroke_symptoms' => ['nullable', 'boolean'],
            'triage.pregnancy_emergency' => ['nullable', 'boolean'],
            'triage.allergic_reaction' => ['nullable', 'boolean'],
            'triage.number_of_casualties' => ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        $emergency = $this->emergencyFlow->createEmergency(
            $request->user()->id,
            $validated['hospital_id'],
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $validated['address_text'] ?? null,
            $validated['triage']
        );

        $emergency->load(['hospital:id,name,slug', 'ambulance:id,plate_number,driver_id', 'ambulance.driver:id,name,phone']);
        return response()->json([
            'message' => 'Emergency request submitted.',
            'emergency' => $this->emergencyResource($emergency),
        ], 201);
    }

    /**
     * List current user's emergencies (patient).
     */
    public function myEmergencies(Request $request): JsonResponse
    {
        $emergencies = Emergency::where('patient_id', $request->user()->id)
            ->with(['hospital:id,name,slug', 'ambulance:id,plate_number,driver_id', 'ambulance.driver:id,name,phone'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
        return response()->json(['emergencies' => $emergencies->map(fn (Emergency $e) => $this->emergencyResource($e))]);
    }

    /**
     * Show single emergency (owner or authorized role).
     */
    public function show(Request $request, Emergency $emergency): JsonResponse
    {
        $user = $request->user();
        $allowed = $emergency->patient_id === $user->id
            || ($emergency->ambulance && $emergency->ambulance->driver_id === $user->id)
            || ($user->hospital_id && $emergency->hospital_id === $user->hospital_id)
            || $user->hasRole(User::ROLE_SUPER_ADMIN);
        if (! $allowed) {
            abort(403);
        }
        $emergency->load(['hospital', 'ambulance.driver', 'triageResponse']);
        return response()->json(['emergency' => $this->emergencyResource($emergency, true)]);
    }

    private function emergencyResource(Emergency $e, bool $withTriage = false): array
    {
        $data = [
            'id' => $e->id,
            'status' => $e->status,
            'latitude' => $e->latitude,
            'longitude' => $e->longitude,
            'address_text' => $e->address_text,
            'severity_score' => $e->severity_score,
            'severity_label' => $e->severity_label,
            'severity_category' => $e->severity_category,
            'eta_minutes' => $e->eta_minutes,
            'requested_at' => $e->requested_at?->toIso8601String(),
            'assigned_at' => $e->assigned_at?->toIso8601String(),
            'enroute_at' => $e->enroute_at?->toIso8601String(),
            'arrived_at' => $e->arrived_at?->toIso8601String(),
            'closed_at' => $e->closed_at?->toIso8601String(),
            'hospital' => $e->hospital ? ['id' => $e->hospital->id, 'name' => $e->hospital->name, 'slug' => $e->hospital->slug] : null,
            'ambulance' => null,
        ];
        if ($e->relationLoaded('ambulance') && $e->ambulance) {
            $data['ambulance'] = [
                'id' => $e->ambulance->id,
                'plate_number' => $e->ambulance->plate_number,
                'driver' => $e->ambulance->driver ? ['id' => $e->ambulance->driver->id, 'name' => $e->ambulance->driver->name, 'phone' => $e->ambulance->driver->phone] : null,
            ];
        }
        if ($withTriage && $e->relationLoaded('triageResponse') && $e->triageResponse) {
            $data['triage_responses'] = $e->triageResponse->responses;
        }
        return $data;
    }
}
