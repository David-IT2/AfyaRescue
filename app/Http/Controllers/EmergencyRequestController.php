<?php

namespace App\Http\Controllers;

use App\Services\EmergencyFlowService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmergencyRequestController extends Controller
{
    public function __construct(
        protected EmergencyFlowService $emergencyFlow
    ) {}

    public function create(): View
    {
        $this->authorizePatient();
        $hospitals = \App\Models\Hospital::where('is_active', true)->orderBy('name')->get(['id', 'name', 'slug', 'address']);
        return view('emergency.request', ['hospitals' => $hospitals]);
    }

    public function store(Request $request)
    {
        $this->authorizePatient();
        $validated = $request->validate([
            'hospital_id' => ['required', 'integer', 'exists:hospitals,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address_text' => ['nullable', 'string', 'max:500'],
            'triage' => ['required', 'array'],
            'triage.conscious' => ['nullable', 'boolean'],
            'triage.breathing' => ['nullable', 'string', 'in:normal,difficult,absent'],
            'triage.bleeding' => ['nullable', 'string', 'in:none,minor,severe'],
            'triage.chest_pain' => ['nullable', 'boolean'],
            'triage.stroke_symptoms' => ['nullable', 'boolean'],
            'triage.pregnancy_emergency' => ['nullable', 'boolean'],
            'triage.allergic_reaction' => ['nullable', 'boolean'],
            'triage.number_of_casualties' => ['nullable', 'integer', 'min:0', 'max:20'],
        ]);

        $conscious = $request->input('triage.conscious');
        $triage = [
            'conscious' => $conscious === '1' || $conscious === true,
            'breathing' => $request->input('triage.breathing', 'normal'),
            'bleeding' => $request->input('triage.bleeding', 'none'),
            'chest_pain' => filter_var($request->input('triage.chest_pain'), FILTER_VALIDATE_BOOLEAN),
            'stroke_symptoms' => filter_var($request->input('triage.stroke_symptoms'), FILTER_VALIDATE_BOOLEAN),
            'pregnancy_emergency' => filter_var($request->input('triage.pregnancy_emergency'), FILTER_VALIDATE_BOOLEAN),
            'allergic_reaction' => filter_var($request->input('triage.allergic_reaction'), FILTER_VALIDATE_BOOLEAN),
            'number_of_casualties' => (int) $request->input('triage.number_of_casualties', 0),
        ];

        $emergency = $this->emergencyFlow->createEmergency(
            $request->user()->id,
            $validated['hospital_id'],
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            $validated['address_text'] ?? null,
            $triage
        );

        return redirect()->route('emergency.show', $emergency)->with('success', 'Emergency request submitted. An ambulance will be assigned if available.');
    }

    public function show(Request $request, \App\Models\Emergency $emergency)
    {
        if ($emergency->patient_id !== $request->user()->id) {
            abort(403);
        }
        $emergency->load(['hospital', 'ambulance.driver', 'triageResponse']);
        return view('emergency.show', ['emergency' => $emergency]);
    }

    private function authorizePatient(): void
    {
        if (! request()->user()?->hasRole('patient')) {
            abort(403, 'Only patients can submit emergency requests.');
        }
    }
}
