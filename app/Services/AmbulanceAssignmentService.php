<?php

namespace App\Services;

use App\Models\Ambulance;
use App\Models\Emergency;
use App\Models\Hospital;

/**
 * Optimized ambulance assignment: nearest by Haversine distance, optionally by hospital level.
 * Updates ambulance status (Available → Assigned) and sets ETA on emergency for analytics.
 */
class AmbulanceAssignmentService
{
    /** Average speed km/h for ETA calculation. */
    private const AVG_SPEED_KMH = 40;

    /**
     * Find nearest available ambulance for the emergency's hospital.
     * Prefers ambulances from same or higher hospital level when emergency is Critical.
     */
    public function findNearestAvailableAmbulance(Emergency $emergency): ?Ambulance
    {
        $hospital = $emergency->hospital;
        $ambulances = $hospital->ambulances()
            ->where('status', Ambulance::STATUS_AVAILABLE)
            ->whereNotNull('driver_id')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('hospital:id,level')
            ->get();

        if ($ambulances->isEmpty()) {
            return null;
        }

        $emergencyLat = (float) $emergency->latitude;
        $emergencyLng = (float) $emergency->longitude;
        $isCritical = ($emergency->severity_category ?? '') === \App\Services\TriageService::CATEGORY_CRITICAL;
        $hospitalLevel = (int) $hospital->level;

        $scored = $ambulances->map(function (Ambulance $a) use ($emergencyLat, $emergencyLng, $isCritical, $hospitalLevel) {
            $distanceKm = $this->haversineDistanceKm(
                $emergencyLat,
                $emergencyLng,
                (float) $a->latitude,
                (float) $a->longitude
            );
            $levelBonus = 0;
            if ($isCritical && isset($a->hospital->level)) {
                $levelBonus = ($a->hospital->level <= $hospitalLevel) ? 0 : -10;
            }
            return ['ambulance' => $a, 'distance_km' => $distanceKm, 'sort_key' => $distanceKm + $levelBonus];
        });

        $nearest = $scored->sortBy('sort_key')->first();
        return $nearest['ambulance'];
    }

    public function haversineDistanceKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadiusKm * $c, 2);
    }

    /** ETA in minutes from distance (km) and average speed. */
    public function etaMinutesFromDistanceKm(float $distanceKm): int
    {
        if ($distanceKm <= 0) {
            return 0;
        }
        return (int) ceil(($distanceKm / self::AVG_SPEED_KMH) * 60);
    }

    /**
     * Assign ambulance to emergency: update emergency + ambulance status, set ETA.
     */
    public function assignAmbulanceToEmergency(Emergency $emergency, Ambulance $ambulance): void
    {
        $distanceKm = $this->haversineDistanceKm(
            (float) $emergency->latitude,
            (float) $emergency->longitude,
            (float) $ambulance->latitude,
            (float) $ambulance->longitude
        );
        $etaMinutes = $this->etaMinutesFromDistanceKm($distanceKm);

        $emergency->update([
            'ambulance_id' => $ambulance->id,
            'status' => Emergency::STATUS_ASSIGNED,
            'assigned_at' => now(),
            'eta_minutes' => $etaMinutes,
        ]);
        $ambulance->update([
            'status' => Ambulance::STATUS_BUSY,
        ]);
    }
}
