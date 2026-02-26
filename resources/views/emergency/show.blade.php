@extends('layouts.app')
@section('title', 'Emergency #' . $emergency->id)
@section('content')
<div class="mx-auto max-w-2xl">
    <h1 class="mb-4 text-2xl font-bold text-slate-800">Emergency #{{ $emergency->id }}</h1>
    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-slate-500">Status</dt>
                <dd class="mt-1 font-semibold capitalize">{{ str_replace('_', ' ', $emergency->status) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-500">Severity</dt>
                <dd class="mt-1">
                    <span class="badge badge-{{ $emergency->severity_label }} rounded px-2 py-0.5 text-sm font-medium">
                        {{ $emergency->severity_label }} ({{ $emergency->severity_score }}/10)
                    </span>
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-slate-500">Address / Location</dt>
                <dd class="mt-1">{{ $emergency->address_text ?: $emergency->latitude . ', ' . $emergency->longitude }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-500">Hospital</dt>
                <dd class="mt-1">{{ $emergency->hospital->name }}</dd>
            </div>
            @if($emergency->ambulance)
            <div>
                <dt class="text-sm font-medium text-slate-500">Assigned ambulance</dt>
                <dd class="mt-1">{{ $emergency->ambulance->plate_number ?? 'Ambulance #' . $emergency->ambulance->id }}</dd>
            </div>
            @if($emergency->ambulance->driver)
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-slate-500">Driver</dt>
                <dd class="mt-1">{{ $emergency->ambulance->driver->name }} – {{ $emergency->ambulance->driver->phone ?? 'N/A' }}</dd>
            </div>
            @endif
            @else
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-slate-500">Ambulance</dt>
                <dd class="mt-1 text-amber-600">No ambulance assigned yet. One will be assigned when available.</dd>
            </div>
            @endif
            <div>
                <dt class="text-sm font-medium text-slate-500">Requested at</dt>
                <dd class="mt-1">{{ $emergency->requested_at?->format('M j, Y H:i') }}</dd>
            </div>
            @if($emergency->assigned_at)
            <div>
                <dt class="text-sm font-medium text-slate-500">Assigned at</dt>
                <dd class="mt-1">{{ $emergency->assigned_at->format('M j, Y H:i') }}</dd>
            </div>
            @endif
        </dl>
        <div class="mt-6">
            <a href="{{ route('emergency.create') }}" class="text-red-600 hover:underline">Submit another emergency</a>
            ·
            <a href="{{ route('dashboard.or.home') }}" class="text-slate-600 hover:underline">Dashboard</a>
        </div>
    </div>
</div>
@endsection
