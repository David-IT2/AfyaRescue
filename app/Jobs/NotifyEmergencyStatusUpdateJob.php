<?php

namespace App\Jobs;

use App\Models\Emergency;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyEmergencyStatusUpdateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $emergencyId
    ) {}

    public function handle(NotificationService $notification): void
    {
        $emergency = Emergency::find($this->emergencyId);
        if ($emergency) {
            $notification->notifyEmergencyStatusUpdate($emergency);
        }
    }
}
