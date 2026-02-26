@extends('layouts.app')
@section('title', 'Manage Hospitals')
@section('content')
<div class="space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-slate-800">Hospitals</h1>
        <a href="{{ route('super-admin.hospitals.create') }}" class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Add hospital</a>
    </div>
    <div class="overflow-x-auto rounded-lg border border-slate-200 bg-white shadow">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50"><tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Name</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Level</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Ambulances</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Emergencies</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-slate-600">Actions</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-200">
                @forelse($hospitals as $h)
                <tr><td class="px-4 py-2 text-sm">{{ $h->name }}</td><td class="px-4 py-2 text-sm">{{ $h->level ?? 1 }}</td><td class="px-4 py-2 text-sm">{{ $h->ambulances_count }}</td><td class="px-4 py-2 text-sm">{{ $h->emergencies_count }}</td><td class="px-4 py-2"><a href="{{ route('super-admin.hospitals.edit', $h) }}" class="text-red-600 hover:underline">Edit</a></td></tr>
                @empty
                <tr><td colspan="5" class="px-4 py-4 text-center text-slate-500">No hospitals.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $hospitals->links() }}
</div>
@endsection
