<?php

namespace Database\Seeders;

use App\Models\Hospital;
use Illuminate\Database\Seeder;

class HospitalSeeder extends Seeder
{
    public function run(): void
    {
        $hospitals = [
            ['name' => 'City General Hospital', 'slug' => 'city-general', 'address' => '123 Main St', 'latitude' => -6.3690, 'longitude' => 34.8888, 'phone' => '+255 22 123 4567'],
            ['name' => 'Metro Emergency Center', 'slug' => 'metro-emergency', 'address' => '456 Oak Ave', 'latitude' => -6.3500, 'longitude' => 34.9000, 'phone' => '+255 22 234 5678'],
        ];
        foreach ($hospitals as $h) {
            Hospital::updateOrCreate(['slug' => $h['slug']], $h);
        }
    }
}
