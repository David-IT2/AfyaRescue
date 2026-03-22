<?php

namespace App\Http\Controllers;

use App\Models\Ambulance;
use App\Models\Emergency;
use Illuminate\Http\Request;

class AmbulanceAssignmentController extends Controller
{
    /**
     * Show the assignment form for a given emergency.
     * Accessible by hospital_admin (own hospital) and super_admin.
     */
    public function create(Request $request, Emergency $emergency)
    {
        $user = $request->user();

        if ($user->hasRole('hospital_admin')) {
            if ($emergency->hospital_id !== $user->hospital_id) {
                abort(403);
            }
            $ambulances = Ambulance::where('hospital_id', $user->hospital_id)
                ->where('status', Ambulance::STATUS_AVAILABLE)
                ->with('driver:id,name,phone')
                ->get();
        } else {
            // super_admin sees all available ambulances for the emergency's hospital
            $ambulances = Ambulance::where('hospital_id', $emergency->hospital_id)
                ->where('status', Ambulance::STATUS_AVAILABLE)
                ->with('driver:id,name,phone')
                ->get();
        }

        $emergency->load(['patient:id,name,phone', 'hospital:id,name', 'ambulance.driver:id,name,phone']);

        return view('emergency.assign', compact('emergency', 'ambulances'));
    }

    /**
     * Assign the ambulance to the emergency.
     */
    public function store(Request $request, Emergency $emergency)
    {
        $user = $request->user();

        if ($user->hasRole('hospital_admin') && $emergency->hospital_id !== $user->hospital_id) {
            abort(403);
        }

        $validated = $request->validate([
            'ambulance_id' => ['required', 'integer', 'exists:ambulances,id'],
            'eta_minutes'  => ['nullable', 'integer', 'min:1', 'max:300'],
        ]);

        $ambulance = Ambulance::where('id', $validated['ambulance_id'])
            ->where('hospital_id', $emergency->hospital_id)
            ->firstOrFail();

        // Update emergency
        $emergency->update([
            'ambulance_id' => $ambulance->id,
            'status'       => Emergency::STATUS_ASSIGNED,
            'assigned_at'  => now(),
            'eta_minutes'  => $validated['eta_minutes'] ?? null,
        ]);

        // Mark ambulance busy
        $ambulance->update(['status' => Ambulance::STATUS_BUSY]);

        return redirect()
            ->back()
            ->with('success', "Ambulance {$ambulance->plate_number} assigned to Emergency #{$emergency->id}.");
    }
}