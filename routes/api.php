<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - AfyaRescue (API-first for Flutter etc.)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    Route::post('/login', [App\Http\Controllers\Api\AuthController::class, 'login']);
    Route::post('/register', [App\Http\Controllers\Api\AuthController::class, 'register']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
        Route::get('/user', [App\Http\Controllers\Api\AuthController::class, 'user']);

        // Patient: submit emergency
        Route::post('/emergencies', [App\Http\Controllers\Api\EmergencyController::class, 'store'])
            ->middleware('role:patient');
        Route::get('/emergencies/my', [App\Http\Controllers\Api\EmergencyController::class, 'myEmergencies'])
            ->middleware('role:patient');
        Route::get('/emergencies/{emergency}', [App\Http\Controllers\Api\EmergencyController::class, 'show']);

        // Driver: list assigned, update status
        Route::get('/driver/emergencies', [App\Http\Controllers\Api\DriverEmergencyController::class, 'index'])
            ->middleware('role:driver');
        Route::patch('/driver/emergencies/{emergency}/status', [App\Http\Controllers\Api\DriverEmergencyController::class, 'updateStatus'])
            ->middleware('role:driver');
        Route::put('/driver/location', [App\Http\Controllers\Api\DriverLocationController::class, 'update'])
            ->middleware('role:driver');

        // Hospital admin: list emergencies for hospital
        Route::get('/hospital/emergencies', [App\Http\Controllers\Api\HospitalEmergencyController::class, 'index'])
            ->middleware('role:hospital_admin,super_admin');
        Route::get('/hospital/emergencies/{emergency}', [App\Http\Controllers\Api\HospitalEmergencyController::class, 'show'])
            ->middleware('role:hospital_admin,super_admin');
        Route::patch('/hospital/emergencies/{emergency}/notes', [App\Http\Controllers\Api\HospitalEmergencyController::class, 'updateNotes'])
            ->middleware('role:hospital_admin,super_admin');
    });
});
