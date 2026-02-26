<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ambulance;
use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmbulanceManagementController extends Controller
{
    public function index(Request $request): View
    {
        $query = Ambulance::with(['hospital:id,name', 'driver:id,name']);
        if ($request->filled('hospital_id')) {
            $query->where('hospital_id', $request->hospital_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $ambulances = $query->orderBy('hospital_id')->orderBy('plate_number')->paginate(20);
        $hospitals = Hospital::orderBy('name')->get(['id', 'name']);
        return view('super-admin.ambulances.index', ['ambulances' => $ambulances, 'hospitals' => $hospitals]);
    }

    public function create(): View
    {
        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get();
        $drivers = User::where('role', 'driver')->orderBy('name')->get(['id', 'name', 'hospital_id']);
        return view('super-admin.ambulances.form', ['ambulance' => null, 'hospitals' => $hospitals, 'drivers' => $drivers]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'driver_id' => ['nullable', 'integer', 'exists:users,id'],
            'plate_number' => ['nullable', 'string', 'max:32'],
            'status' => ['required', 'string', 'in:available,busy,maintenance'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        Ambulance::create($validated);
        return redirect()->route('super-admin.ambulances.index')->with('success', 'Ambulance created.');
    }

    public function edit(Ambulance $ambulance): View
    {
        $hospitals = Hospital::where('is_active', true)->orderBy('name')->get();
        $drivers = User::where('role', 'driver')->orderBy('name')->get(['id', 'name', 'hospital_id']);
        return view('super-admin.ambulances.form', ['ambulance' => $ambulance, 'hospitals' => $hospitals, 'drivers' => $drivers]);
    }

    public function update(Request $request, Ambulance $ambulance)
    {
        $validated = $request->validate([
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'driver_id' => ['nullable', 'integer', 'exists:users,id'],
            'plate_number' => ['nullable', 'string', 'max:32'],
            'status' => ['required', 'string', 'in:available,busy,maintenance'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);
        $ambulance->update($validated);
        return redirect()->route('super-admin.ambulances.index')->with('success', 'Ambulance updated.');
    }
}
