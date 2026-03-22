@extends('layouts.app')
@section('title', 'Emergency #' . $emergency->id)
@section('content')

{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="mx-auto max-w-2xl space-y-4">
    <h1 class="text-2xl font-bold text-white">Emergency #{{ $emergency->id }}</h1>

    {{-- Status progress bar --}}
    @php
        $steps = ['requested','assigned','enroute','arrived','closed'];
        $currentIdx = array_search($emergency->status, $steps);
    @endphp
    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
        <p class="mb-3 text-xs font-medium uppercase tracking-widest text-slate-400">Progress</p>
        <div class="flex items-center gap-1">
            @foreach($steps as $i => $step)
                <div class="flex flex-1 flex-col items-center">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold
                        @if($i < $currentIdx) bg-emerald-500 text-white
                        @elseif($i === $currentIdx) bg-red-500 text-white ring-4 ring-red-500/30
                        @else bg-slate-700 text-slate-400
                        @endif">
                        @if($i < $currentIdx)✓@else{{ $i + 1 }}@endif
                    </div>
                    <span class="mt-1 text-center text-[10px] capitalize text-slate-400">{{ $step }}</span>
                </div>
                @if(!$loop->last)
                    <div class="mb-4 h-0.5 flex-1 {{ $i < $currentIdx ? 'bg-emerald-500' : 'bg-slate-700' }}"></div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Details card --}}
    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-slate-400">Status</dt>
                <dd class="mt-1 font-semibold capitalize text-white">{{ str_replace('_', ' ', $emergency->status) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-400">Severity</dt>
                <dd class="mt-1 text-white">{{ $emergency->severity_category ?? $emergency->severity_label }} ({{ $emergency->severity_score }}/10)</dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-slate-400">Location</dt>
                <dd class="mt-1 text-white">{{ $emergency->address_text ?: $emergency->latitude . ', ' . $emergency->longitude }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-400">Hospital</dt>
                <dd class="mt-1 text-white">{{ $emergency->hospital->name }}</dd>
            </div>
            @if($emergency->eta_minutes && !in_array($emergency->status, ['arrived','closed']))
            <div>
                <dt class="text-sm font-medium text-slate-400">ETA</dt>
                <dd class="mt-1 font-bold text-amber-400">~{{ $emergency->eta_minutes }} min</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-slate-400">Requested at</dt>
                <dd class="mt-1 text-white">{{ $emergency->requested_at?->format('M j, Y H:i') }}</dd>
            </div>
            @if($emergency->assigned_at)
            <div>
                <dt class="text-sm font-medium text-slate-400">Assigned at</dt>
                <dd class="mt-1 text-white">{{ $emergency->assigned_at->format('M j, Y H:i') }}</dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- Ambulance & Driver card --}}
    @if($emergency->ambulance)
    <div class="rounded-lg border border-emerald-700/50 bg-emerald-900/20 p-4">
        <p class="mb-2 text-sm font-semibold text-emerald-400">Ambulance assigned</p>
        <p class="text-white font-medium">{{ $emergency->ambulance->plate_number ?? 'Ambulance #' . $emergency->ambulance->id }}</p>
        @if($emergency->ambulance->driver)
            <p class="mt-1 text-sm text-slate-300">
                Driver: <span class="font-medium text-white">{{ $emergency->ambulance->driver->name }}</span>
            </p>
            @if($emergency->ambulance->driver->phone)
                <a href="tel:{{ $emergency->ambulance->driver->phone }}"
                   class="mt-3 inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Call driver – {{ $emergency->ambulance->driver->phone }}
                </a>
            @endif
        @endif
    </div>
    @else
    <div class="rounded-lg border border-amber-700/50 bg-amber-900/20 p-4">
        <p class="text-sm font-medium text-amber-400">No ambulance assigned yet.</p>
        <p class="mt-1 text-sm text-slate-400">One will be assigned as soon as available. This page auto-refreshes.</p>
    </div>
    @endif

    {{-- Live map (shown when ambulance has coordinates) --}}
    @if($emergency->ambulance && $emergency->ambulance->latitude && $emergency->ambulance->longitude)
    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
        <p class="mb-2 text-sm font-semibold text-white">Live ambulance location</p>
        <div id="tracking-map" style="height:320px;width:100%;border-radius:0.5rem;z-index:1;"></div>
    </div>
    @endif

    <div class="flex gap-4 text-sm">
        <a href="{{ route('emergency.create') }}" class="text-red-400 hover:underline">Submit another emergency</a>
        <span class="text-slate-600">·</span>
        <a href="{{ route('dashboard.or.home') }}" class="text-slate-400 hover:underline">Dashboard</a>
    </div>
</div>

{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // ── Map ──────────────────────────────────────────────────────────────────
    @if($emergency->ambulance && $emergency->ambulance->latitude && $emergency->ambulance->longitude)
    const ambulanceLat = {{ $emergency->ambulance->latitude }};
    const ambulanceLng = {{ $emergency->ambulance->longitude }};
    const patientLat   = {{ $emergency->latitude }};
    const patientLng   = {{ $emergency->longitude }};
    const ambulanceId  = {{ $emergency->ambulance->id }};

    const map = L.map('tracking-map').setView([ambulanceLat, ambulanceLng], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors', maxZoom: 19
    }).addTo(map);

    // Patient marker (red)
    const patientIcon = L.circleMarker([patientLat, patientLng], {
        radius: 10, fillColor: '#ef4444', color: '#fff', weight: 2,
        opacity: 1, fillOpacity: 1
    }).addTo(map).bindPopup('Your location');

    // Ambulance marker (green)
    const ambulanceMarker = L.circleMarker([ambulanceLat, ambulanceLng], {
        radius: 10, fillColor: '#22c55e', color: '#fff', weight: 2,
        opacity: 1, fillOpacity: 1
    }).addTo(map).bindPopup('Ambulance');

    map.fitBounds([[patientLat, patientLng], [ambulanceLat, ambulanceLng]], { padding: [40, 40] });

    // Poll ambulance location every 5 seconds
    function pollAmbulanceLocation() {
        fetch('/ambulance/' + ambulanceId + '/location')
            .then(r => r.json())
            .then(data => {
                if (data.latitude && data.longitude) {
                    ambulanceMarker.setLatLng([data.latitude, data.longitude]);
                }
            })
            .catch(() => {});
    }
    setInterval(pollAmbulanceLocation, 5000);
    @endif

    // ── Auto-refresh page every 15s if not yet assigned ─────────────────────
    @if(!in_array($emergency->status, ['arrived', 'closed']))
    setTimeout(function() { location.reload(); }, 15000);
    @endif
</script>

@endsection