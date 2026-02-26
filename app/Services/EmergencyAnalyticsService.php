<?php

namespace App\Services;

use App\Models\Emergency;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EmergencyAnalyticsService
{
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
        $total = $q->get()->sum(fn (Emergency $e) => $e->requested_at->diffInMinutes($e->assigned_at));
        return round($total / $count, 1);
    }

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
        $total = $q->get()->sum(fn (Emergency $e) => $e->assigned_at->diffInMinutes($e->arrived_at));
        return round($total / $count, 1);
    }

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

    /** Emergencies over time: daily, weekly, or monthly buckets. */
    public function trends(?int $hospitalId = null, string $period = 'daily', int $days = 30): array
    {
        $from = Carbon::now()->subDays($days);
        $q = Emergency::where('created_at', '>=', $from);
        if ($hospitalId) {
            $q->where('hospital_id', $hospitalId);
        }
        $items = $q->get(['id', 'created_at', 'severity_category']);
        $grouped = match ($period) {
            'weekly' => $items->groupBy(fn ($e) => $e->created_at->startOfWeek()->format('Y-m-d')),
            'monthly' => $items->groupBy(fn ($e) => $e->created_at->format('Y-m')),
            default => $items->groupBy(fn ($e) => $e->created_at->format('Y-m-d')),
        };
        return $grouped->map(fn ($group) => [
            'total' => $group->count(),
            'critical' => $group->where('severity_category', \App\Services\TriageService::CATEGORY_CRITICAL)->count(),
            'moderate' => $group->where('severity_category', \App\Services\TriageService::CATEGORY_MODERATE)->count(),
            'mild' => $group->where('severity_category', \App\Services\TriageService::CATEGORY_MILD)->count(),
        ])->sortKeys()->all();
    }
}
