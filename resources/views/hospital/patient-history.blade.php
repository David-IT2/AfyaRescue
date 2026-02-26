@extends('layouts.app')
@section('title', 'Patient history – ' . $patient->name)
@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('hospital.dashboard') }}" class="text-sm text-slate-500 hover:text-slate-700">← Dashboard</a>
            <h1 class="mt-1 text-2xl font-bold text-slate-800">Patient history</h1>
            <p class="mt-1 text-slate-600">{{ $patient->name }} @if($patient->phone)<span class="text-slate-500">{{ $patient->phone }}</span>@endif</p>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Severity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Hospital</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Address</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Requested</th>
                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 bg-white">
                @forelse($emergencies as $e)
                <tr class="hover:bg-slate-50">
                    <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-900">{{ $e->id }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                            @if($e->status === 'requested') bg-amber-100 text-amber-800
                            @elseif($e->status === 'assigned') bg-blue-100 text-blue-800
                            @elseif($e->status === 'enroute') bg-indigo-100 text-indigo-800
                            @elseif($e->status === 'arrived') bg-green-100 text-green-800
                            @else bg-slate-100 text-slate-800
                            @endif
                        ">{{ $e->status }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm">{{ $e->severity_category ?? $e->severity_label ?? '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700">{{ $e->hospital->name ?? '—' }}</td>
                    <td class="max-w-[180px] truncate px-4 py-3 text-sm text-slate-600" title="{{ $e->address_text }}">{{ $e->address_text ?: '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-600">{{ $e->requested_at?->format('M j, Y H:i') }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <a href="{{ route('hospital.dashboard') }}?patient_id={{ $patient->id }}" class="text-red-600 hover:underline">View in dashboard</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-slate-500">No emergencies found for this patient.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
