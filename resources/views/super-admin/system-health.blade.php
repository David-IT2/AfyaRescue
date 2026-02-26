@extends('layouts.app')
@section('title', 'System Health')
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-slate-800">System Health</h1>
        <div class="flex gap-2">
            <a href="{{ route('hospital.dashboard') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Hospital Dashboard</a>
            <a href="{{ route('super-admin.users.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Manage Users</a>
            <a href="{{ route('super-admin.hospitals.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Hospitals</a>
            <a href="{{ route('super-admin.ambulances.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Ambulances</a>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Active emergencies</p>
            <p class="text-2xl font-semibold text-slate-800">{{ $active_emergencies }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Ambulances available</p>
            <p class="text-2xl font-semibold text-emerald-600">{{ $available_ambulances }} / {{ $total_ambulances }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Ambulances busy</p>
            <p class="text-2xl font-semibold text-slate-800">{{ $busy_ambulances }}</p>
        </div>
        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-sm text-slate-500">Avg. assignment time</p>
            <p class="text-xl font-semibold text-slate-800">{{ $stats['avg_assignment_min'] !== null ? $stats['avg_assignment_min'] . ' min' : '—' }}</p>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <h2 class="mb-3 text-lg font-semibold text-slate-800">By severity (all time)</h2>
        <p class="text-sm text-slate-700">
            @foreach($stats['by_severity'] ?? [] as $cat => $count)
                <span class="mr-3">{{ $cat ?? 'N/A' }}: <strong>{{ $count }}</strong></span>
            @endforeach
            @if(empty($stats['by_severity']))
                No data yet.
            @endif
        </p>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Hospital</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Active emergencies</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Ambulances</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($hospitals as $h)
                <tr>
                    <td class="px-4 py-2 text-sm font-medium text-slate-800">{{ $h->name }}</td>
                    <td class="px-4 py-2 text-sm">{{ $h->active_emergencies }}</td>
                    <td class="px-4 py-2 text-sm">{{ $h->ambulance_count }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('hospital.dashboard') }}?hospital_id={{ $h->id }}" class="text-red-600 hover:underline">View dashboard</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-4 py-4 text-center text-slate-500">No hospitals.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
