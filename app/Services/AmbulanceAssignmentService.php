<?php

namespace App\Services;

use App\Models\Ambulance;
use App\Models\AssignmentLog;
use App\Models\Emergency;
use App\Models\Hospital;

/**
 * Advanced matching: distance (Haversine), hospital level, ambulance type, driver skill.
 * Critical emergencies prefer ICU/advanced ambulances and critical_care drivers.
 * Logs every assignment for performance analysis.
 */
class AmbulanceAssignmentService
{
    private const AVG_SPEED_KMH = 40;

    public function findNearestAvailableAmbulance(Emergency $emergency): ?Ambulance
    {
        $hospital = $emergency->hospital;
        $ambulances = $hospital->ambulances()
            ->where('status', Ambulance::STATUS_AVAILABLE)
            ->whereNotNull('driver_id')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with(['hospital:id,level', 'driver:id,driver_skill'])
            ->get();

        if ($ambulances->isEmpty()) {
            return null;
        }

        $emergencyLat = (float) $emergency->latitude;
        $emergencyLng = (float) $emergency->longitude;
        $isCritical = ($emergency->severity_category ?? '') === TriageService::CATEGORY_CRITICAL;
        $hospitalLevel = (int) $hospital->level;

        $scored = $ambulances->map(function (Ambulance $a) use ($emergencyLat, $emergencyLng, $isCritical, $hospitalLevel) {
            $distanceKm = $this->haversineDistanceKm(
                $emergencyLat,
                $emergencyLng,
                (float) $a->latitude,
                (float) $a->longitude
            );
            $sortKey = $distanceKm;
            if ($isCritical) {
                $typeScore = match ($a->type ?? 'basic') {
                    'icu' => -5,
                    'advanced' => -2,
                    default => 0,
                };
                $skillScore = match ($a->driver->driver_skill ?? 'basic') {
                    'critical_care' => -3,
                    'advanced' => -1,
                    default => 0,
                };
                $sortKey += $typeScore + $skillScore;
            }
            return ['ambulance' => $a, 'distance_km' => $distanceKm, 'sort_key' => $sortKey];
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

    public function etaMinutesFromDistanceKm(float $distanceKm): int
    {
        if ($distanceKm <= 0) {
            return 0;
        }
        return (int) ceil(($distanceKm / self::AVG_SPEED_KMH) * 60);
    }

    public function assignAmbulanceToEmergency(Emergency $emergency, Ambulance $ambulance): void
    {
        $distanceKm = $this->haversineDistanceKm(
            (float) $emergency->latitude,
            (float) $emergency->longitude,
            (float) $ambulance->latitude,
            (float) $ambulance->longitude
        );
        $etaMinutes = $this->etaMinutesFromDistanceKm($distanceKm);
        $reason = ($emergency->severity_category ?? '') === TriageService::CATEGORY_CRITICAL ? 'critical_match' : 'distance';

        $emergency->update([
            'ambulance_id' => $ambulance->id,
            'status' => Emergency::STATUS_ASSIGNED,
            'assigned_at' => now(),
            'eta_minutes' => $etaMinutes,
        ]);
        $ambulance->update([
            'status' => Ambulance::STATUS_BUSY,
        ]);

        AssignmentLog::create([
            'emergency_id' => $emergency->id,
            'ambulance_id' => $ambulance->id,
            'distance_km' => $distanceKm,
            'eta_minutes' => $etaMinutes,
            'assignment_reason' => $reason,
            'assigned_at' => now(),
        ]);
    }
}
