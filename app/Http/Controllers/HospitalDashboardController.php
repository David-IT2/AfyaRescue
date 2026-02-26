<?php

namespace App\Http\Controllers;

use App\Models\Emergency;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HospitalDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $query = Emergency::with(['patient:id,name,phone', 'ambulance:id,plate_number,driver_id', 'ambulance.driver:id,name,phone', 'hospital:id,name,slug']);
        if ($request->user()->role === 'hospital_admin' && $request->user()->hospital_id) {
            $query->where('hospital_id', $request->user()->hospital_id);
        }
        if ($request->user()->role === 'super_admin') {
            $hospitalId = $request->integer('hospital_id');
            if ($hospitalId > 0) {
                $query->where('hospital_id', $hospitalId);
            }
        }
        $emergencies = $query->orderByRaw("CASE status WHEN 'requested' THEN 1 WHEN 'assigned' THEN 2 WHEN 'enroute' THEN 3 WHEN 'arrived' THEN 4 WHEN 'closed' THEN 5 ELSE 6 END")
            ->orderByDesc('severity_score')
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();
        $hospitals = $request->user()->role === 'super_admin'
            ? \App\Models\Hospital::where('is_active', true)->orderBy('name')->get(['id', 'name'])
            : null;

        return view('hospital.dashboard', [
            'emergencies' => $emergencies,
            'hospitalId' => $request->user()->hospital_id,
            'hospitals' => $hospitals,
            'isSuperAdmin' => $request->user()->role === 'super_admin',
        ]);
    }
}
