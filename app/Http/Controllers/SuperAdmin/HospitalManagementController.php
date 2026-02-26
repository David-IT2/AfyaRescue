<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class HospitalManagementController extends Controller
{
    public function index(): View
    {
        $hospitals = Hospital::withCount(['ambulances', 'emergencies'])->orderBy('name')->paginate(20);
        return view('super-admin.hospitals.index', ['hospitals' => $hospitals]);
    }

    public function create(): View
    {
        return view('super-admin.hospitals.form', ['hospital' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:32'],
            'level' => ['nullable', 'integer', 'min:1', 'max:3'],
        ]);
        $validated['slug'] = Str::slug($validated['name']) . '-' . substr(uniqid(), -4);
        $validated['level'] = $validated['level'] ?? 1;
        Hospital::create($validated);
        return redirect()->route('super-admin.hospitals.index')->with('success', 'Hospital created.');
    }

    public function edit(Hospital $hospital): View
    {
        return view('super-admin.hospitals.form', ['hospital' => $hospital]);
    }

    public function update(Request $request, Hospital $hospital)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'phone' => ['nullable', 'string', 'max:32'],
            'level' => ['nullable', 'integer', 'min:1', 'max:3'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $hospital->fill($validated);
        $hospital->is_active = $request->boolean('is_active', $hospital->is_active);
        $hospital->level = $validated['level'] ?? $hospital->level;
        $hospital->save();
        return redirect()->route('super-admin.hospitals.index')->with('success', 'Hospital updated.');
    }
}
