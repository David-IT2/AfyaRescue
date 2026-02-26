<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AmbulanceLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $ambulanceId,
        public int $hospitalId,
        public float $latitude,
        public float $longitude,
        public ?int $emergencyId = null
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('hospital.' . $this->hospitalId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ambulance.location';
    }

    public function broadcastWith(): array
    {
        return [
            'ambulance_id' => $this->ambulanceId,
            'hospital_id' => $this->hospitalId,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'emergency_id' => $this->emergencyId,
        ];
    }
}
