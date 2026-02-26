@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="mb-4 text-xl font-semibold">Login to AfyaRescue</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-4">
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500" />
            @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
            <input id="password" type="password" name="password" required
                class="w-full rounded-md border border-slate-300 px-3 py-2 shadow-sm focus:border-red-500 focus:ring-red-500" />
            @error('password') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4 flex items-center">
            <input id="remember" type="checkbox" name="remember" class="rounded border-slate-300 text-red-600 focus:ring-red-500" />
            <label for="remember" class="ml-2 text-sm text-slate-600">Remember me</label>
        </div>
        <button type="submit" class="w-full rounded-md bg-red-600 px-4 py-2 font-medium text-white hover:bg-red-700">Login</button>
    </form>
    <p class="mt-4 text-center text-sm text-slate-600">
        Don't have an account? <a href="{{ route('register') }}" class="font-medium text-red-600 hover:underline">Register</a>
    </p>
</div>
@endsection
