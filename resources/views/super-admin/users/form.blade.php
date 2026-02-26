@extends('layouts.app')
@section('title', $user ? 'Edit user' : 'Add user')
@section('content')
<div class="max-w-xl">
    <h1 class="text-2xl font-bold text-slate-800 mb-4">{{ $user ? 'Edit user' : 'Add user' }}</h1>
    <form method="post" action="{{ $user ? route('super-admin.users.update', $user) : route('super-admin.users.store') }}" class="space-y-4 rounded-lg border bg-white p-4 shadow">
        @csrf
        @if($user) @method('PUT') @endif
        <div><label class="block text-sm font-medium text-slate-700">Name</label><input type="text" name="name" value="{{ old('name', $user?->name) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div>
        <div><label class="block text-sm font-medium text-slate-700">Email</label><input type="email" name="email" value="{{ old('email', $user?->email) }}" required class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div>
        <div><label class="block text-sm font-medium text-slate-700">Password {{ $user ? '(leave blank to keep)' : '' }}</label><input type="password" name="password" {{ $user ? '' : 'required' }} class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div>
        <div><label class="block text-sm font-medium text-slate-700">Confirm password</label><input type="password" name="password_confirmation" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div>
        <div><label class="block text-sm font-medium text-slate-700">Role</label><select name="role" required class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5">@foreach(['patient','driver','hospital_admin','super_admin'] as $r)<option value="{{ $r }}" {{ old('role', $user?->role) === $r ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$r)) }}</option>@endforeach</select></div>
        <div><label class="block text-sm font-medium text-slate-700">Phone</label><input type="text" name="phone" value="{{ old('phone', $user?->phone) }}" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5" /></div>
        <div><label class="block text-sm font-medium text-slate-700">Hospital</label><select name="hospital_id" class="mt-1 w-full rounded-md border border-slate-300 px-2 py-1.5"><option value="">—</option>@foreach($hospitals as $h)<option value="{{ $h->id }}" {{ old('hospital_id', $user?->hospital_id) == $h->id ? 'selected' : '' }}>{{ $h->name }}</option>@endforeach</select></div>
        <div class="flex gap-2"><button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-white hover:bg-red-700">Save</button><a href="{{ route('super-admin.users.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-slate-700 hover:bg-slate-50">Cancel</a></div>
    </form>
</div>
@endsection
