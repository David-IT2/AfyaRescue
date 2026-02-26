@extends('layouts.app')
@section('title', $hospital ? 'Edit hospital' : 'Add hospital')
@section('content')
<div class="max-w-xl">
    <h1 class="text-2xl font-bold text-slate-800 mb-4">{{ $hospital ? 'Edit hospital' : 'Add hospital' }}</h1>
    <form method="post" action="{{ $hospital ? route('super-admin.hospitals.update', $hospital) : route('super-admin.hospitals.store') }}" class="space-y-4 rounded-lg border bg-white p-4 shadow">
        @csrf
        @if($hospital) @method('PUT') @endif
        <div><label class="block text-sm font-medium text-slate-700">Name</label><input type="text" name="name" value="{{ old('name', $hospital?->name) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div>
        <div><label class="block text-sm font-medium text-slate-700">Address</label><input type="text" name="address" value="{{ old('address', $hospital?->address) }}" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div>
        <div><label class="block text-sm font-medium text-slate-700">Phone</label><input type="text" name="phone" value="{{ old('phone', $hospital?->phone) }}" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div>
        <div><label class="block text-sm font-medium text-slate-700">Level (1-3)</label><input type="number" name="level" min="1" max="3" value="{{ old('level', $hospital?->level ?? 1) }}" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div>
        @if($hospital)<div><label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $hospital->is_active) ? 'checked' : '' }} /> Active</label></div>@endif
        <div class="flex gap-2"><button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Save</button><a href="{{ route('super-admin.hospitals.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50">Cancel</a></div>
    </form>
</div>
@endsection
