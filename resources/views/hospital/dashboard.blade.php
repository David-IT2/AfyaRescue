@extends('layouts.app')
@section('title', 'Hospital Dashboard')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-white">Incoming emergencies</h1>
        <div class="flex flex-wrap items-center gap-3">
            @if(!empty($isSuperAdmin) && $hospitals && $hospitals->isNotEmpty())
                <form method="get" class="flex items-center gap-2" id="filter-form">
                    <input type="hidden" name="patient" value="{{ request('patient') }}" />
                    <input type="hidden" name="patient_id" value="{{ request('patient_id') }}" />
                    <input type="hidden" name="status" value="{{ request('status') }}" />
                    <input type="hidden" name="from" value="{{ request('from') }}" />
                    <input type="hidden" name="to" value="{{ request('to') }}" />
                    <label for="hospital_id" class="text-sm font-bold text-white">Hospital:</label>
                    <select name="hospital_id" id="hospital_id" onchange="this.form.submit()" class="rounded-md border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900">
                        <option value="">All</option>
                        @foreach($hospitals as $h)
                            <option value="{{ $h->id }}" {{ request('hospital_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a href="{{ route('hospital.export') }}?{{ request()->getQueryString() }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Export CSV</a>
            <a href="{{ route('hospital.report') }}?{{ request()->getQueryString() }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" target="_blank">Report (Print/PDF)</a>
            <span class="text-xs font-bold text-white">Reload for latest</span>
        </div>
    </div>

    {{-- Filters --}}
    <form method="get" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="hospital_id" value="{{ request('hospital_id') }}" />
        <input type="hidden" name="patient_id" value="{{ request('patient_id') }}" />
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[120px]">
                <label for="patient" class="mb-1 block text-xs font-medium text-slate-700">Patient</label>
                <input type="text" name="patient" id="patient" value="{{ request('patient') }}" placeholder="Name or phone" class="w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-900 placeholder:text-slate-400" />
            </div>
            <div>
                <label for="status" class="mb-1 block text-xs font-medium text-slate-700">Status</label>
                <select name="status" id="status" class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-900">
                    <option value="">All</option>
                    @foreach(['requested','assigned','enroute','arrived','closed'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="from" class="mb-1 block text-xs font-medium text-slate-700">From</label>
                <input type="date" name="from" id="from" value="{{ request('from') }}" class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-900" />
            </div>
            <div>
                <label for="to" class="mb-1 block text-xs font-medium text-slate-700">To</label>
                <input type="date" name="to" id="to" value="{{ request('to') }}" class="rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-900" />
            </div>
            <button type="submit" class="rounded-md bg-slate-700 px-3 py-1 text-xs font-medium text-white hover:bg-slate-800">Filter</button>
        </div>
    </form>

    {{-- Analytics --}}
    @if(!empty($stats))
    <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Avg. assignment time</p>
            <p class="text-lg font-semibold text-slate-800">{{ $stats['avg_assignment_min'] !== null ? $stats['avg_assignment_min'] . ' min' : '—' }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Avg. en route time</p>
            <p class="text-lg font-semibold text-slate-800">{{ $stats['avg_enroute_min'] !== null ? $stats['avg_enroute_min'] . ' min' : '—' }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">By severity</p>
            <p class="text-xs font-medium text-slate-800">
                @foreach($stats['by_severity'] ?? [] as $cat => $count)
                    <span class="mr-2">{{ $cat ?? 'N/A' }}: {{ $count }}</span>
                @endforeach
                @if(empty($stats['by_severity']))—@endif
            </p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-3 shadow-sm">
            <p class="text-xs text-slate-500">Ambulance utilization</p>
            <p class="text-lg font-semibold text-slate-800">{{ $stats['utilization']['utilization_pct'] ?? 0 }}%</p>
        </div>
    </div>
    @endif

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200 text-xs">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-3 py-2 text-left font-medium uppercase tracking-wider text-slate-600">ID</th>
                    @if(!empty($isSuperAdmin))
                        <th class="px-3 py-2 text-left font-medium uppercase tracking-wider text-slate-600">Hospital</th>
                    @endif
                    <th class="px-3 py-2 text-left font-medium uppercase tracking-wider text-slate-600">Status</th>
                    <th class="px-3 py-2 text-left font-medium uppercase tracking-wider text-slate-600">Sev.</th>
                    <th class="px-3 py-2 text-left font-medium uppercase tracking-wider text-slate-600">Patient</th>
                    <th class="px-3 py-2 text-left font-medium uppercase tracking-wider text-slate-600">Location</th>
                    <th class="px-3 py-2 text-left font-medium uppercase tracking-wider text-slate-600">Ambulance / Driver</th>
                    <th class="px-3 py-2 text-left font-medium uppercase tracking-wider text-slate-600">ETA</th>
                    <th class="px-3 py-2 text-left font-medium uppercase tracking-wider text-slate-600">Requested</th>
                    <th class="px-3 py-2 text-left font-medium uppercase tracking-wider text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($emergencies as $e)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-3 py-2 font-medium text-slate-900">{{ $e->id }}</td>
                    @if(!empty($isSuperAdmin))
                        <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ $e->hospital->name ?? '—' }}</td>
                    @endif
                    <td class="whitespace-nowrap px-3 py-2">
                        <span class="inline-flex rounded-full px-2 py-0.5 font-medium
                            @if($e->status === 'requested') bg-amber-100 text-amber-800
                            @elseif($e->status === 'assigned') bg-blue-100 text-blue-800
                            @elseif($e->status === 'enroute') bg-indigo-100 text-indigo-800
                            @elseif($e->status === 'arrived') bg-green-100 text-green-800
                            @else bg-slate-100 text-slate-800
                            @endif
                        ">{{ str_replace('_', ' ', $e->status) }}</span>
                    </td>
                    <td class="whitespace-nowrap px-3 py-2">
                        @php $cat = $e->severity_category ?? $e->severity_label; @endphp
                        <span class="rounded px-1.5 py-0.5 font-medium
                            @if($cat === 'Critical' || $cat === 'critical') bg-red-100 text-red-800
                            @elseif($cat === 'Moderate' || $cat === 'medium' || $cat === 'high') bg-amber-100 text-amber-800
                            @else bg-emerald-100 text-emerald-800
                            @endif
                        ">{{ $cat ?? '—' }}</span>
                    </td>
                    <td class="px-3 py-2 text-slate-700">
                        @if($e->patient)
                            <a href="{{ route('hospital.patient', $e->patient) }}" class="font-medium text-red-600 hover:underline">{{ $e->patient->name }}</a>
                            @if($e->patient->phone)<br><span class="text-slate-400">{{ $e->patient->phone }}</span>@endif
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="max-w-[120px] truncate px-3 py-2 text-slate-600" title="{{ $e->address_text }}">{{ $e->address_text ?: $e->latitude . ', ' . $e->longitude }}</td>
                    <td class="px-3 py-2 text-slate-700">
                        @if($e->ambulance)
                            {{ $e->ambulance->plate_number ?? '#' . $e->ambulance->id }}
                            @if($e->ambulance->driver)
                                <br><span class="text-slate-400">{{ $e->ambulance->driver->name }}</span>
                            @endif
                        @else
                            <span class="text-amber-600">Not assigned</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-2 text-slate-600">
                        @if($e->eta_minutes !== null && $e->status !== 'arrived' && $e->status !== 'closed')
                            ~{{ $e->eta_minutes }}m
                        @else
                            —
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-2 text-slate-600">{{ $e->requested_at?->format('M j H:i') }}</td>
                    <td class="whitespace-nowrap px-3 py-2">
                        @if(in_array($e->status, ['requested', 'assigned']))
                            <a href="{{ route('emergency.assign', $e) }}"
                               class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-2 py-1 font-medium text-white hover:bg-blue-700">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                {{ $e->ambulance ? 'Reassign' : 'Assign' }}
                            </a>
                        @else
                            <span class="text-slate-400">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ !empty($isSuperAdmin) ? 10 : 9 }}" class="px-3 py-8 text-center text-slate-500">No emergencies match the filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection