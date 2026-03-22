@extends('layouts.app')
@section('title', 'My Assignments')
@section('content')

<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">My emergency assignments</h1>

    {{-- GPS sharing status --}}
    <div id="gps-status" class="rounded-lg border border-slate-700 bg-slate-800/50 px-4 py-2 text-sm text-slate-400">
        📍 Location sharing: <span id="gps-label" class="font-medium text-amber-400">starting…</span>
    </div>

    @forelse($emergencies as $e)
    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm" id="emergency-{{ $e->id }}">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-900">Emergency #{{ $e->id }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium
                        @if($e->status === 'assigned') bg-blue-100 text-blue-800
                        @elseif($e->status === 'enroute') bg-indigo-100 text-indigo-800
                        @elseif($e->status === 'arrived') bg-green-100 text-green-800
                        @else bg-slate-100 text-slate-800
                        @endif">{{ str_replace('_', ' ', $e->status) }}</span>
                    <span class="rounded px-2 py-0.5 text-xs font-medium badge badge-{{ $e->severity_label }}">{{ $e->severity_label }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-600">{{ $e->address_text ?: $e->latitude . ', ' . $e->longitude }}</p>

                {{-- Patient info + call button --}}
                @if($e->patient)
                    <p class="mt-1 text-sm text-slate-700">
                        Patient: <span class="font-medium">{{ $e->patient->name }}</span>
                    </p>
                    @if($e->patient->phone)
                        <a href="tel:{{ $e->patient->phone }}"
                           class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-emerald-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            Call patient – {{ $e->patient->phone }}
                        </a>
                    @endif
                @endif

                @if($e->hospital)
                    <p class="mt-1 text-sm text-slate-500">Hospital: {{ $e->hospital->name }}</p>
                @endif
            </div>

            {{-- Status action buttons --}}
            <div class="flex flex-col gap-2">
                @if($e->status === 'assigned')
                    <form method="post" action="{{ route('driver.status', $e) }}">
                        @csrf
                        <input type="hidden" name="status" value="enroute" />
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">Mark en route</button>
                    </form>
                @endif
                @if($e->status === 'enroute')
                    <form method="post" action="{{ route('driver.status', $e) }}">
                        @csrf
                        <input type="hidden" name="status" value="arrived" />
                        <button type="submit" class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">Mark arrived</button>
                    </form>
                @endif
                @if(in_array($e->status, ['enroute', 'arrived']))
                    <form method="post" action="{{ route('driver.status', $e) }}">
                        @csrf
                        <input type="hidden" name="status" value="closed" />
                        <button type="submit" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Close</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <p class="rounded-lg border border-slate-200 bg-slate-50 p-6 text-center text-slate-600">No assignments at the moment.</p>
    @endforelse
</div>

<script>
// GPS: send location to server every 10 seconds for each active ambulance
const ambulanceIds = @json(
    $emergencies
        ->whereIn('status', ['assigned','enroute','arrived'])
        ->map(fn($e) => $e->ambulance_id)
        ->filter()
        ->unique()
        ->values()
);

const label = document.getElementById('gps-label');

function sendLocation(lat, lng) {
    ambulanceIds.forEach(function(id) {
        fetch('/ambulance/' + id + '/location', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ latitude: lat, longitude: lng })
        }).catch(() => {});
    });
}

function startGPS() {
    if (!navigator.geolocation) {
        label.textContent = 'not supported';
        label.className = 'font-medium text-red-400';
        return;
    }
    navigator.geolocation.watchPosition(
        function(pos) {
            label.textContent = 'active ✓';
            label.className = 'font-medium text-emerald-400';
            sendLocation(pos.coords.latitude, pos.coords.longitude);
        },
        function() {
            label.textContent = 'denied – please allow location';
            label.className = 'font-medium text-red-400';
        },
        { enableHighAccuracy: true, maximumAge: 5000, timeout: 10000 }
    );
}

if (ambulanceIds.length > 0) {
    startGPS();
} else {
    label.textContent = 'no active assignment';
    label.className = 'font-medium text-slate-500';
}
</script>

@endsection