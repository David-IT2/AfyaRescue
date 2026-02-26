<?php

namespace App\Services;

use App\Models\Emergency;
use Illuminate\Support\Facades\DB;

/**
 * Handles emergency creation, assignment, and status transitions.
 * Uses TriageService for severity; logs all events via EmergencyLogService.
 */
class EmergencyFlowService
{
    public function __construct(
        protected TriageService $triageService,
        protected AmbulanceAssignmentService $ambulanceAssignment,
        protected NotificationService $notification,
        protected EmergencyLogService $emergencyLog
    ) {}

    public function createEmergency(int $patientId, int $hospitalId, float $latitude, float $longitude, ?string $addressText, array $triageResponses): Emergency
    {
        $normalized = $this->normalizeTriageResponses($triageResponses);
        $result = $this->triageService->evaluate($normalized);

        $emergency = DB::transaction(function () use ($patientId, $hospitalId, $latitude, $longitude, $addressText, $normalized, $result) {
            $emergency = Emergency::create([
                'patient_id' => $patientId,
                'hospital_id' => $hospitalId,
                'status' => Emergency::STATUS_REQUESTED,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address_text' => $addressText,
                'severity_score' => $result['legacy_score'],
                'severity_label' => $result['legacy_label'],
                'severity_category' => $result['category'],
                'requested_at' => now(),
            ]);

            $emergency->triageResponse()->create([
                'responses' => $normalized,
                'calculated_score' => $result['weighted_score'],
            ]);

            $this->emergencyLog->log($emergency, EmergencyLogService::EVENT_CREATED, [
                'severity_category' => $result['category'],
                'weighted_score' => $result['weighted_score'],
            ]);

            $ambulance = $this->ambulanceAssignment->findNearestAvailableAmbulance($emergency);
            if ($ambulance) {
                $this->ambulanceAssignment->assignAmbulanceToEmergency($emergency, $ambulance);
                $emergency->refresh();
                $this->emergencyLog->log($emergency, EmergencyLogService::EVENT_ASSIGNED, [
                    'ambulance_id' => $ambulance->id,
                    'eta_minutes' => $emergency->eta_minutes,
                ]);
            }

            return $emergency;
        });

        $this->notification->notifyNewEmergency($emergency);
        return $emergency;
    }

    public function updateStatus(Emergency $emergency, string $newStatus): Emergency
    {
        $allowed = [
            Emergency::STATUS_ASSIGNED => [Emergency::STATUS_REQUESTED],
            Emergency::STATUS_ENROUTE => [Emergency::STATUS_ASSIGNED],
            Emergency::STATUS_ARRIVED => [Emergency::STATUS_ENROUTE],
            Emergency::STATUS_CLOSED => [Emergency::STATUS_ARRIVED, Emergency::STATUS_ENROUTE],
        ];

        if (! isset($allowed[$newStatus]) || ! in_array($emergency->status, $allowed[$newStatus], true)) {
            throw new \InvalidArgumentException("Invalid status transition from {$emergency->status} to {$newStatus}");
        }

        $payload = ['status' => $newStatus];
        $timestampColumn = match ($newStatus) {
            Emergency::STATUS_ASSIGNED => 'assigned_at',
            Emergency::STATUS_ENROUTE => 'enroute_at',
            Emergency::STATUS_ARRIVED => 'arrived_at',
            Emergency::STATUS_CLOSED => 'closed_at',
            default => null,
        };
        if ($timestampColumn) {
            $payload[$timestampColumn] = now();
        }

        if ($newStatus === Emergency::STATUS_CLOSED && $emergency->ambulance_id) {
            $emergency->ambulance->update(['status' => \App\Models\Ambulance::STATUS_AVAILABLE]);
        }

        $emergency->update($payload);
        $eventType = match ($newStatus) {
            Emergency::STATUS_ENROUTE => EmergencyLogService::EVENT_ENROUTE,
            Emergency::STATUS_ARRIVED => EmergencyLogService::EVENT_ARRIVED,
            Emergency::STATUS_CLOSED => EmergencyLogService::EVENT_CLOSED,
            default => null,
        };
        if ($eventType) {
            $this->emergencyLog->log($emergency, $eventType);
        }
        $this->notification->notifyEmergencyStatusUpdate($emergency);
        return $emergency->fresh();
    }

    private function normalizeTriageResponses(array $raw): array
    {
        $out = [];
        foreach ($raw as $k => $v) {
            if ($k === 'conscious') {
                $out[$k] = filter_var($v, FILTER_VALIDATE_BOOLEAN);
            } elseif (in_array($k, ['chest_pain', 'stroke_symptoms', 'pregnancy_emergency', 'allergic_reaction'], true)) {
                $out[$k] = filter_var($v, FILTER_VALIDATE_BOOLEAN);
            } elseif ($k === 'number_of_casualties') {
                $out[$k] = (int) $v;
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }
}
