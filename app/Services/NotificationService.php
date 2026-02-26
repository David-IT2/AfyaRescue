<?php

namespace App\Services;

use App\Models\Emergency;
use Illuminate\Support\Facades\Log;

/**
 * Sends notifications to driver and hospital (log for MVP; can add Pusher/mail/SMS).
 */
class NotificationService
{
    public function notifyNewEmergency(Emergency $emergency): void
    {
        $emergency->load(['patient', 'hospital', 'ambulance.driver']);
        Log::channel('stack')->info('AfyaRescue: New emergency', [
            'emergency_id' => $emergency->id,
            'hospital_id' => $emergency->hospital_id,
            'severity' => $emergency->severity_label,
            'ambulance_id' => $emergency->ambulance_id,
            'driver_id' => $emergency->ambulance?->driver_id,
        ]);

        $this->broadcastIfConfiguredNew([
            'emergency_id' => $emergency->id,
            'hospital_id' => $emergency->hospital_id,
            'severity_score' => $emergency->severity_score,
            'severity_label' => $emergency->severity_label,
            'status' => $emergency->status,
        ]);
    }

    public function notifyEmergencyStatusUpdate(Emergency $emergency): void
    {
        Log::channel('stack')->info('AfyaRescue: Emergency status updated', [
            'emergency_id' => $emergency->id,
            'status' => $emergency->status,
        ]);

        $this->broadcastIfConfiguredUpdate([
            'emergency_id' => $emergency->id,
            'hospital_id' => $emergency->hospital_id,
            'status' => $emergency->status,
        ]);
    }

    protected function broadcastIfConfiguredNew(array $data): void
    {
        if (config('broadcasting.default') !== 'log') {
            try {
                event(new \App\Events\EmergencyStatusChanged(array_merge($data, ['event' => 'new'])));
            } catch (\Throwable $e) {
                Log::debug('Broadcast skipped: ' . $e->getMessage());
            }
        }
    }

    protected function broadcastIfConfiguredUpdate(array $data): void
    {
        if (config('broadcasting.default') !== 'log') {
            try {
                event(new \App\Events\EmergencyStatusChanged(array_merge($data, ['event' => 'updated'])));
            } catch (\Throwable $e) {
                Log::debug('Broadcast skipped: ' . $e->getMessage());
            }
        }
    }
}
