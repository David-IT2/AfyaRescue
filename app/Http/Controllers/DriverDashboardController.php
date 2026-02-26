<?php

namespace App\Http\Controllers;

use App\Models\Emergency;
use App\Services\EmergencyFlowService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverDashboardController extends Controller
{
    public function __construct(
        protected EmergencyFlowService $emergencyFlow
    ) {}

    /**
     * List emergencies assigned to the current driver.
     */
    public function index(Request $request): View
    {
        $ambulanceIds = $request->user()->driverAmbulance()->pluck('id');
        $emergencies = Emergency::whereIn('ambulance_id', $ambulanceIds)
            ->with(['patient:id,name,phone', 'hospital:id,name', 'ambulance:id,plate_number'])
            ->whereNotIn('status', [Emergency::STATUS_CLOSED])
            ->orderByRaw("CASE status WHEN 'requested' THEN 1 WHEN 'assigned' THEN 2 WHEN 'enroute' THEN 3 WHEN 'arrived' THEN 4 ELSE 5 END")
            ->orderByDesc('created_at')
            ->get();

        return view('driver.dashboard', ['emergencies' => $emergencies]);
    }

    /**
     * Update emergency status (enroute, arrived, closed).
     */
    public function updateStatus(Request $request, Emergency $emergency)
    {
        $ambulanceIds = $request->user()->driverAmbulance()->pluck('id');
        if (! in_array($emergency->ambulance_id, $ambulanceIds->all())) {
            abort(403, 'This emergency is not assigned to you.');
        }
        $request->validate([
            'status' => ['required', 'string', 'in:enroute,arrived,closed'],
        ]);
        $this->emergencyFlow->updateStatus($emergency, $request->status);
        return redirect()->route('driver.dashboard')->with('success', 'Status updated to ' . $request->status . '.');
    }
}
