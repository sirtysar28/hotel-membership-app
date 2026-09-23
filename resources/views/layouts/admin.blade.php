<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') — Ciputra Premiere Club (CPC)</title>
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
    <style>
        /* ===== Sidebar collapse (hanya desktop ≥768px) ===== */
        #sidebar { width: 16rem; transition: width .25s ease, transform .25s ease; }
        @media (min-width: 768px) {
            body.nav-collapsed #sidebar { width: 4.75rem; }
            body.nav-collapsed .nav-label,
            body.nav-collapsed .nav-section-title { display: none; }
            body.nav-collapsed .nav-link { justify-content: center; padding-left: .5rem; padding-right: .5rem; }
            body.nav-collapsed .sidebar-brand-text { display: none; }
            body.nav-collapsed .sidebar-footer-text { display: none; }
        }
        #btn-collapse svg { transition: transform .25s ease; }
        @media (min-width: 768px) {
            body.nav-collapsed #btn-collapse svg { transform: rotate(180deg); }
        }

        /* ===== Sidebar off-canvas (mobile) ===== */
        #sidebar { transform: translateX(-100%); }
        #sidebar.open { transform: translateX(0); }
        @media (min-width: 768px) {
            #sidebar { transform: translateX(0) !important; }
        }

        /* Scrollbar tipis utk nav */
        #sidebar nav::-webkit-scrollbar { width: 5px; }
        #sidebar nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 99px; }
    </style>
    @include('partials.pwa-head')
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen antialiased">

@include('partials.page-loader')

{{-- Backdrop mobile --}}
<div id="sidebar-backdrop" class="fixed inset-0 bg-black/50 z-30 hidden md:hidden opacity-0 transition-opacity duration-200"></div>

<div class="flex min-h-screen md:h-screen md:overflow-hidden">

    {{-- ==================== SIDEBAR ==================== --}}
    <aside id="sidebar" class="fixed md:sticky top-0 left-0 z-40 h-full md:h-screen flex flex-col shrink-0 bg-brand-800 text-brand-100 shadow-xl">
        {{-- Brand --}}
        <div class="h-16 flex items-center px-4 border-b border-white/10 shrink-0">
            <img src="{{ \App\Support\Brand::logo('admin') }}" alt="Logo Hotel Ciputra Membership" class="w-9 h-9 rounded-xl shadow-lg shrink-0 object-cover">
            <span class="sidebar-brand-text font-semibold text-base text-white ml-3 whitespace-nowrap">CPC <span class="text-amber-300">Admin</span></span>
            {{-- Close (mobile) --}}
            <button onclick="closeSidebar()" class="md:hidden ml-auto p-1.5 rounded-lg hover:bg-white/10 text-brand-200" aria-label="Tutup menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm overflow-y-auto overscroll-contain">
            @include('partials.admin-nav')
        </nav>

        {{-- Footer sidebar --}}
        <div class="p-3 border-t border-white/10 shrink-0">
            <p class="sidebar-footer-text text-xs text-brand-300">Ciputra Premiere Club v1.0</p>
            <p class="sidebar-footer-text text-[10px] text-brand-400/70 mt-0.5">
                Developed by <a href="https://trijayasolution.com" target="_blank" rel="noopener" class="text-amber-300/90 hover:text-amber-300">Trijaya Solution</a>
            </p>
        </div>
    </aside>

    {{-- ==================== MAIN ==================== --}}
    <div class="flex-1 flex flex-col min-w-0 md:h-screen md:overflow-y-auto">

        {{-- Topbar --}}
        <header class="bg-white/95 backdrop-blur border-b h-16 flex items-center justify-between gap-2 px-3 sm:px-6 sticky top-0 z-20 shrink-0">
            <div class="flex items-center gap-1.5 sm:gap-3 min-w-0">
                {{-- Hamburger (mobile) --}}
                <button onclick="openSidebar()" class="md:hidden p-2 -ml-1 rounded-lg text-brand-800 hover:bg-brand-50" aria-label="Buka menu">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                {{-- Collapse toggle (desktop) --}}
                <button id="btn-collapse" onclick="toggleCollapse()" class="hidden md:flex p-2 rounded-lg text-brand-800 hover:bg-brand-50" aria-label="Minimize sidebar" title="Minimize / expand sidebar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                </button>
                <h1 class="font-semibold text-brand-800 truncate text-sm sm:text-base">@yield('page-title', 'Dashboard')</h1>
            </div>

            <div class="flex items-center gap-2 sm:gap-4">
                @if(auth()->user()->hotel)
                    <span class="hidden lg:inline text-xs bg-brand-50 text-brand-700 px-2.5 py-1 rounded-full whitespace-nowrap">{{ auth()->user()->hotel->name }}</span>
                @endif
                <div class="hidden sm:flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-brand-800 text-amber-300 flex items-center justify-center font-semibold text-sm shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="text-right leading-tight max-w-[10rem]">
                        <div class="text-sm font-medium text-gray-800 truncate">{{ auth()->user()->name }}</div>
                        <div class="text-xs text-gray-500">{{ auth()->user()->roleLabel() }}</div>
                    </div>
                </div>
                {{-- Avatar kecil (mobile) --}}
                <div class="sm:hidden w-8 h-8 rounded-full bg-brand-800 text-amber-300 flex items-center justify-center font-semibold text-xs">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-xs bg-red-50 text-red-600 hover:bg-red-100 px-2.5 sm:px-3 py-2 rounded-lg font-medium whitespace-nowrap">Logout</button>
                </form>
            </div>
        </header>

        {{-- Flash --}}
        @if (session('success'))
            <div class="mx-4 sm:mx-6 mt-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mx-4 sm:mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>

        <footer class="text-center text-xs text-gray-400 px-4 py-4 border-t border-gray-200/70">
            &copy; {{ date('Y') }} Ciputra Premiere Club (CPC) —
            Developed by <a href="https://trijayasolution.com" target="_blank" rel="noopener" class="text-brand-500 hover:underline font-medium">Trijaya Solution</a>
        </footer>
    </div>
</div>

<script>
    /* ===== Toggle intip password (dipakai di semua input password) ===== */
    function togglePw(btn) {
        const input = btn.parentElement.querySelector('input');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.querySelector('.i-eye').classList.toggle('hidden', show);
        btn.querySelector('.i-eyeoff').classList.toggle('hidden', !show);
        btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    }

    /* ===== Sidebar: collapse (desktop) + off-canvas (mobile) ===== */
    const KEY = 'hc-sidebar-collapsed';
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');

    // Restore preferensi collapse
    if (localStorage.getItem(KEY) === '1') {
        document.body.classList.add('nav-collapsed');
    }

    function toggleCollapse() {
        const collapsed = document.body.classList.toggle('nav-collapsed');
        localStorage.setItem(KEY, collapsed ? '1' : '0');
    }

    function openSidebar() {
        sidebar.classList.add('open');
        backdrop.classList.remove('hidden');
        requestAnimationFrame(() => backdrop.classList.replace('opacity-0', 'opacity-100'));
        document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        backdrop.classList.replace('opacity-100', 'opacity-0');
        setTimeout(() => backdrop.classList.add('hidden'), 200);
        document.body.style.overflow = '';
    }

    backdrop.addEventListener('click', closeSidebar);
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeSidebar();
    });
    // Tutup sidebar mobile saat link diklik
    sidebar.querySelectorAll('a').forEach((a) => a.addEventListener('click', () => {
        if (window.innerWidth < 768) closeSidebar();
    }));
</script>
@include('partials.pwa-register')
@stack('scripts')
</body>
</html>
