<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Login') — Hotel Ciputra Membership</title>
    @include('partials.pwa-head')
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { brand: { 50:'#f0f7ff',100:'#dbeefe',500:'#1e6bb8',600:'#17558f',700:'#123f6a',800:'#0f2a4a',900:'#0a1c32' }, gold: { 300:'#fcd34d',400:'#fbbf24',500:'#f59e0b' } }
                }
            }
        }
    </script>
    @stack('styles')
    @stack('scripts')
</head>
<body class="bg-gradient-to-br from-brand-900 via-brand-800 to-brand-700 min-h-screen flex flex-col antialiased">
    @include('partials.page-loader')

    {{-- Dekorasi latar --}}
    <div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full bg-gold-400/10"></div>
        <div class="absolute -bottom-32 -left-24 w-[28rem] h-[28rem] rounded-full bg-white/5"></div>
    </div>

    <main class="flex-1 flex items-center justify-center px-4 py-12 relative">
        <div class="w-full max-w-md">
            <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
                {{-- Strip emas --}}
                <div class="h-1.5 bg-gradient-to-r from-gold-500 via-gold-400 to-gold-300"></div>
                <div class="p-8">
                    {{-- Logo tunggal: lockup transparan utk latar putih --}}
                    <div class="flex justify-center mb-6">
                        <img src="{{ asset('images/logo-header-light.png') }}" alt="Hotel Ciputra Membership" class="w-64 max-w-full">
                    </div>

                    @if (session('status'))
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm mb-4">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-lg px-4 py-3 text-sm mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="relative text-center text-brand-100/80 text-xs px-4 pb-6 space-y-1">
        <div>&copy; {{ date('Y') }} Hotel Ciputra Membership &mdash; Jakarta &amp; Semarang. All rights reserved.</div>
        <div>
            Developed by
            <a href="https://trijayasolution.com" target="_blank" rel="noopener" class="text-gold-300 hover:text-gold-400 font-semibold hover:underline">Trijaya Solution</a>
        </div>
    </footer>

    @include('partials.pwa-register')
    <script>
        /* Toggle intip password */
        function togglePw(btn) {
            const input = btn.parentElement.querySelector('input');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.querySelector('.i-eye').classList.toggle('hidden', show);
            btn.querySelector('.i-eyeoff').classList.toggle('hidden', !show);
            btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
        }
    </script>
</body>
</html>
