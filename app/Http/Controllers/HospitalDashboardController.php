<?php

namespace App\Http\Controllers;

use App\Models\Emergency;
use App\Models\Hospital;
use App\Services\EmergencyAnalyticsService;
use App\Services\EmergencyReportingService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HospitalDashboardController extends Controller
{
    public function __construct(
        protected EmergencyReportingService $reporting,
        protected EmergencyAnalyticsService $analytics
    ) {}

    public function index(Request $request): View|StreamedResponse
    {
        $user = $request->user();
        $hospitalIdScope = $user->role === 'hospital_admin' ? $user->hospital_id : null;

        $filters = [
            'patient' => $request->query('patient'),
            'status' => $request->query('status'),
            'hospital_id' => $user->role === 'super_admin' ? $request->integer('hospital_id') : null,
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ];
        if ($user->role === 'super_admin' && $request->integer('hospital_id') > 0) {
            $hospitalIdScope = $request->integer('hospital_id');
        }

        $query = $this->reporting->filteredQuery($filters, $hospitalIdScope)
            ->with(['patient:id,name,phone', 'ambulance:id,plate_number,driver_id', 'ambulance.driver:id,name,phone', 'hospital:id,name,slug']);
        $emergencies = $query
            ->orderByDesc('severity_score')
            ->orderByRaw("CASE status WHEN 'requested' THEN 1 WHEN 'assigned' THEN 2 WHEN 'enroute' THEN 3 WHEN 'arrived' THEN 4 WHEN 'closed' THEN 5 ELSE 6 END")
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $hospitals = $user->role === 'super_admin'
            ? Hospital::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : null;

        $stats = [
            'avg_assignment_min' => $this->analytics->averageAssignmentTimeMinutes($hospitalIdScope),
            'avg_enroute_min' => $this->analytics->averageEnrouteTimeMinutes($hospitalIdScope),
            'by_severity' => $this->analytics->countBySeverityCategory($hospitalIdScope),
            'utilization' => $this->analytics->ambulanceUtilization($hospitalIdScope),
        ];

        return view('hospital.dashboard', [
            'emergencies' => $emergencies,
            'hospitalId' => $user->hospital_id,
            'hospitals' => $hospitals,
            'isSuperAdmin' => $user->role === 'super_admin',
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $hospitalIdScope = $user->role === 'hospital_admin' ? $user->hospital_id : null;
        if ($user->role === 'super_admin' && $request->integer('hospital_id') > 0) {
            $hospitalIdScope = $request->integer('hospital_id');
        }
        $filters = [
            'patient' => $request->query('patient'),
            'status' => $request->query('status'),
            'hospital_id' => $user->role === 'super_admin' ? $request->query('hospital_id') : null,
            'from' => $request->query('from'),
            'to' => $request->query('to'),
        ];
        return $this->reporting->exportCsv($filters, $hospitalIdScope);
    }
}
