@extends('layouts.app')
@section('title', 'Manage Ambulances')
@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">Ambulances</h1>
        <a href="{{ route('super-admin.ambulances.create') }}" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Add ambulance</a>
    </div>
    <form method="get" class="flex gap-2 flex-wrap">
        <select name="hospital_id" class="rounded-md border border-slate-300 px-2 py-1 text-sm">
            <option value="">All hospitals</option>
            @foreach($hospitals as $h)
                <option value="{{ $h->id }}" {{ request('hospital_id') == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-md border border-slate-300 px-2 py-1 text-sm">
            <option value="">All statuses</option>
            <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
            <option value="busy" {{ request('status') === 'busy' ? 'selected' : '' }}>Busy</option>
            <option value="maintenance" {{ request('status') === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
        </select>
        <button type="submit" class="rounded-md bg-slate-200 px-3 py-1 text-sm">Filter</button>
    </form>
    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Plate</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Hospital</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Driver</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($ambulances as $a)
                <tr>
                    <td class="px-4 py-2 text-sm">{{ $a->plate_number ?? '—' }}</td>
                    <td class="px-4 py-2 text-sm">{{ $a->hospital->name ?? '—' }}</td>
                    <td class="px-4 py-2 text-sm">{{ $a->driver->name ?? '—' }}</td>
                    <td class="px-4 py-2 text-sm">{{ $a->status }}</td>
                    <td class="px-4 py-2"><a href="{{ route('super-admin.ambulances.edit', $a) }}" class="text-red-600 hover:underline">Edit</a></td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-4 text-center text-slate-500">No ambulances.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $ambulances->withQueryString()->links() }}
</div>
@endsection
