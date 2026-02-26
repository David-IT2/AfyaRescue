<?php

namespace App\Http\Controllers\Api;

use App\Events\AmbulanceLocationUpdated;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DriverLocationController extends Controller
{
    /**
     * Update current ambulance location (real-time tracking). Broadcasts to hospital channel.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);
        $user = $request->user();
        $ambulance = $user->driverAmbulance()->first();
        if (! $ambulance) {
            return response()->json(['message' => 'No ambulance assigned to you.'], 403);
        }
        $ambulance->update([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'location_updated_at' => now(),
        ]);
        $emergencyId = $ambulance->emergencies()->whereNotIn('status', ['closed'])->value('id');
        if (config('broadcasting.default') !== 'log') {
            event(new AmbulanceLocationUpdated(
                $ambulance->id,
                $ambulance->hospital_id,
                (float) $request->latitude,
                (float) $request->longitude,
                $emergencyId
            ));
        }
        return response()->json(['message' => 'Location updated.', 'ambulance_id' => $ambulance->id]);
    }
}
