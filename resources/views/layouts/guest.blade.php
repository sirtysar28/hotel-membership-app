<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Ciputra Premiere Club (CPC)')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { 50:'#f0f7ff',100:'#dbeefe',500:'#1e6bb8',600:'#17558f',700:'#123f6a',800:'#0f2a4a',900:'#0a1c32' } }
                }
            }
        }
    </script>
    @include('partials.pwa-head')
    @stack('styles')
</head>
<body class="bg-gray-50 min-h-screen flex flex-col antialiased">
    @include('partials.page-loader')
    <nav class="bg-brand-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-semibold text-lg tracking-wide">
                    <img src="{{ \App\Support\Brand::logo('landing') }}" alt="Logo Ciputra Premiere Club (CPC)" class="w-9 h-9 rounded-xl shadow ring-1 ring-white/20 object-cover">
                    <span class="hidden sm:inline tracking-wider">CIPUTRA PREMIERE <span class="text-amber-300">CLUB</span></span>
                    <span class="sm:hidden tracking-wider">C<span class="text-amber-300">PC</span></span>
                </a>
                <div class="flex items-center gap-3">
                    <a href="{{ route('home') }}" class="hover:text-amber-300 text-sm">Home</a>
                    @auth
                        @if(auth()->user()->isMember())
                            <a href="{{ route('portal.dashboard') }}" class="bg-amber-400 text-brand-900 px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-amber-300">Member Portal</a>
                        @else
                            <a href="{{ route('admin.dashboard') }}" class="bg-amber-400 text-brand-900 px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-amber-300">Dashboard</a>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="hover:text-amber-300 text-sm">Login</a>
                        <a href="{{ route('register.create') }}" class="bg-amber-400 text-brand-900 px-4 py-1.5 rounded-lg text-sm font-semibold hover:bg-amber-300">Daftar Member</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    @if (session('success'))
        <div class="max-w-7xl mx-auto w-full mt-4 px-4 sm:px-6 lg:px-8">
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="max-w-7xl mx-auto w-full mt-4 px-4 sm:px-6 lg:px-8">
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-brand-900 text-brand-100 text-sm mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>&copy; {{ date('Y') }} Ciputra Premiere Club (CPC) &mdash; Jakarta &amp; Semarang. All rights reserved.</div>
            <div class="text-xs">Developed by <a href="https://trijayasolution.com" target="_blank" rel="noopener" class="text-amber-300 hover:text-amber-200 hover:underline font-semibold">Trijaya Solution</a></div>
        </div>
    </footer>
    @stack('scripts')
    @include('partials.pwa-register')
</body>
</html>
