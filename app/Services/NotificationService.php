<?php

namespace App\Services;

use App\Models\Emergency;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends notifications to driver and hospital (log, broadcast, optional email for critical).
 */
class NotificationService
{
    public function notifyNewEmergency(Emergency $emergency): void
    {
        $emergency->load(['patient', 'hospital', 'ambulance.driver']);
        Log::channel('stack')->info('AfyaRescue: New emergency', [
            'emergency_id' => $emergency->id,
            'hospital_id' => $emergency->hospital_id,
            'severity' => $emergency->severity_category ?? $emergency->severity_label,
            'ambulance_id' => $emergency->ambulance_id,
            'driver_id' => $emergency->ambulance?->driver_id,
        ]);

        $this->broadcastIfConfiguredNew([
            'emergency_id' => $emergency->id,
            'hospital_id' => $emergency->hospital_id,
            'severity_score' => $emergency->severity_score,
            'severity_label' => $emergency->severity_label,
            'severity_category' => $emergency->severity_category,
            'status' => $emergency->status,
        ]);

        if (($emergency->severity_category ?? '') === \App\Services\TriageService::CATEGORY_CRITICAL) {
            $this->notifyCriticalEmergency($emergency);
        }
    }

    /** Optional: email critical alert when MAIL_* is configured. */
    protected function notifyCriticalEmergency(Emergency $emergency): void
    {
        if (config('mail.default') === 'log') {
            Log::channel('stack')->warning('AfyaRescue: CRITICAL emergency', [
                'emergency_id' => $emergency->id,
                'hospital_id' => $emergency->hospital_id,
            ]);
            return;
        }
        $to = config('services.afyarescue.critical_alert_email');
        if (! $to) {
            return;
        }
        try {
            Mail::raw(
                "Critical emergency #{$emergency->id} at hospital {$emergency->hospital->name}. Severity: " . ($emergency->severity_category ?? $emergency->severity_label) . ". Check dashboard.",
                fn ($m) => $m->to($to)->subject('AfyaRescue: Critical Emergency #' . $emergency->id)
            );
        } catch (\Throwable $e) {
            Log::debug('Critical email skipped: ' . $e->getMessage());
        }
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
