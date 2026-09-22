<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Member Portal') — Hotel Ciputra</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: { extend: { colors: { brand: { 50:'#f0f7ff',100:'#dbeefe',500:'#1e6bb8',600:'#17558f',700:'#123f6a',800:'#0f2a4a',900:'#0a1c32' } } } }
        }
    </script>
    @include('partials.pwa-head')
    @stack('styles')
</head>
<body class="bg-brand-50 min-h-screen antialiased">
@include('partials.page-loader')
<nav class="bg-brand-800 text-white shadow sticky top-0 z-30">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 flex justify-between h-16 items-center gap-4">
        <a href="{{ route('portal.dashboard') }}" class="flex items-center gap-2.5 font-semibold shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Hotel Ciputra Membership" class="w-9 h-9 rounded-xl shadow ring-1 ring-white/20">
            <span class="hidden sm:inline">Member Portal</span>
        </a>
        <div class="flex items-center gap-2 sm:gap-4 text-sm overflow-x-auto no-scrollbar -mx-1 px-1" style="scrollbar-width:none;">
            <a href="{{ route('portal.dashboard') }}" class="hover:text-amber-300 whitespace-nowrap {{ request()->routeIs('portal.dashboard') ? 'text-amber-300' : '' }}">Dashboard</a>
            <a href="{{ route('portal.card') }}" class="hover:text-amber-300 whitespace-nowrap {{ request()->routeIs('portal.card') ? 'text-amber-300' : '' }}">Card</a>
            <a href="{{ route('portal.visits') }}" class="hover:text-amber-300 whitespace-nowrap {{ request()->routeIs('portal.visits') ? 'text-amber-300' : '' }}">Visits</a>
            <a href="{{ route('portal.transactions') }}" class="hover:text-amber-300 whitespace-nowrap {{ request()->routeIs('portal.transactions') ? 'text-amber-300' : '' }}">Transactions</a>
            <a href="{{ route('portal.vouchers') }}" class="hover:text-amber-300 whitespace-nowrap {{ request()->routeIs('portal.vouchers') ? 'text-amber-300' : '' }}">Vouchers</a>
            <a href="{{ route('portal.benefits') }}" class="hover:text-amber-300 whitespace-nowrap {{ request()->routeIs('portal.benefits') ? 'text-amber-300' : '' }}">Benefits</a>
            <a href="{{ route('portal.profile') }}" class="hover:text-amber-300 whitespace-nowrap {{ request()->routeIs('portal.profile') ? 'text-amber-300' : '' }}">Profile</a>
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button class="bg-white/10 hover:bg-white/20 px-3 py-1.5 rounded-lg whitespace-nowrap">Logout</button>
            </form>
        </div>
    </div>
</nav>

@if (session('success'))
    <div class="max-w-6xl mx-auto mt-4 px-4 sm:px-6">
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
    </div>
@endif

<main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    @yield('content')
</main>
@include('partials.pwa-register')
</body>
</html>
