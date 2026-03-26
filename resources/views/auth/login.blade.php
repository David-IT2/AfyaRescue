@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="mx-auto max-w-md rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h1 class="mb-4 text-xl font-semibold text-slate-800">Login to AfyaRescue</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="mb-4">
            <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm focus:border-[#ff0013] focus:ring-[#ff0013]" />
            @error('email') <p class="mt-1 text-sm" style="color:#ff0013">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4">
            <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
            <div class="relative">
                <input id="password" type="password" name="password" required
                    class="w-full rounded-md border border-slate-300 px-3 py-2 pr-10 text-slate-900 shadow-sm focus:border-[#ff0013] focus:ring-[#ff0013]" />
                <button type="button" id="toggle-password"
                    class="absolute inset-y-0 right-2 flex items-center text-xs text-slate-500 hover:text-slate-700">
                    Show
                </button>
            </div>
            @error('password') <p class="mt-1 text-sm" style="color:#ff0013">{{ $message }}</p> @enderror
        </div>
        <div class="mb-4 flex items-center">
            <input id="remember" type="checkbox" name="remember" class="rounded border-slate-300 focus:ring-[#ff0013]" style="accent-color:#ff0013" />
            <label for="remember" class="ml-2 text-sm text-slate-600">Remember me</label>
        </div>
        <button type="submit" class="w-full rounded-md px-4 py-2 font-medium text-white" style="background-color:#ff0013" onmouseover="this.style.backgroundColor='#cc0010'" onmouseout="this.style.backgroundColor='#ff0013'">Login</button>
    </form>
    <p class="mt-4 text-center text-sm text-slate-600">
        Don't have an account? <a href="{{ route('register') }}" class="font-medium hover:underline" style="color:#ff0013">Register</a>
    </p>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('password');
    const toggle = document.getElementById('toggle-password');
    if (input && toggle) {
        toggle.addEventListener('click', function () {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            toggle.textContent = isPassword ? 'Hide' : 'Show';
        });
    }
});
</script>
@endsection