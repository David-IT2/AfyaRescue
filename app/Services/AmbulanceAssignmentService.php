<?php

namespace App\Services;

use App\Models\Ambulance;
use App\Models\Emergency;
use App\Models\Hospital;
use Illuminate\Support\Collection;

/**
 * Assigns the nearest available ambulance (by distance) to an emergency.
 */
class AmbulanceAssignmentService
{
    public function __construct(
        protected TriageScoringService $triageScoring
    ) {}

    /**
     * Find nearest available ambulance for the hospital serving this emergency.
     * Uses Haversine-style distance for simplicity (approximate km).
     */
    public function findNearestAvailableAmbulance(Emergency $emergency): ?Ambulance
    {
        $hospital = $emergency->hospital;
        $ambulances = $hospital->ambulances()
            ->where('status', Ambulance::STATUS_AVAILABLE)
            ->whereNotNull('driver_id')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($ambulances->isEmpty()) {
            return null;
        }

        $emergencyLat = (float) $emergency->latitude;
        $emergencyLng = (float) $emergency->longitude;

        $nearest = $ambulances->sortBy(function (Ambulance $a) use ($emergencyLat, $emergencyLng) {
            return $this->haversineDistanceKm(
                $emergencyLat,
                $emergencyLng,
                (float) $a->latitude,
                (float) $a->longitude
            );
        })->first();

        return $nearest;
    }

    /**
     * Approximate distance in km between two points (Haversine).
     */
    public function haversineDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadiusKm * $c;
    }

    /**
     * Assign the given ambulance to the emergency and mark ambulance as busy.
     */
    public function assignAmbulanceToEmergency(Emergency $emergency, Ambulance $ambulance): void
    {
        $emergency->update([
            'ambulance_id' => $ambulance->id,
            'status' => Emergency::STATUS_ASSIGNED,
            'assigned_at' => now(),
        ]);
        $ambulance->update([
            'status' => Ambulance::STATUS_BUSY,
        ]);
    }
}
