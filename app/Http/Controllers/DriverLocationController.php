<?php

namespace App\Http\Controllers;

use App\Models\Ambulance;
use Illuminate\Http\Request;

class DriverLocationController extends Controller
{
    /**
     * Receive a GPS ping from the driver's browser and update ambulance location.
     */
    public function update(Request $request, Ambulance $ambulance)
    {
        $user = $request->user();

        // Only the assigned driver can update this ambulance's location
        if ($ambulance->driver_id !== $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'latitude'  => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $ambulance->update([
            'latitude'             => $validated['latitude'],
            'longitude'            => $validated['longitude'],
            'location_updated_at'  => now(),
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Return the current location of an ambulance (polled by patient page).
     */
    public function show(Request $request, Ambulance $ambulance)
    {
        return response()->json([
            'latitude'            => $ambulance->latitude,
            'longitude'           => $ambulance->longitude,
            'location_updated_at' => $ambulance->location_updated_at?->toIso8601String(),
        ]);
    }
}