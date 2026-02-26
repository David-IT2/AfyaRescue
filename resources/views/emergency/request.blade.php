@extends('layouts.app')
@section('title', 'Request Emergency')
@section('content')
<div class="mx-auto max-w-2xl">
    <h1 class="mb-6 text-2xl font-bold text-slate-800">Request Emergency Assistance</h1>
    <form method="POST" action="{{ route('emergency.store') }}" class="space-y-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label for="hospital_id" class="mb-1 block text-sm font-medium text-slate-700">Nearest hospital / Preferred hospital</label>
            <select id="hospital_id" name="hospital_id" required
                class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500"
            >
                @foreach($hospitals as $h)
                    <option value="{{ $h->id }}" {{ (int) old('hospital_id') === $h->id ? 'selected' : '' }}>{{ $h->name }} @if($h->address) – {{ Str::limit($h->address, 40) }} @endif</option>
                @endforeach
            </select>
            @error('hospital_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="latitude" class="mb-1 block text-sm font-medium text-slate-700">Latitude</label>
                <input id="latitude" type="text" name="latitude" value="{{ old('latitude', '-6.3690') }}" required
                    class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="-6.3690"
                />
                @error('latitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="longitude" class="mb-1 block text-sm font-medium text-slate-700">Longitude</label>
                <input id="longitude" type="text" name="longitude" value="{{ old('longitude', '34.8888') }}" required
                    class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="34.8888"
                />
                @error('longitude') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
        <p class="text-xs text-slate-500">In a real app, location would come from GPS. For testing, use coordinates or click "Use my location" below.</p>
        <div>
            <label for="address_text" class="mb-1 block text-sm font-medium text-slate-700">Address or landmark (optional)</label>
            <input id="address_text" type="text" name="address_text" value="{{ old('address_text') }}"
                class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500"
                placeholder="e.g. Near Main Street, Building 5"
            />
        </div>

        <hr class="border-slate-200" />
        <h2 class="text-lg font-semibold text-slate-800">Triage questionnaire</h2>
        <div class="space-y-4">
            <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                <label for="triage_conscious" class="text-sm font-medium text-slate-700">Is the patient conscious?</label>
                <select id="triage_conscious" name="triage[conscious]"
                    class="rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-red-500 focus:ring-red-500"
                >
                    <option value="1">Yes</option>
                    <option value="0" {{ old('triage.conscious') === '0' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                <label for="triage_breathing" class="text-sm font-medium text-slate-700">Breathing</label>
                <select id="triage_breathing" name="triage[breathing]"
                    class="rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-red-500 focus:ring-red-500"
                >
                    <option value="normal" {{ old('triage.breathing', 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="difficult" {{ old('triage.breathing') === 'difficult' ? 'selected' : '' }}>Difficult</option>
                    <option value="absent" {{ old('triage.breathing') === 'absent' ? 'selected' : '' }}>Absent</option>
                </select>
            </div>
            <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                <label for="triage_bleeding" class="text-sm font-medium text-slate-700">Bleeding</label>
                <select id="triage_bleeding" name="triage[bleeding]"
                    class="rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-red-500 focus:ring-red-500"
                >
                    <option value="none" {{ old('triage.bleeding', 'none') === 'none' ? 'selected' : '' }}>None</option>
                    <option value="minor" {{ old('triage.bleeding') === 'minor' ? 'selected' : '' }}>Minor</option>
                    <option value="severe" {{ old('triage.bleeding') === 'severe' ? 'selected' : '' }}>Severe</option>
                </select>
            </div>
            <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                <label for="triage_chest_pain" class="text-sm font-medium text-slate-700">Chest pain?</label>
                <select id="triage_chest_pain" name="triage[chest_pain]"
                    class="rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-red-500 focus:ring-red-500"
                >
                    <option value="0">No</option>
                    <option value="1" {{ old('triage.chest_pain') === '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>
            <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                <label for="triage_stroke_symptoms" class="text-sm font-medium text-slate-700">Stroke symptoms?</label>
                <select id="triage_stroke_symptoms" name="triage[stroke_symptoms]"
                    class="rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-red-500 focus:ring-red-500"
                >
                    <option value="0">No</option>
                    <option value="1" {{ old('triage.stroke_symptoms') === '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>
            <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                <label for="triage_pregnancy_emergency" class="text-sm font-medium text-slate-700">Pregnancy emergency?</label>
                <select id="triage_pregnancy_emergency" name="triage[pregnancy_emergency]"
                    class="rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-red-500 focus:ring-red-500"
                >
                    <option value="0">No</option>
                    <option value="1" {{ old('triage.pregnancy_emergency') === '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>
            <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                <label for="triage_allergic_reaction" class="text-sm font-medium text-slate-700">Severe allergic reaction?</label>
                <select id="triage_allergic_reaction" name="triage[allergic_reaction]"
                    class="rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-red-500 focus:ring-red-500"
                >
                    <option value="0">No</option>
                    <option value="1" {{ old('triage.allergic_reaction') === '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>
            <div class="flex items-center justify-between rounded-md border border-slate-200 p-3">
                <label for="triage_number_of_casualties" class="text-sm font-medium text-slate-700">Number of casualties</label>
                <input id="triage_number_of_casualties" type="number" name="triage[number_of_casualties]" min="0" max="20" value="{{ old('triage.number_of_casualties', 1) }}"
                    class="w-20 rounded-md border border-slate-300 px-2 py-1 text-sm focus:border-red-500 focus:ring-red-500"
                />
            </div>
        </div>
        <div class="flex gap-3">
            <button type="submit" class="rounded-md bg-red-600 px-6 py-2 font-medium text-white hover:bg-red-700">Submit Emergency Request</button>
            <a href="{{ route('dashboard.or.home') }}" class="rounded-md border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
</div>
@endsection
