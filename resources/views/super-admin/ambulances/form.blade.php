@extends('layouts.app')
@section('title', $ambulance ? 'Edit ambulance' : 'Add ambulance')
@section('content')
<div class="max-w-xl">
    <h1 class="text-2xl font-bold text-slate-800 mb-4">{{ $ambulance ? 'Edit ambulance' : 'Add ambulance' }}</h1>
    <form method="post" action="{{ $ambulance ? route('super-admin.ambulances.update', $ambulance) : route('super-admin.ambulances.store') }}" class="space-y-4 rounded-lg border bg-white p-4 shadow">
        @csrf
        @if($ambulance) @method('PUT') @endif
        <div><label class="block text-sm font-medium text-slate-700">Hospital</label><select name="hospital_id" required class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5">@foreach($hospitals as $h)<option value="{{ $h->id }}" {{ old('hospital_id', $ambulance?->hospital_id) == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>@endforeach</select></div>
        <div><label class="block text-sm font-medium text-slate-700">Driver</label><select name="driver_id" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5"><option value="">—</option>@foreach($drivers as $d)<option value="{{ $d->id }}" {{ old('driver_id', $ambulance?->driver_id) == $d->id ? 'selected' : '' }}>{{ $d->name }} ({{ $d->hospital_id ? 'H#' . $d->hospital_id : '—' }})</option>@endforeach</select></div>
        <div><label class="block text-sm font-medium text-slate-700">Plate number</label><input type="text" name="plate_number" value="{{ old('plate_number', $ambulance?->plate_number) }}" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div>
        <div><label class="block text-sm font-medium text-slate-700">Status</label><select name="status" required class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5"><option value="available" {{ old('status', $ambulance?->status) === 'available' ? 'selected' : '' }}>Available</option><option value="busy" {{ old('status', $ambulance?->status) === 'busy' ? 'selected' : '' }}>Busy</option><option value="maintenance" {{ old('status', $ambulance?->status) === 'maintenance' ? 'selected' : '' }}>Maintenance</option></select></div>
        <div class="grid grid-cols-2 gap-4"><div><label class="block text-sm font-medium text-slate-700">Latitude</label><input type="text" name="latitude" value="{{ old('latitude', $ambulance?->latitude) }}" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div><div><label class="block text-sm font-medium text-slate-700">Longitude</label><input type="text" name="longitude" value="{{ old('longitude', $ambulance?->longitude) }}" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div></div>
        <div class="flex gap-2"><button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Save</button><a href="{{ route('super-admin.ambulances.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50">Cancel</a></div>
    </form>
</div>
@endsection
