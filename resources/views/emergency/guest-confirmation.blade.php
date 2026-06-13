@extends('layouts.app')
@section('title', 'Emergency Submitted')
@section('content')
<div class="mx-auto max-w-2xl space-y-6">
    <div class="rounded-lg border border-emerald-700/50 bg-emerald-900/20 p-6">
        <h1 class="text-2xl font-bold text-white">Emergency request received</h1>
        <p class="mt-2 text-slate-300">Your request has been submitted. Help is being coordinated now.</p>
    </div>

    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-6">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-sm font-medium text-slate-400">Reference</dt>
                <dd class="mt-1 font-semibold text-white">#{{ $emergency->id }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-400">Status</dt>
                <dd class="mt-1 capitalize text-white">{{ str_replace('_', ' ', $emergency->status) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-400">Severity</dt>
                <dd class="mt-1 text-white">{{ $emergency->severity_category ?? $emergency->severity_label }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-slate-400">Hospital</dt>
                <dd class="mt-1 text-white">{{ $emergency->hospital->name ?? '—' }}</dd>
            </div>
            @if($emergency->eta_minutes)
            <div>
                <dt class="text-sm font-medium text-slate-400">Estimated arrival</dt>
                <dd class="mt-1 font-bold text-amber-400">~{{ $emergency->eta_minutes }} min</dd>
            </div>
            @endif
            <div class="sm:col-span-2">
                <dt class="text-sm font-medium text-slate-400">Location</dt>
                <dd class="mt-1 text-white">{{ $emergency->address_text ?: $emergency->latitude . ', ' . $emergency->longitude }}</dd>
            </div>
        </dl>
    </div>

    @if(($emergency->severity_category ?? '') === 'Critical')
    <div class="rounded-lg border border-red-700/50 bg-red-900/20 p-4">
        <p class="text-sm font-semibold text-red-300">If you are in immediate danger, also call your local emergency number.</p>
    </div>
    @endif

    <div class="rounded-lg border border-slate-700 bg-slate-800/50 p-4 text-sm text-slate-300">
        <p class="font-medium text-white">Want to track this request live?</p>
        <p class="mt-1">Create a free account or log in to see status updates, ambulance assignment, and live location on a map.</p>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('register') }}" class="rounded-md bg-red-600 px-4 py-2 font-medium text-white hover:bg-red-700">Register</a>
            <a href="{{ route('login') }}" class="rounded-md border border-slate-500 px-4 py-2 text-slate-200 hover:bg-slate-700">Log in</a>
        </div>
    </div>

    <a href="{{ route('home') }}" class="inline-block text-sm text-slate-400 hover:text-white">← Back to home</a>
</div>
@endsection
