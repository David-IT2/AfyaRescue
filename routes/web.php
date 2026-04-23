<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\EmergencyRequestController;
use App\Http\Controllers\AmbulanceAssignmentController;
use App\Http\Controllers\DriverLocationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DriverDashboardController;
use App\Http\Controllers\HospitalDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()
        ->view('welcome')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/dashboard', HomeController::class)->name('dashboard.or.home')->middleware('auth');

// Public: anyone can request emergency help without logging in
Route::get('/emergency', [EmergencyRequestController::class, 'create'])->name('emergency.create');
Route::post('/emergency', [EmergencyRequestController::class, 'store'])->name('emergency.store');

// Auth required: only the patient who made the request can track it
Route::middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/emergency/{emergency}', [EmergencyRequestController::class, 'show'])->name('emergency.show');
});

// Driver dashboard + location sharing
Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/driver', [DriverDashboardController::class, 'index'])->name('driver.dashboard');
    Route::post('/driver/emergency/{emergency}/status', [DriverDashboardController::class, 'updateStatus'])->name('driver.status');
});

// Ambulance assignment (hospital_admin + super_admin)
Route::middleware(['auth', 'role:hospital_admin,super_admin'])->group(function () {
    Route::get('/emergency/{emergency}/assign', [AmbulanceAssignmentController::class, 'create'])->name('emergency.assign');
    Route::post('/emergency/{emergency}/assign', [AmbulanceAssignmentController::class, 'store'])->name('emergency.assign.store');
});

// Ambulance location — driver posts, patient polls (both authenticated)
Route::middleware('auth')->group(function () {
    Route::post('/ambulance/{ambulance}/location', [DriverLocationController::class, 'update'])->name('ambulance.location.update');
    Route::get('/ambulance/{ambulance}/location', [DriverLocationController::class, 'show'])->name('ambulance.location.show');
});

// Super Admin: system health, manage users, hospitals, ambulances, metrics
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/', [\App\Http\Controllers\SuperAdmin\SystemHealthController::class, 'index'])->name('health');
    Route::get('/health', [\App\Http\Controllers\SuperAdmin\SystemHealthController::class, 'index'])->name('health.index');
    Route::get('/metrics', [\App\Http\Controllers\SuperAdmin\MetricsController::class, 'index'])->name('metrics');
    Route::resource('users', \App\Http\Controllers\SuperAdmin\UserManagementController::class)->except(['show']);
    Route::resource('hospitals', \App\Http\Controllers\SuperAdmin\HospitalManagementController::class)->except(['show']);
    Route::resource('ambulances', \App\Http\Controllers\SuperAdmin\AmbulanceManagementController::class)->except(['show']);
});

// Hospital dashboard
Route::get('/hospital', [HospitalDashboardController::class, 'index'])
    ->name('hospital.dashboard')
    ->middleware(['auth', 'role:hospital_admin,super_admin']);
Route::get('/hospital/export', [HospitalDashboardController::class, 'exportCsv'])
    ->name('hospital.export')
    ->middleware(['auth', 'role:hospital_admin,super_admin']);
Route::get('/hospital/report', [HospitalDashboardController::class, 'exportReport'])
    ->name('hospital.report')
    ->middleware(['auth', 'role:hospital_admin,super_admin']);
Route::get('/hospital/patient/{patient}', [HospitalDashboardController::class, 'patientHistory'])
    ->name('hospital.patient')
    ->middleware(['auth', 'role:hospital_admin,super_admin']);