<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Afya Rescue – Emergency Response & Ambulance Coordination</title>
    <meta name="description" content="Connect emergencies with the right ambulance and hospital. Request help, coordinate drivers, and streamline hospital readiness.">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Figtree', ui-sans-serif, system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <header class="relative z-10 border-b border-slate-800/50 bg-slate-950/80 backdrop-blur-sm">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="flex items-center gap-2">
                <span class="text-xl font-bold tracking-tight text-white">Afya<span class="text-red-500">Rescue</span></span>
            </a>
            <nav class="flex items-center gap-3 sm:gap-6">
                @auth
                    <a href="{{ url('/dashboard') }}" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-slate-200 transition hover:bg-slate-700 hover:text-white">Dashboard</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-slate-400 transition hover:text-white">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-300 transition hover:text-white">Log in</a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-500">Register</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="relative z-10">
        {{-- Hero --}}
        <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6 sm:py-28 lg:px-8 lg:py-36">
            <div class="mx-auto max-w-3xl text-center">
                <h1 class="text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl">
                    Get help when it
                    <span class="text-red-500">matters most</span>
                </h1>
                <p class="mt-6 text-lg leading-relaxed text-slate-400 sm:text-xl">
                    Afya Rescue connects emergencies with the right ambulance and hospital. Request help in seconds, track status in real time, and keep everyone in sync—from patient to driver to hospital.
                </p>
                @guest
                <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="inline-flex items-center rounded-lg bg-red-600 px-6 py-3 text-base font-semibold text-white shadow-lg transition hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-slate-950">Get started</a>
                    <a href="{{ route('login') }}" class="inline-flex items-center rounded-lg border border-slate-600 bg-slate-800/50 px-6 py-3 text-base font-semibold text-slate-200 transition hover:border-slate-500 hover:bg-slate-800 hover:text-white">Log in</a>
                </div>
                @endguest
            </div>
        </section>

        {{-- Roles / How it works --}}
        <section class="border-t border-slate-800/50 bg-slate-900/30 py-20 sm:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-center text-2xl font-bold text-white sm:text-3xl">Built for every part of the chain</h2>
                <p class="mx-auto mt-3 max-w-2xl text-center text-slate-400">One platform for patients, drivers, and hospitals.</p>
                <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-2xl border border-slate-700/50 bg-slate-800/40 p-6 backdrop-blur sm:p-8">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-500/10 text-red-400">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Patients & callers</h3>
                        <p class="mt-2 text-slate-400">Submit an emergency request with location and triage details. Get a clear status and ETA so you know help is on the way.</p>
                        @guest
                        <a href="{{ route('register') }}" class="mt-4 inline-block text-sm font-medium text-red-400 transition hover:text-red-300">Request help →</a>
                        @endguest
                    </div>
                    <div class="rounded-2xl border border-slate-700/50 bg-slate-800/40 p-6 backdrop-blur sm:p-8">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10 text-amber-400">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Drivers</h3>
                        <p class="mt-2 text-slate-400">See your assignments, update status en route, and keep dispatch and hospitals informed with live location.</p>
                        @guest
                        <a href="{{ route('login') }}" class="mt-4 inline-block text-sm font-medium text-amber-400 transition hover:text-amber-300">Driver login →</a>
                        @endguest
                    </div>
                    <div class="rounded-2xl border border-slate-700/50 bg-slate-800/40 p-6 backdrop-blur sm:p-8 sm:col-span-2 lg:col-span-1">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="mt-4 text-lg font-semibold text-white">Hospitals</h3>
                        <p class="mt-2 text-slate-400">View incoming emergencies, triage responses, and patient history. Export reports and stay ready for arrival.</p>
                        @guest
                        <a href="{{ route('login') }}" class="mt-4 inline-block text-sm font-medium text-emerald-400 transition hover:text-emerald-300">Hospital login →</a>
                        @endguest
                    </div>
                </div>
            </div>
        </section>

        {{-- Features strip --}}
        <section class="py-20 sm:py-24">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="grid gap-12 lg:grid-cols-3">
                    <div class="text-center lg:text-left">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-slate-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h3 class="mt-3 font-semibold text-white">Fast triage</h3>
                        <p class="mt-1 text-sm text-slate-400">Priority scoring so the most critical cases get the fastest response.</p>
                    </div>
                    <div class="text-center lg:text-left">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-slate-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <h3 class="mt-3 font-semibold text-white">Smart assignment</h3>
                        <p class="mt-1 text-sm text-slate-400">Right ambulance and hospital based on location, capacity, and severity.</p>
                    </div>
                    <div class="text-center lg:text-left">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-lg bg-slate-800 text-slate-300">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <h3 class="mt-3 font-semibold text-white">Live updates</h3>
                        <p class="mt-1 text-sm text-slate-400">Real-time status and notifications so everyone stays informed.</p>
                    </div>
                </div>
            </div>
        </section>

        <footer class="border-t border-slate-800/50 py-8">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <p class="text-center text-sm text-slate-500">Afya Rescue – Emergency Response & Ambulance Coordination</p>
            </div>
        </footer>
    </main>

    {{-- WhatsApp floating help button --}}
    <a href="https://wa.me/254795574929?text=Hello%2C%20I%20need%20help%20with%20Afya%20Rescue"
       target="_blank"
       rel="noopener noreferrer"
       title="Chat with us on WhatsApp"
       style="position:fixed !important;bottom:1.5rem !important;right:1.5rem !important;left:auto !important;z-index:9999;display:block;line-height:0;">
        <svg style="width:56px;height:56px;display:block;" fill="#25D366" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51a12.8 12.8 0 00-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
        </svg>
    </a>

    {{-- Remove any cached Laravel welcome background graphic --}}
    <script>
        (function () {
            var el = document.getElementById('background');
            if (el) el.remove();
            document.querySelectorAll('img[src*="laravel.com"]').forEach(function (img) { img.remove(); });
        })();
    </script>
</body>
</html>