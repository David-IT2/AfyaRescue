@extends('layouts.app')
@section('title', 'Register')
@section('content')
<div class="mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="mb-4 text-xl font-semibold">Register for AfyaRescue</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="mb-4">
            <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500" />
            @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500" />
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
            <input id="password" type="password" name="password" required class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500" />
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Confirm Password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500" />
        </div>
        <div class="mb-4">
            <label for="role" class="mb-1 block text-sm font-medium text-slate-700">Role</label>
            <select id="role" name="role" required class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">
                <option value="patient" {{ old('role') === 'patient' ? 'selected' : '' }}>Patient</option>
                <option value="driver" {{ old('role') === 'driver' ? 'selected' : '' }}>Driver</option>
                <option value="hospital_admin" {{ old('role') === 'hospital_admin' ? 'selected' : '' }}>Hospital Admin</option>
                <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>Super Admin</option>
            </select>
            @error('role') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">Phone (optional)</label>
            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500" />
        </div>
        <div class="mb-4" id="hospital-field" style="{{ in_array(old('role'), ['driver', 'hospital_admin']) ? '' : 'display:none' }}">
            <label for="hospital_id" class="mb-1 block text-sm font-medium text-slate-700">Hospital</label>
            <select id="hospital_id" name="hospital_id" class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500">
                <option value="">-- Select --</option>
                @foreach($hospitals as $h)
                <option value="{{ $h->id }}" {{ (int) old('hospital_id') === $h->id ? 'selected' : '' }}>{{ $h->name }}</option>
                @endforeach
            </select>
            @error('hospital_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <button type="submit" class="w-full rounded-md bg-red-600 px-4 py-2 font-medium text-white hover:bg-red-700">Register</button>
    </form>
    <p class="mt-4 text-center text-sm text-slate-600">
        Already have an account? <a href="{{ route('login') }}" class="font-medium text-red-600 hover:underline">Login</a>
    </p>
</div>
<script>
document.getElementById('role').addEventListener('change', function() {
    var show = ['driver', 'hospital_admin'].indexOf(this.value) !== -1;
    document.getElementById('hospital-field').style.display = show ? 'block' : 'none';
});
</script>
@endsection
