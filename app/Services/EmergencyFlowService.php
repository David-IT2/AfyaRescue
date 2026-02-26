<?php

namespace App\Services;

use App\Models\Emergency;
use Illuminate\Support\Facades\DB;

/**
 * Handles emergency creation, assignment, and status transitions.
 */
class EmergencyFlowService
{
    public function __construct(
        protected TriageScoringService $triageScoring,
        protected AmbulanceAssignmentService $ambulanceAssignment,
        protected NotificationService $notification
    ) {}

    /**
     * Create emergency with triage, compute severity, assign nearest ambulance, notify.
     */
    public function createEmergency(int $patientId, int $hospitalId, float $latitude, float $longitude, ?string $addressText, array $triageResponses): Emergency
    {
        $score = $this->triageScoring->calculateScore($triageResponses);
        $label = $this->triageScoring->scoreToLabel($score);

        $emergency = DB::transaction(function () use ($patientId, $hospitalId, $latitude, $longitude, $addressText, $triageResponses, $score, $label) {
            $emergency = Emergency::create([
                'patient_id' => $patientId,
                'hospital_id' => $hospitalId,
                'status' => Emergency::STATUS_REQUESTED,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'address_text' => $addressText,
                'severity_score' => $score,
                'severity_label' => $label,
                'requested_at' => now(),
            ]);

            $emergency->triageResponse()->create([
                'responses' => $triageResponses,
                'calculated_score' => $score,
            ]);

            $ambulance = $this->ambulanceAssignment->findNearestAvailableAmbulance($emergency);
            if ($ambulance) {
                $this->ambulanceAssignment->assignAmbulanceToEmergency($emergency, $ambulance);
                $emergency->refresh();
            }

            return $emergency;
        });

        $this->notification->notifyNewEmergency($emergency);
        return $emergency;
    }

    /**
     * Transition status (driver/hospital): assigned -> enroute -> arrived -> closed.
     */
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
        $this->notification->notifyEmergencyStatusUpdate($emergency);
        return $emergency->fresh();
    }
}
