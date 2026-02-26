<?php

namespace Database\Seeders;

use App\Models\Ambulance;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Seeder;

class AmbulanceSeeder extends Seeder
{
    public function run(): void
    {
        $cityGeneral = Hospital::firstWhere('slug', 'city-general');
        $metro = Hospital::firstWhere('slug', 'metro-emergency');
        $driver1 = User::firstWhere('email', 'driver@afyarescue.test');
        $driver2 = User::firstWhere('email', 'driver2@afyarescue.test');

        if ($cityGeneral) {
            Ambulance::updateOrCreate(
                ['hospital_id' => $cityGeneral->id, 'plate_number' => 'AMB-001'],
                ['driver_id' => $driver1?->id, 'status' => Ambulance::STATUS_AVAILABLE, 'latitude' => -6.368, 'longitude' => 34.889]
            );
            Ambulance::updateOrCreate(
                ['hospital_id' => $cityGeneral->id, 'plate_number' => 'AMB-002'],
                ['driver_id' => null, 'status' => Ambulance::STATUS_AVAILABLE, 'latitude' => -6.370, 'longitude' => 34.890]
            );
        }
        if ($metro && $driver2) {
            Ambulance::updateOrCreate(
                ['hospital_id' => $metro->id, 'plate_number' => 'AMB-003'],
                ['driver_id' => $driver2->id, 'status' => Ambulance::STATUS_AVAILABLE, 'latitude' => -6.351, 'longitude' => 34.901]
            );
        }
    }
}
