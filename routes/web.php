<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\EmergencyRequestController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DriverDashboardController;
use App\Http\Controllers\HospitalDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/dashboard', HomeController::class)->name('dashboard.or.home')->middleware('auth');

// Patient: emergency request form
Route::middleware(['auth', 'role:patient'])->group(function () {
    Route::get('/emergency', [EmergencyRequestController::class, 'create'])->name('emergency.create');
    Route::post('/emergency', [EmergencyRequestController::class, 'store'])->name('emergency.store');
    Route::get('/emergency/{emergency}', [EmergencyRequestController::class, 'show'])->name('emergency.show');
});

// Driver dashboard
Route::middleware(['auth', 'role:driver'])->group(function () {
    Route::get('/driver', [DriverDashboardController::class, 'index'])->name('driver.dashboard');
    Route::post('/driver/emergency/{emergency}/status', [DriverDashboardController::class, 'updateStatus'])->name('driver.status');
});

// Super Admin: manage users, hospitals, ambulances
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::resource('users', \App\Http\Controllers\SuperAdmin\UserManagementController::class)->except(['show', 'destroy']);
    Route::resource('hospitals', \App\Http\Controllers\SuperAdmin\HospitalManagementController::class)->except(['show', 'destroy']);
    Route::resource('ambulances', \App\Http\Controllers\SuperAdmin\AmbulanceManagementController::class)->except(['show', 'destroy']);
});

// Hospital dashboard
Route::get('/hospital', [HospitalDashboardController::class, 'index'])
    ->name('hospital.dashboard')
    ->middleware(['auth', 'role:hospital_admin,super_admin']);
Route::get('/hospital/export', [HospitalDashboardController::class, 'exportCsv'])
    ->name('hospital.export')
    ->middleware(['auth', 'role:hospital_admin,super_admin']);
