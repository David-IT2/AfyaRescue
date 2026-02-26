<?php

namespace App\Services;

use App\Models\Emergency;
use App\Models\EmergencyEventLog;
use Illuminate\Support\Facades\Auth;

class EmergencyLogService
{
    public const EVENT_CREATED = 'created';
    public const EVENT_ASSIGNED = 'assigned';
    public const EVENT_ENROUTE = 'enroute';
    public const EVENT_ARRIVED = 'arrived';
    public const EVENT_CLOSED = 'closed';

    public function log(Emergency $emergency, string $eventType, array $payload = []): EmergencyEventLog
    {
        return EmergencyEventLog::create([
            'emergency_id' => $emergency->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'user_id' => Auth::id(),
        ]);
    }
}
