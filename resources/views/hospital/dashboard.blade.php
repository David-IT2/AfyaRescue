@extends('layouts.app')
@section('title', 'Hospital Dashboard')
@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-2xl font-bold text-slate-800">Incoming emergencies</h1>
        <div class="flex flex-wrap items-center gap-3">
            @if(!empty($isSuperAdmin) && $hospitals && $hospitals->isNotEmpty())
                <form method="get" class="flex items-center gap-2" id="filter-form">
                    <input type="hidden" name="patient" value="{{ request('patient') }}" />
                    <input type="hidden" name="patient_id" value="{{ request('patient_id') }}" />
                    <input type="hidden" name="status" value="{{ request('status') }}" />
                    <input type="hidden" name="from" value="{{ request('from') }}" />
                    <input type="hidden" name="to" value="{{ request('to') }}" />
                    <label for="hospital_id" class="text-sm text-slate-600">Hospital:</label>
                    <select name="hospital_id" id="hospital_id" onchange="this.form.submit()" class="rounded-md border border-slate-300 px-2 py-1 text-sm">
                        <option value="">All</option>
                        @foreach($hospitals as $h)
                            <option value="{{ $h->id }}" {{ request('hospital_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <a href="{{ route('hospital.export') }}?{{ request()->getQueryString() }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Export CSV</a>
            <a href="{{ route('hospital.report') }}?{{ request()->getQueryString() }}" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50" target="_blank">Report (Print/PDF)</a>
            <span class="text-xs text-slate-500">Reload for latest</span>
        </div>
    </div>

    {{-- Filters: patient search, status --}}
    <form method="get" class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="hospital_id" value="{{ request('hospital_id') }}" />
        <input type="hidden" name="patient_id" value="{{ request('patient_id') }}" />
        <div class="flex flex-wrap items-end gap-4">
            <div class="min-w-[140px]">
                <label for="patient" class="mb-1 block text-sm font-medium text-slate-700">Patient</label>
                <input type="text" name="patient" id="patient" value="{{ request('patient') }}" placeholder="Name or phone" class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            </div>
            <div>
                <label for="status" class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                <select name="status" id="status" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                    <option value="">All</option>
                    @foreach(['requested','assigned','enroute','arrived','closed'] as $s)
                        <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="from" class="mb-1 block text-sm font-medium text-slate-700">From</label>
                <input type="date" name="from" id="from" value="{{ request('from') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            </div>
            <div>
                <label for="to" class="mb-1 block text-sm font-medium text-slate-700">To</label>
                <input type="date" name="to" id="to" value="{{ request('to') }}" class="rounded-md border border-slate-300 px-2 py-1.5 text-sm" />
            </div>
            <button type="submit" class="rounded-md bg-slate-700 px-4 py-1.5 text-sm font-medium text-white hover:bg-slate-800">Filter</button>
        </div>
    </form>

    {{-- Analytics --}}
    @if(!empty($stats))
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Avg. assignment time</p>
            <p class="text-xl font-semibold text-slate-800">{{ $stats['avg_assignment_min'] !== null ? $stats['avg_assignment_min'] . ' min' : '—' }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Avg. en route time</p>
            <p class="text-xl font-semibold text-slate-800">{{ $stats['avg_enroute_min'] !== null ? $stats['avg_enroute_min'] . ' min' : '—' }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">By severity</p>
            <p class="text-sm font-medium text-slate-800">
                @foreach($stats['by_severity'] ?? [] as $cat => $count)
                    <span class="mr-2">{{ $cat ?? 'N/A' }}: {{ $count }}</span>
                @endforeach
                @if(empty($stats['by_severity']))
                    —
                @endif
            </p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Ambulance utilization</p>
            <p class="text-xl font-semibold text-slate-800">{{ $stats['utilization']['utilization_pct'] ?? 0 }}%</p>
        </div>
    </div>
    @endif

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">ID</th>
                    @if(!empty($isSuperAdmin))
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Hospital</th>
                    @endif
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Severity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Patient</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Location</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Ambulance / Driver</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">ETA</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Requested</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($emergencies as $e)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $e->id }}</td>
                    @if(!empty($isSuperAdmin))
                        <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{{ $e->hospital->name ?? '—' }}</td>
                    @endif
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                            @if($e->status === 'requested') bg-amber-100 text-amber-800
                            @elseif($e->status === 'assigned') bg-blue-100 text-blue-800
                            @elseif($e->status === 'enroute') bg-indigo-100 text-indigo-800
                            @elseif($e->status === 'arrived') bg-green-100 text-green-800
                            @else bg-slate-100 text-slate-800
                            @endif
                        ">{{ str_replace('_', ' ', $e->status) }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3">
                        @php $cat = $e->severity_category ?? $e->severity_label; @endphp
                        <span class="rounded px-2 py-0.5 text-xs font-medium
                            @if($cat === 'Critical' || $cat === 'critical') bg-red-100 text-red-800
                            @elseif($cat === 'Moderate' || $cat === 'medium' || $cat === 'high') bg-amber-100 text-amber-800
                            @else bg-emerald-100 text-emerald-800
                            @endif
                        ">{{ $cat ?? '—' }}</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-700">
                        @if($e->patient)
                            <a href="{{ route('hospital.patient', $e->patient) }}" class="font-medium text-red-600 hover:underline">{{ $e->patient->name }}</a>
                            @if($e->patient->phone)
                                <br><span class="text-slate-500">{{ $e->patient->phone }}</span>
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="max-w-[160px] truncate px-4 py-3 text-sm text-slate-600" title="{{ $e->address_text }}">{{ $e->address_text ?: $e->latitude . ', ' . $e->longitude }}</td>
                    <td class="px-4 py-3 text-sm text-slate-700">
                        @if($e->ambulance)
                            {{ $e->ambulance->plate_number ?? '#' . $e->ambulance->id }}
                            @if($e->ambulance->driver)
                                <br><span class="text-slate-500">{{ $e->ambulance->driver->name }} – {{ $e->ambulance->driver->phone ?? 'N/A' }}</span>
                            @endif
                        @else
                            <span class="text-amber-600">Not assigned</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">
                        @if($e->eta_minutes !== null && $e->status !== 'arrived' && $e->status !== 'closed')
                            ~{{ $e->eta_minutes }} min
                        @else
                            —
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $e->requested_at?->format('M j H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ !empty($isSuperAdmin) ? 9 : 8 }}" class="px-4 py-8 text-center text-slate-500">No emergencies match the filters.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
