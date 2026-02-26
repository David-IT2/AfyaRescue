@extends('layouts.app')
@section('title', 'Hospital Dashboard')
@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-800">Incoming emergencies</h1>
        <div class="flex items-center gap-4">
            @if(!empty($isSuperAdmin) && $hospitals && $hospitals->isNotEmpty())
                <form method="get" class="flex items-center gap-2">
                    <label for="hospital_id" class="text-sm text-slate-600">Filter by hospital:</label>
                    <select name="hospital_id" id="hospital_id" onchange="this.form.submit()" class="rounded-md border border-slate-300 px-2 py-1 text-sm">
                        <option value="">All hospitals</option>
                        @foreach($hospitals as $h)
                            <option value="{{ $h->id }}" {{ request('hospital_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                        @endforeach
                    </select>
                </form>
            @endif
            <span class="text-sm text-slate-500">Reload page for latest</span>
        </div>
    </div>
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow">
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
                        <span class="badge badge-{{ $e->severity_label }} rounded px-2 py-0.5 text-xs font-medium">
                            {{ $e->severity_label }} ({{ $e->severity_score }})
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-700">
                        {{ $e->patient->name ?? 'N/A' }}
                        @if($e->patient && $e->patient->phone)
                            <br><span class="text-slate-500">{{ $e->patient->phone }}</span>
                        @endif
                    </td>
                    <td class="max-w-[180px] truncate px-4 py-3 text-sm text-slate-600" title="{{ $e->address_text }}">{{ $e->address_text ?: $e->latitude . ', ' . $e->longitude }}</td>
                    <td class="px-4 py-3 text-sm text-slate-700">
                        @if($e->ambulance)
                            {{ $e->ambulance->plate_number ?? 'Ambulance #' . $e->ambulance->id }}
                            @if($e->ambulance->driver)
                                <br><span class="text-slate-500">{{ $e->ambulance->driver->name }} – {{ $e->ambulance->driver->phone ?? 'N/A' }}</span>
                            @endif
                        @else
                            <span class="text-amber-600">Not assigned</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $e->requested_at?->format('M j H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ !empty($isSuperAdmin) ? 8 : 7 }}" class="px-4 py-8 text-center text-slate-500">No emergencies at the moment.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
