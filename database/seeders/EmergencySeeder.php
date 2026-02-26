<?php

namespace Database\Seeders;

use App\Models\Emergency;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmergencySeeder extends Seeder
{
    public function run(): void
    {
        $patient = User::firstWhere('email', 'patient@afyarescue.test');
        $hospital = Hospital::firstWhere('slug', 'city-general');
        if (! $patient || ! $hospital) {
            return;
        }

        $ambulance = $hospital->ambulances()->whereNotNull('driver_id')->first();
        $statuses = [Emergency::STATUS_REQUESTED, Emergency::STATUS_ASSIGNED, Emergency::STATUS_ENROUTE];
        foreach ([1, 2, 3] as $i) {
            $status = $statuses[$i - 1];
            $e = Emergency::create([
                'patient_id' => $patient->id,
                'hospital_id' => $hospital->id,
                'ambulance_id' => ($status !== Emergency::STATUS_REQUESTED && $ambulance) ? $ambulance->id : null,
                'status' => $status,
                'latitude' => -6.3690 + ($i * 0.002),
                'longitude' => 34.8888 + ($i * 0.001),
                'address_text' => "Test location $i, Main Street",
                'severity_score' => [3, 6, 8][$i - 1],
                'severity_label' => ['medium', 'high', 'high'][$i - 1],
                'requested_at' => now()->subMinutes($i * 15),
                'assigned_at' => $status !== Emergency::STATUS_REQUESTED ? now()->subMinutes($i * 14) : null,
                'enroute_at' => $status === Emergency::STATUS_ENROUTE ? now()->subMinutes(5) : null,
            ]);
            $e->triageResponse()->create([
                'responses' => [
                    'conscious' => true,
                    'breathing' => $i === 3 ? 'difficult' : 'normal',
                    'bleeding' => $i === 2 ? 'minor' : 'none',
                    'chest_pain' => $i >= 2,
                    'stroke_symptoms' => false,
                    'pregnancy_emergency' => false,
                    'allergic_reaction' => false,
                    'number_of_casualties' => 1,
                ],
                'calculated_score' => $e->severity_score,
            ]);
        }
    }
}
