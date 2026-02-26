<?php

namespace App\Services;

use App\Models\Emergency;
use Illuminate\Support\Collection;

class EmergencyAnalyticsService
{
    /**
     * Average response time in minutes (requested_at to assigned_at).
     */
    public function averageAssignmentTimeMinutes(?int $hospitalId = null): ?float
    {
        $q = Emergency::whereNotNull('assigned_at')->whereNotNull('requested_at');
        if ($hospitalId) {
            $q->where('hospital_id', $hospitalId);
        }
        $count = $q->count();
        if ($count === 0) {
            return null;
        }
        $total = $q->get()->sum(function (Emergency $e) {
            return $e->requested_at->diffInMinutes($e->assigned_at);
        });
        return round($total / $count, 1);
    }

    /**
     * Average time from assigned to arrived (enroute time).
     */
    public function averageEnrouteTimeMinutes(?int $hospitalId = null): ?float
    {
        $q = Emergency::whereNotNull('assigned_at')->whereNotNull('arrived_at');
        if ($hospitalId) {
            $q->where('hospital_id', $hospitalId);
        }
        $count = $q->count();
        if ($count === 0) {
            return null;
        }
        $total = $q->get()->sum(function (Emergency $e) {
            return $e->assigned_at->diffInMinutes($e->arrived_at);
        });
        return round($total / $count, 1);
    }

    /**
     * Count emergencies by severity category.
     */
    public function countBySeverityCategory(?int $hospitalId = null, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): Collection
    {
        $q = Emergency::query();
        if ($hospitalId) {
            $q->where('hospital_id', $hospitalId);
        }
        if ($from) {
            $q->where('created_at', '>=', $from);
        }
        if ($to) {
            $q->where('created_at', '<=', $to);
        }
        return $q->get()->groupBy('severity_category')->map->count();
    }

    /**
     * Ambulance utilization: count of distinct ambulances used in period vs total ambulances.
     */
    public function ambulanceUtilization(?int $hospitalId = null, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): array
    {
        $q = Emergency::whereNotNull('ambulance_id');
        if ($hospitalId) {
            $q->where('hospital_id', $hospitalId);
        }
        if ($from) {
            $q->where('created_at', '>=', $from);
        }
        if ($to) {
            $q->where('created_at', '<=', $to);
        }
        $usedAmbulanceIds = $q->distinct()->pluck('ambulance_id');
        $totalAmbulances = \App\Models\Ambulance::when($hospitalId, fn ($q) => $q->where('hospital_id', $hospitalId))->count();
        return [
            'ambulances_used' => $usedAmbulanceIds->count(),
            'total_ambulances' => $totalAmbulances,
            'utilization_pct' => $totalAmbulances > 0 ? round($usedAmbulanceIds->count() / $totalAmbulances * 100, 1) : 0,
        ];
    }
}
