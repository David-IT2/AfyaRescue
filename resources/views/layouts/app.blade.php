<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AfyaRescue') - Emergency Response</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif; }
        .badge-critical { background: #dc2626; color: #fff; }
        .badge-high { background: #ea580c; color: #fff; }
        .badge-medium { background: #ca8a04; color: #fff; }
        .badge-low { background: #16a34a; color: #fff; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <nav class="border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-14 justify-between">
                <div class="flex items-center">
                    <a href="{{ url('/') }}" class="text-xl font-bold text-red-600">AfyaRescue</a>
                    @auth
                        <span class="ml-4 text-sm text-slate-500">({{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }})</span>
                    @endauth
                </div>
                <div class="flex items-center gap-4">
                    @auth
                        @if(auth()->user()->hasRole('patient'))
                            <a href="{{ route('emergency.create') }}" class="rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">New Emergency</a>
                        @endif
                        @if(auth()->user()->hasRole('driver'))
                            <a href="{{ route('driver.dashboard') }}" class="rounded-md bg-slate-700 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">My Assignments</a>
                        @endif
                        @if(auth()->user()->hasRole('hospital_admin', 'super_admin'))
                            <a href="{{ route('hospital.dashboard') }}" class="rounded-md bg-slate-700 px-3 py-2 text-sm font-medium text-white hover:bg-slate-800">Hospital Dashboard</a>
                        @endif
                        @if(auth()->user()->hasRole('super_admin'))
                            <a href="{{ route('super-admin.health') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">System health</a>
                            <a href="{{ route('super-admin.users.index') }}" class="rounded-md border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">Manage</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-slate-600 hover:text-slate-900">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Login</a>
                        <a href="{{ route('register') }}" class="rounded-md bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-md bg-red-50 p-4 text-red-800">{{ session('error') }}</div>
        @endif
        @yield('content')
    </main>
</body>
</html>
