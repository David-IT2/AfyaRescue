@extends('layouts.app')
@section('title', 'Metrics')
@section('content')

{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-white">Metrics</h1>
        <div class="flex gap-2">
            <a href="{{ route('super-admin.health') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-bold text-white hover:bg-slate-700">System Health</a>
            <a href="{{ route('hospital.dashboard') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-bold text-white hover:bg-slate-700">Hospital Dashboard</a>
        </div>
    </div>

    {{-- Date range filter --}}
    <form method="get" class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
        <div class="flex flex-wrap items-end gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-white">Range</label>
                <select name="range" id="range-select" onchange="toggleCustom(this.value)"
                    class="rounded-md border border-slate-600 bg-slate-700 px-3 py-1.5 text-sm text-white">
                    <option value="0"  {{ $range === '0'      ? 'selected' : '' }}>Today</option>
                    <option value="7"  {{ $range === '7'      ? 'selected' : '' }}>Last 7 days</option>
                    <option value="30" {{ $range === '30'     ? 'selected' : '' }}>Last 30 days</option>
                    <option value="custom" {{ $range === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>
            <div id="custom-dates" class="{{ $range === 'custom' ? 'flex' : 'hidden' }} items-end gap-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-white">From</label>
                    <input type="date" name="from" value="{{ $from }}"
                        class="rounded-md border border-slate-600 bg-slate-700 px-3 py-1.5 text-sm text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-white">To</label>
                    <input type="date" name="to" value="{{ $to }}"
                        class="rounded-md border border-slate-600 bg-slate-700 px-3 py-1.5 text-sm text-white" />
                </div>
            </div>
            <button type="submit" class="rounded-md bg-red-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-red-500">Apply</button>
        </div>
        <p class="mt-2 text-xs text-slate-400">
            Showing: {{ $start->format('M j, Y') }} — {{ $end->format('M j, Y') }}
        </p>
    </form>

    {{-- Emergency stats cards --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
            <p class="text-sm text-slate-400">Total emergencies</p>
            <p class="text-3xl font-bold text-white">{{ $totalEmergencies }}</p>
        </div>
        <div class="rounded-lg border border-amber-700/50 bg-amber-900/20 p-4">
            <p class="text-sm text-amber-400">Active</p>
            <p class="text-3xl font-bold text-amber-300">{{ $activeEmergencies }}</p>
        </div>
        <div class="rounded-lg border border-emerald-700/50 bg-emerald-900/20 p-4">
            <p class="text-sm text-emerald-400">Closed</p>
            <p class="text-3xl font-bold text-emerald-300">{{ $closedEmergencies }}</p>
        </div>
        <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
            <p class="text-sm text-slate-400">By severity</p>
            <div class="mt-1 space-y-0.5">
                @forelse($bySeverity as $cat => $count)
                    <p class="text-sm font-medium
                        @if(strtolower($cat) === 'critical') text-red-400
                        @elseif(strtolower($cat) === 'moderate') text-amber-400
                        @else text-emerald-400
                        @endif">
                        {{ $cat ?? 'N/A' }}: <span class="font-bold">{{ $count }}</span>
                    </p>
                @empty
                    <p class="text-sm text-slate-500">No data</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Status breakdown + Avg times --}}
    <div class="grid gap-4 sm:grid-cols-2">
        {{-- Status breakdown --}}
        <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
            <h2 class="mb-3 text-sm font-semibold text-white">Emergencies by status</h2>
            <div class="space-y-2">
                @foreach(['requested','assigned','enroute','arrived','closed'] as $s)
                    @php $count = $byStatus[$s] ?? 0; $pct = $totalEmergencies > 0 ? round($count / $totalEmergencies * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs text-slate-400 mb-0.5">
                            <span class="capitalize">{{ $s }}</span>
                            <span>{{ $count }} ({{ $pct }}%)</span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-slate-700">
                            <div class="h-2 rounded-full
                                @if($s==='requested') bg-amber-400
                                @elseif($s==='assigned') bg-blue-400
                                @elseif($s==='enroute') bg-indigo-400
                                @elseif($s==='arrived') bg-emerald-400
                                @else bg-slate-400
                                @endif"
                                style="width:{{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Avg response times --}}
        <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
            <h2 class="mb-3 text-sm font-semibold text-white">Avg. response times</h2>
            <div class="space-y-3">
                <div class="flex items-center justify-between rounded-md bg-slate-700/50 px-3 py-2">
                    <span class="text-sm text-slate-300">Request → Assigned</span>
                    <span class="text-lg font-bold text-white">
                        {{ $avgAssignmentMin !== null ? round($avgAssignmentMin) . ' min' : '—' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-md bg-slate-700/50 px-3 py-2">
                    <span class="text-sm text-slate-300">Assigned → En route</span>
                    <span class="text-lg font-bold text-white">
                        {{ $avgEnrouteMin !== null ? round($avgEnrouteMin) . ' min' : '—' }}
                    </span>
                </div>
                <div class="flex items-center justify-between rounded-md bg-slate-700/50 px-3 py-2">
                    <span class="text-sm text-slate-300">En route → Arrived</span>
                    <span class="text-lg font-bold text-white">
                        {{ $avgArrivalMin !== null ? round($avgArrivalMin) . ' min' : '—' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Hospitals, Ambulances, Users row --}}
    <div class="grid gap-4 sm:grid-cols-3">
        {{-- Hospitals --}}
        <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
            <h2 class="mb-3 text-sm font-semibold text-white">Hospitals</h2>
            <div class="flex items-end gap-4">
                <div>
                    <p class="text-3xl font-bold text-white">{{ $totalHospitals }}</p>
                    <p class="text-xs text-slate-400">Total</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-400">{{ $activeHospitals }}</p>
                    <p class="text-xs text-slate-400">Active</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-red-400">{{ $totalHospitals - $activeHospitals }}</p>
                    <p class="text-xs text-slate-400">Inactive</p>
                </div>
            </div>
        </div>

        {{-- Ambulances --}}
        <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
            <h2 class="mb-3 text-sm font-semibold text-white">Ambulances</h2>
            <div class="flex items-end gap-4">
                <div>
                    <p class="text-3xl font-bold text-white">{{ $totalAmbulances }}</p>
                    <p class="text-xs text-slate-400">Total</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-emerald-400">{{ $availableAmbulances }}</p>
                    <p class="text-xs text-slate-400">Available</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-amber-400">{{ $busyAmbulances }}</p>
                    <p class="text-xs text-slate-400">Busy</p>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-400">{{ $maintenanceAmbulances }}</p>
                    <p class="text-xs text-slate-400">Maint.</p>
                </div>
            </div>
        </div>

        {{-- Users by role --}}
        <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
            <h2 class="mb-3 text-sm font-semibold text-white">Users by role</h2>
            <div class="space-y-1">
                @foreach(['patient','driver','hospital_admin','super_admin'] as $role)
                    <div class="flex justify-between text-sm">
                        <span class="text-slate-400">{{ ucfirst(str_replace('_', ' ', $role)) }}</span>
                        <span class="font-bold text-white">{{ $usersByRole[$role] ?? 0 }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Map --}}
    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4">
        <h2 class="mb-3 text-sm font-semibold text-white">
            Emergency locations
            <span class="ml-2 text-xs font-normal text-slate-400">({{ $mapEmergencies->count() }} in range)</span>
        </h2>
        <div id="metrics-map" style="height:480px;width:100%;border-radius:0.5rem;z-index:1;"></div>
    </div>

</div>

{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Toggle custom date inputs
    function toggleCustom(val) {
        document.getElementById('custom-dates').classList.toggle('hidden', val !== 'custom');
        document.getElementById('custom-dates').classList.toggle('flex', val === 'custom');
    }

    // Map
    const map = L.map('metrics-map').setView([-1.286389, 36.817223], 7); // Kenya default

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19,
    }).addTo(map);

    const emergencies = @json($mapEmergencies);

    const colors = {
        requested: '#f59e0b',
        assigned:  '#3b82f6',
        enroute:   '#6366f1',
        arrived:   '#10b981',
        closed:    '#64748b',
    };

    const bounds = [];

    emergencies.forEach(function(e) {
        const lat = parseFloat(e.latitude);
        const lng = parseFloat(e.longitude);
        if (!lat || !lng) return;

        bounds.push([lat, lng]);

        const color = colors[e.status] || '#64748b';

        const marker = L.circleMarker([lat, lng], {
            radius: 8,
            fillColor: color,
            color: '#fff',
            weight: 1.5,
            opacity: 1,
            fillOpacity: 0.85,
        }).addTo(map);

        marker.bindPopup(
            '<div style="min-width:160px">' +
            '<strong>#' + e.id + '</strong><br>' +
            '<span style="color:' + color + ';font-weight:600;text-transform:capitalize;">' + e.status + '</span><br>' +
            (e.severity_category ? 'Severity: ' + e.severity_category + '<br>' : '') +
            (e.address_text ? e.address_text + '<br>' : '') +
            (e.patient ? 'Patient: ' + e.patient.name + '<br>' : '') +
            (e.hospital ? 'Hospital: ' + e.hospital.name + '<br>' : '') +
            (e.requested_at ? '<small>' + e.requested_at + '</small>' : '') +
            '</div>'
        );
    });

    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40] });
    }
</script>

@endsection