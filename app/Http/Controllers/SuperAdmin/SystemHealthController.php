<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Emergency;
use App\Models\Hospital;
use App\Services\EmergencyAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SystemHealthController extends Controller
{
    public function __construct(
        protected EmergencyAnalyticsService $analytics
    ) {}

    /**
     * System health dashboard: active emergencies, ambulance availability, stats.
     */
    public function index(Request $request): View
    {
        $activeCount = Emergency::whereNotIn('status', ['closed'])->count();
        $totalAmbulances = Ambulance::count();
        $availableAmbulances = Ambulance::whereDoesntHave('emergencies', function ($q) {
            $q->whereNotIn('status', ['closed']);
        })->count();
        $busyAmbulances = $totalAmbulances - $availableAmbulances;

        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get()->map(function (Hospital $h) {
            $active = Emergency::where('hospital_id', $h->id)->whereNotIn('status', ['closed'])->count();
            $ambulances = Ambulance::where('hospital_id', $h->id)->count();
            return (object) [
                'id' => $h->id,
                'name' => $h->name,
                'active_emergencies' => $active,
                'ambulance_count' => $ambulances,
            ];
        });

        $stats = [
            'avg_assignment_min' => $this->analytics->averageAssignmentTimeMinutes(null),
            'avg_enroute_min' => $this->analytics->averageEnrouteTimeMinutes(null),
            'by_severity' => $this->analytics->countBySeverityCategory(null),
            'utilization' => $this->analytics->ambulanceUtilization(null),
        ];

        return view('super-admin.system-health', [
            'active_emergencies' => $activeCount,
            'total_ambulances' => $totalAmbulances,
            'available_ambulances' => $availableAmbulances,
            'busy_ambulances' => $busyAmbulances,
            'hospitals' => $hospitals,
            'stats' => $stats,
        ]);
    }
}
