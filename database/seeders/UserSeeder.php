<?php

namespace Database\Seeders;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hospitals = Hospital::all();
        $cityGeneral = $hospitals->firstWhere('slug', 'city-general');
        $metro = $hospitals->firstWhere('slug', 'metro-emergency');

        User::updateOrCreate(
            ['email' => 'patient@afyarescue.test'],
            ['name' => 'Test Patient', 'password' => Hash::make('password'), 'role' => User::ROLE_PATIENT, 'phone' => '+255 712 000 001']
        );
        User::updateOrCreate(
            ['email' => 'driver@afyarescue.test'],
            ['name' => 'Test Driver', 'password' => Hash::make('password'), 'role' => User::ROLE_DRIVER, 'phone' => '+255 712 000 002', 'hospital_id' => $cityGeneral?->id]
        );
        User::updateOrCreate(
            ['email' => 'hospital@afyarescue.test'],
            ['name' => 'Hospital Admin', 'password' => Hash::make('password'), 'role' => User::ROLE_HOSPITAL_ADMIN, 'phone' => '+255 712 000 003', 'hospital_id' => $cityGeneral?->id]
        );
        User::updateOrCreate(
            ['email' => 'admin@afyarescue.test'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'role' => User::ROLE_SUPER_ADMIN, 'phone' => null, 'hospital_id' => null]
        );
        if ($metro) {
            User::updateOrCreate(
                ['email' => 'driver2@afyarescue.test'],
                ['name' => 'Driver Two', 'password' => Hash::make('password'), 'role' => User::ROLE_DRIVER, 'phone' => '+255 712 000 004', 'hospital_id' => $metro->id]
            );
        }
    }
}
