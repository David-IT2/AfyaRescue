<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Emergency;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function index(Request $request)
    {
        // --- Date range ---
        $range = $request->get('range', '7');
        $from  = $request->get('from');
        $to    = $request->get('to');

        if ($range === 'custom' && $from && $to) {
            $start = \Carbon\Carbon::parse($from)->startOfDay();
            $end   = \Carbon\Carbon::parse($to)->endOfDay();
        } elseif ($range === '0') {
            $start = now()->startOfDay();
            $end   = now()->endOfDay();
        } elseif ($range === '30') {
            $start = now()->subDays(30)->startOfDay();
            $end   = now()->endOfDay();
        } else {
            // default 7 days
            $start = now()->subDays(7)->startOfDay();
            $end   = now()->endOfDay();
        }

        // --- Emergency stats ---
        $emergencyQuery = Emergency::whereBetween('requested_at', [$start, $end]);

        $totalEmergencies  = (clone $emergencyQuery)->count();
        $activeEmergencies = (clone $emergencyQuery)->whereIn('status', [
            Emergency::STATUS_REQUESTED,
            Emergency::STATUS_ASSIGNED,
            Emergency::STATUS_ENROUTE,
            Emergency::STATUS_ARRIVED,
        ])->count();
        $closedEmergencies = (clone $emergencyQuery)->where('status', Emergency::STATUS_CLOSED)->count();

        $byStatus = (clone $emergencyQuery)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $bySeverity = (clone $emergencyQuery)
            ->select('severity_category', DB::raw('count(*) as total'))
            ->groupBy('severity_category')
            ->pluck('total', 'severity_category')
            ->toArray();

        // --- Avg response times ---
        $avgAssignmentMin = (clone $emergencyQuery)
            ->whereNotNull('assigned_at')
            ->whereNotNull('requested_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, requested_at, assigned_at)) as avg_min'))
            ->value('avg_min');

        $avgEnrouteMin = (clone $emergencyQuery)
            ->whereNotNull('enroute_at')
            ->whereNotNull('assigned_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, assigned_at, enroute_at)) as avg_min'))
            ->value('avg_min');

        $avgArrivalMin = (clone $emergencyQuery)
            ->whereNotNull('arrived_at')
            ->whereNotNull('enroute_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(MINUTE, enroute_at, arrived_at)) as avg_min'))
            ->value('avg_min');

        // --- Hospitals & Ambulances ---
        $totalHospitals        = Hospital::count();
        $activeHospitals       = Hospital::where('is_active', true)->count();
        $totalAmbulances       = Ambulance::count();
        $availableAmbulances   = Ambulance::where('status', Ambulance::STATUS_AVAILABLE)->count();
        $busyAmbulances        = Ambulance::where('status', Ambulance::STATUS_BUSY)->count();
        $maintenanceAmbulances = Ambulance::where('status', Ambulance::STATUS_MAINTENANCE)->count();

        // --- Users by role ---
        $usersByRole = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        // --- Map data: emergencies with coordinates in range ---
        $mapEmergencies = (clone $emergencyQuery)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->with('hospital:id,name', 'patient:id,name')
            ->select('id', 'latitude', 'longitude', 'status', 'severity_category', 'address_text', 'patient_id', 'hospital_id', 'requested_at')
            ->get();

        return view('super-admin.metrics', compact(
            'range', 'from', 'to', 'start', 'end',
            'totalEmergencies', 'activeEmergencies', 'closedEmergencies',
            'byStatus', 'bySeverity',
            'avgAssignmentMin', 'avgEnrouteMin', 'avgArrivalMin',
            'totalHospitals', 'activeHospitals',
            'totalAmbulances', 'availableAmbulances', 'busyAmbulances', 'maintenanceAmbulances',
            'usersByRole',
            'mapEmergencies'
        ));
    }
}