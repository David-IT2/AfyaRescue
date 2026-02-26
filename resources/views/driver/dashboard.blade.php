@extends('layouts.app')
@section('title', 'My Assignments')
@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-slate-800">My emergency assignments</h1>
    @forelse($emergencies as $e)
    <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-slate-900">Emergency #{{ $e->id }}</span>
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium
                        @if($e->status === 'assigned') bg-blue-100 text-blue-800
                        @elseif($e->status === 'enroute') bg-indigo-100 text-indigo-800
                        @elseif($e->status === 'arrived') bg-green-100 text-green-800
                        @else bg-slate-100 text-slate-800
                        @endif
                    ">{{ str_replace('_', ' ', $e->status) }}</span>
                    <span class="rounded px-2 py-0.5 text-xs font-medium badge badge-{{ $e->severity_label }}">{{ $e->severity_label }}</span>
                </div>
                <p class="mt-1 text-sm text-slate-600">{{ $e->address_text ?: $e->latitude . ', ' . $e->longitude }}</p>
                @if($e->patient)
                    <p class="mt-1 text-sm text-slate-700">Patient: {{ $e->patient->name }} – {{ $e->patient->phone ?? 'N/A' }}</p>
                @endif
                @if($e->hospital)
                    <p class="text-sm text-slate-500">Hospital: {{ $e->hospital->name }}</p>
                @endif
            </div>
            <div>
                @if($e->status === 'assigned')
                    <form method="post" action="{{ route('driver.status', $e) }}" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="enroute" />
                        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700">Mark en route</button>
                    </form>
                @endif
                @if($e->status === 'enroute')
                    <form method="post" action="{{ route('driver.status', $e) }}" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="arrived" />
                        <button type="submit" class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700">Mark arrived</button>
                    </form>
                @endif
                @if(in_array($e->status, ['enroute', 'arrived']))
                    <form method="post" action="{{ route('driver.status', $e) }}" class="inline ml-1">
                        @csrf
                        <input type="hidden" name="status" value="closed" />
                        <button type="submit" class="rounded-md border border-slate-300 px-3 py-1.5 text-sm text-slate-700 hover:bg-slate-50">Close</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
    @empty
    <p class="rounded-lg border border-slate-200 bg-slate-50 p-6 text-center text-slate-600">No assignments at the moment.</p>
    @endforelse
</div>
@endsection
