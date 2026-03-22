@extends('layouts.app')
@section('title', 'Assign Ambulance – Emergency #' . $emergency->id)
@section('content')
<div class="mx-auto max-w-xl space-y-6">
    <h1 class="text-2xl font-bold text-white">Assign ambulance – Emergency #{{ $emergency->id }}</h1>

    {{-- Emergency summary --}}
    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4 text-sm">
        <div class="grid gap-2 sm:grid-cols-2">
            <div>
                <p class="text-slate-400">Patient</p>
                <p class="font-medium text-white">{{ $emergency->patient->name ?? 'N/A' }}</p>
                @if($emergency->patient?->phone)
                    <a href="tel:{{ $emergency->patient->phone }}" class="text-emerald-400 hover:underline">{{ $emergency->patient->phone }}</a>
                @endif
            </div>
            <div>
                <p class="text-slate-400">Severity</p>
                <p class="font-medium text-white">{{ $emergency->severity_category ?? $emergency->severity_label }} ({{ $emergency->severity_score }}/10)</p>
            </div>
            <div class="sm:col-span-2">
                <p class="text-slate-400">Location</p>
                <p class="text-white">{{ $emergency->address_text ?: $emergency->latitude . ', ' . $emergency->longitude }}</p>
            </div>
            <div>
                <p class="text-slate-400">Hospital</p>
                <p class="text-white">{{ $emergency->hospital->name }}</p>
            </div>
            <div>
                <p class="text-slate-400">Current status</p>
                <p class="font-medium capitalize text-amber-400">{{ $emergency->status }}</p>
            </div>
        </div>
    </div>

    {{-- Already assigned notice --}}
    @if($emergency->ambulance)
    <div class="rounded-lg border border-amber-700/50 bg-amber-900/20 p-3 text-sm text-amber-300">
        ⚠ Already assigned to <strong>{{ $emergency->ambulance->plate_number }}</strong>
        @if($emergency->ambulance->driver) (Driver: {{ $emergency->ambulance->driver->name }})@endif.
        Re-assigning will update the ambulance.
    </div>
    @endif

    {{-- Assignment form --}}
    @if($ambulances->isEmpty())
        <div class="rounded-lg border border-red-700/50 bg-red-900/20 p-4 text-sm text-red-300">
            No available ambulances at this hospital right now.
        </div>
    @else
    <form method="POST" action="{{ route('emergency.assign.store', $emergency) }}"
          class="rounded-lg border border-slate-700 bg-slate-800/50 p-6 space-y-4">
        @csrf
        @if($errors->any())
            <div class="rounded-md bg-red-900/30 border border-red-700 p-3 text-sm text-red-300">
                {{ $errors->first() }}
            </div>
        @endif

        <div>
            <label class="mb-1 block text-sm font-medium text-white">Select ambulance</label>
            <select name="ambulance_id" required
                class="w-full rounded-md border border-slate-600 bg-slate-700 px-3 py-2 text-sm text-white">
                <option value="">— choose —</option>
                @foreach($ambulances as $amb)
                    <option value="{{ $amb->id }}" {{ old('ambulance_id') == $amb->id ? 'selected' : '' }}>
                        {{ $amb->plate_number }} ({{ ucfirst($amb->type) }})
                        @if($amb->driver) – Driver: {{ $amb->driver->name }} {{ $amb->driver->phone ? '(' . $amb->driver->phone . ')' : '' }}@endif
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-white">ETA (minutes) <span class="text-slate-400 font-normal">optional</span></label>
            <input type="number" name="eta_minutes" min="1" max="300" value="{{ old('eta_minutes') }}"
                placeholder="e.g. 10"
                class="w-full rounded-md border border-slate-600 bg-slate-700 px-3 py-2 text-sm text-white placeholder:text-slate-500" />
        </div>

        <button type="submit"
            class="w-full rounded-md bg-red-600 py-2 text-sm font-semibold text-white hover:bg-red-500">
            Assign ambulance
        </button>
    </form>
    @endif

    <a href="{{ url()->previous() }}" class="text-sm text-slate-400 hover:underline">← Back</a>
</div>
@endsection