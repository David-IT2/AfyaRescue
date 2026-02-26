<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergencyStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $data
    ) {}

    public function broadcastOn(): array
    {
        $hospitalId = $this->data['hospital_id'] ?? null;
        if ($hospitalId) {
            return [new Channel('hospital.' . $hospitalId)];
        }
        return [];
    }

    public function broadcastAs(): string
    {
        return 'emergency.updated';
    }

    public function broadcastWith(): array
    {
        return $this->data;
    }
}
