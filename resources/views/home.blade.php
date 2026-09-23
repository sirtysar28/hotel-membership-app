@extends('layouts.guest')
@section('title', 'Ciputra Premiere Club (CPC) — Sistem Keanggotaan Eksklusif Hotel Ciputra')

@push('styles')
<style>
    /* Fallback tanpa JavaScript: konten tetap terlihat */
    .no-js .reveal, .no-js .reveal-l, .no-js .reveal-r, .no-js .reveal-scale { opacity: 1 !important; transform: none !important; }
    /* ===== Animations ===== */
    @keyframes floatY { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-16px); } }
    @keyframes floatY2 { 0%,100% { transform: translateY(0) rotate(-1.5deg); } 50% { transform: translateY(-10px) rotate(1.5deg); } }
    @keyframes blobDrift { 0%,100% { transform: translate(0,0) scale(1); } 33% { transform: translate(30px,-40px) scale(1.08); } 66% { transform: translate(-25px,25px) scale(.95); } }
    @keyframes gradientShift { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
    @keyframes shimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes scrollHint { 0%,100% { transform: translateY(0); opacity: .9; } 50% { transform: translateY(8px); opacity: .4; } }

    html { scroll-behavior: smooth; }

    /* Hero animated gradient mesh */
    .hero-bg {
        background: linear-gradient(125deg, #0a1c32, #0f2a4a, #123f6a, #17558f, #123f6a, #0f2a4a);
        background-size: 300% 300%;
        animation: gradientShift 14s ease infinite;
    }
    .blob { position: absolute; border-radius: 9999px; filter: blur(60px); opacity: .35; will-change: transform; }
    .blob-gold  { background: #fbbf24; width: 22rem; height: 22rem; top: -6rem; right: -6rem; animation: blobDrift 11s ease-in-out infinite; }
    .blob-blue  { background: #1e6bb8; width: 26rem; height: 26rem; bottom: -10rem; left: -8rem; animation: blobDrift 14s ease-in-out infinite reverse; }
    .blob-emerald { background: #10b981; width: 14rem; height: 14rem; top: 40%; left: 35%; opacity: .18; animation: blobDrift 17s ease-in-out infinite; }

    /* Floating hero cards */
    .float-slow { animation: floatY 7s ease-in-out infinite; }
    .float-fast { animation: floatY2 5.5s ease-in-out infinite; }
    .float-delay { animation-delay: 1.2s; }

    /* Scroll reveal */
    .reveal { opacity: 0; transform: translateY(32px); transition: opacity .8s cubic-bezier(.16,1,.3,1), transform .8s cubic-bezier(.16,1,.3,1); will-change: opacity, transform; }
    .reveal.visible { opacity: 1; transform: translateY(0); }
    .reveal-l { opacity: 0; transform: translateX(-40px); transition: opacity .8s ease, transform .8s cubic-bezier(.16,1,.3,1); }
    .reveal-l.visible { opacity: 1; transform: translateX(0); }
    .reveal-r { opacity: 0; transform: translateX(40px); transition: opacity .8s ease, transform .8s cubic-bezier(.16,1,.3,1); }
    .reveal-r.visible { opacity: 1; transform: translateX(0); }
    .reveal-scale { opacity: 0; transform: scale(.92); transition: opacity .7s ease, transform .7s cubic-bezier(.16,1,.3,1); }
    .reveal-scale.visible { opacity: 1; transform: scale(1); }

    /* Parallax */
    .parallax { will-change: transform; }

    /* Gold shimmer text */
    .text-shimmer {
        background: linear-gradient(90deg, #f59e0b 25%, #fde68a 50%, #f59e0b 75%);
        background-size: 400px 100%;
        -webkit-background-clip: text; background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: shimmer 5s linear infinite;
    }

    /* Scroll hint */
    .scroll-hint { animation: scrollHint 2s ease-in-out infinite; }

    /* Card hover lift */
    .lift { transition: transform .35s cubic-bezier(.16,1,.3,1), box-shadow .35s ease; }
    .lift:hover { transform: translateY(-8px); box-shadow: 0 24px 48px -12px rgba(10,28,50,.25); }

    /* Respect reduced motion */
    @media (prefers-reduced-motion: reduce) {
        .hero-bg, .blob, .float-slow, .float-fast, .text-shimmer, .scroll-hint { animation: none !important; }
        .reveal, .reveal-l, .reveal-r, .reveal-scale { opacity: 1 !important; transform: none !important; transition: none !important; }
        html { scroll-behavior: auto; }
    }
</style>
@endpush

@section('content')

{{-- ============================= HERO ============================= --}}
<section class="hero-bg relative overflow-hidden text-white">
    {{-- Parallax blobs --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
        <div class="blob blob-gold" data-parallax="0.25"></div>
        <div class="blob blob-blue" data-parallax="-0.18"></div>
        <div class="blob blob-emerald hidden sm:block" data-parallax="0.12"></div>
        {{-- Grid pattern --}}
        <div class="absolute inset-0 opacity-[0.05]" style="background-image:linear-gradient(#fff 1px,transparent 1px),linear-gradient(90deg,#fff 1px,transparent 1px);background-size:56px 56px;" data-parallax="0.06"></div>
    </div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 lg:py-36 grid lg:grid-cols-2 gap-14 items-center">
        <div class="text-center lg:text-left">
            <div class="inline-flex items-center gap-2 bg-white/10 border border-white/20 backdrop-blur px-4 py-1.5 rounded-full text-xs font-semibold tracking-wider uppercase mb-6 reveal">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Jakarta &amp; Semarang
            </div>
            <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold leading-[1.1] tracking-tight reveal" style="transition-delay:.1s">
                CIPUTRA PREMIERE<br>
                <span class="text-shimmer">CLUB (CPC)</span>
            </h1>
            <p class="mt-6 text-brand-100/90 text-base sm:text-lg leading-relaxed max-w-xl mx-auto lg:mx-0 reveal" style="transition-delay:.2s">
                Dua jalur keanggotaan: <strong class="text-amber-300">Paid Membership</strong> ({{ format_idr($price) }}) dan
                <strong class="text-amber-300">Free / Earned Membership</strong> yang naik level otomatis berdasarkan visit &amp; spending Anda.
            </p>
            <div class="mt-9 flex flex-wrap gap-4 justify-center lg:justify-start reveal" style="transition-delay:.3s">
                <a href="{{ route('register.create') }}" class="group bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-brand-900 font-bold px-8 py-4 rounded-2xl shadow-xl shadow-amber-500/20 transition-all hover:scale-[1.03] active:scale-95">
                    Daftar Sekarang
                    <span class="inline-block group-hover:translate-x-1 transition-transform">→</span>
                </a>
                <a href="#levels" class="border border-white/30 hover:bg-white/10 backdrop-blur px-8 py-4 rounded-2xl font-semibold transition-all hover:scale-[1.03] active:scale-95">
                    Lihat Level
                </a>
            </div>

            {{-- Stats dengan count-up --}}
            <div class="mt-12 grid grid-cols-3 gap-3 sm:gap-6 max-w-md mx-auto lg:mx-0 reveal" style="transition-delay:.4s">
                <div class="text-center lg:text-left">
                    <div class="text-2xl sm:text-4xl font-extrabold text-amber-300" data-countup="{{ $totalMembers }}">0</div>
                    <div class="text-[11px] sm:text-xs text-brand-200/80 uppercase tracking-wider mt-1">Members</div>
                </div>
                <div class="text-center lg:text-left">
                    <div class="text-2xl sm:text-4xl font-extrabold text-amber-300" data-countup="{{ $totalVisits }}">0</div>
                    <div class="text-[11px] sm:text-xs text-brand-200/80 uppercase tracking-wider mt-1">Total Visits</div>
                </div>
                <div class="text-center lg:text-left">
                    <div class="text-2xl sm:text-4xl font-extrabold text-amber-300" data-countup="{{ $hotels->count() }}">0</div>
                    <div class="text-[11px] sm:text-xs text-brand-200/80 uppercase tracking-wider mt-1">Hotels</div>
                </div>
            </div>
        </div>

        {{-- Floating level cards --}}
        <div class="relative grid grid-cols-2 gap-4 sm:gap-5 max-w-md mx-auto w-full" data-parallax="-0.05">
            @foreach($levels->take(4) as $index => $level)
                <div class="bg-white/[.07] border border-white/15 backdrop-blur-xl rounded-3xl p-5 sm:p-6 shadow-2xl {{ $index % 2 === 0 ? 'float-slow' : 'float-fast float-delay' }} {{ $index === 1 ? 'sm:mt-8' : '' }} {{ $index === 3 ? 'sm:-mt-4' : '' }}"
                     style="border-top: 3px solid {{ $level->card_color }};">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-lg font-bold mb-3" style="background: {{ $level->card_color }}22; color: {{ $level->card_color }};">
                        {{ substr($level->name, 0, 1) }}
                    </div>
                    <div class="font-bold text-base sm:text-lg">{{ $level->name }}</div>
                    <div class="text-[11px] sm:text-xs text-brand-200/80 mt-2 space-y-1">
                        @if($level->activeRule)
                            <div>· Visit: {{ $level->activeRule->min_visit }}{{ $level->activeRule->max_visit ? '–'.$level->activeRule->max_visit : ($level->activeRule->min_visit ? '+' : '') }}</div>
                            <div>· Spending: {{ $level->activeRule->min_spending > 0 ? format_idr($level->activeRule->min_spending) : '-' }}</div>
                        @else
                            <div>· Level awal</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Scroll hint --}}
    <div class="relative flex justify-center pb-6">
        <a href="#cara-kerja" class="text-white/50 hover:text-white/80 transition-colors scroll-hint" aria-label="Scroll ke bawah">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 13l-7 7-7-7m14-8l-7 7-7-7"/></svg>
        </a>
    </div>

    {{-- Wave divider --}}
    <div class="absolute bottom-0 inset-x-0 leading-none" aria-hidden="true">
        <svg viewBox="0 0 1440 74" fill="#f9fafb" preserveAspectRatio="none" class="w-full h-10 sm:h-16"><path d="M0,40 C240,80 480,0 720,24 C960,48 1200,74 1440,30 L1440,74 L0,74 Z"/></svg>
    </div>
</section>

{{-- ============================= CARA KERJA ============================= --}}
<section id="cara-kerja" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-24">
    <div class="text-center max-w-2xl mx-auto reveal">
        <span class="text-xs font-bold tracking-[0.25em] uppercase text-brand-500">Mudah &amp; Otomatis</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-brand-800 mt-3">Cara Kerja</h2>
        <p class="text-gray-500 mt-4">Tiga langkah simpel menuju pengalaman menginap &amp; dining yang lebih rewarding.</p>
    </div>
    <div class="grid md:grid-cols-3 gap-6 lg:gap-8 mt-12">
        <div class="relative bg-white rounded-3xl shadow-lg shadow-brand-900/5 p-8 lift reveal" style="transition-delay:0s">
            <div class="absolute -top-5 left-8 w-11 h-11 rounded-2xl bg-gradient-to-br from-brand-500 to-brand-800 text-white font-bold flex items-center justify-center shadow-lg shadow-brand-500/30">1</div>
            <div class="text-4xl mt-2">📝</div>
            <h3 class="font-bold text-brand-800 text-lg mt-4">Registrasi</h3>
            <p class="text-sm text-gray-600 mt-3 leading-relaxed">Isi data pribadi, sistem otomatis mengecek duplikasi data member. Pilih jalur Paid atau Free.</p>
        </div>
        <div class="relative bg-white rounded-3xl shadow-lg shadow-brand-900/5 p-8 lift reveal" style="transition-delay:.15s">
            <div class="absolute -top-5 left-8 w-11 h-11 rounded-2xl bg-gradient-to-br from-amber-400 to-amber-600 text-brand-900 font-bold flex items-center justify-center shadow-lg shadow-amber-500/30">2</div>
            <div class="text-4xl mt-2">💳</div>
            <h3 class="font-bold text-brand-800 text-lg mt-4">Digital Card</h3>
            <p class="text-sm text-gray-600 mt-3 leading-relaxed">Setelah aktif, digital membership card dengan QR code dikirim otomatis ke email Anda.</p>
        </div>
        <div class="relative bg-white rounded-3xl shadow-lg shadow-brand-900/5 p-8 lift reveal" style="transition-delay:.3s">
            <div class="absolute -top-5 left-8 w-11 h-11 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white font-bold flex items-center justify-center shadow-lg shadow-emerald-500/30">3</div>
            <div class="text-4xl mt-2">⭐</div>
            <h3 class="font-bold text-brand-800 text-lg mt-4">Naik Level</h3>
            <p class="text-sm text-gray-600 mt-3 leading-relaxed">Setiap stay &amp; transaksi F&amp;B tercatat. Level naik otomatis: Classic → Privilege → Signature → Diamond.</p>
        </div>
    </div>
</section>

{{-- ============================= LEVELS ============================= --}}
<section id="levels" class="relative py-20 md:py-24 bg-gradient-to-b from-brand-50/70 via-white to-white overflow-hidden">
    <div class="absolute top-20 -right-24 w-72 h-72 rounded-full bg-amber-200/30 blur-3xl pointer-events-none" data-parallax="0.1" aria-hidden="true"></div>
    <div class="absolute bottom-10 -left-24 w-80 h-80 rounded-full bg-brand-100/60 blur-3xl pointer-events-none" data-parallax="-0.08" aria-hidden="true"></div>

    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-2xl mx-auto reveal">
            <span class="text-xs font-bold tracking-[0.25em] uppercase text-brand-500">Membership Tiers</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-brand-800 mt-3">Pilih Level Anda</h2>
            <p class="text-gray-500 mt-4">Semakin sering stay &amp; dining, semakin tinggi level — benefit semakin eksklusif.</p>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mt-12">
            @foreach($levels as $index => $level)
                <div class="bg-white rounded-3xl border-2 p-6 sm:p-7 text-center lift reveal-scale {{ $level->code === 'signature' ? 'border-amber-400 shadow-xl shadow-amber-500/10' : 'border-gray-100' }}" style="transition-delay: {{ $index * 0.1 }}s">
                    @if($level->code === 'signature')
                        <span class="inline-block bg-amber-400 text-brand-900 text-[10px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider mb-3">Populer</span>
                    @endif
                    <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center text-xl font-extrabold shadow-inner" style="background: {{ $level->card_color }}1a; color: {{ $level->card_color }};">
                        {{ substr($level->name, 0, 1) }}
                    </div>
                    <h3 class="font-bold text-brand-800 mt-4">{{ $level->name }}</h3>
                    @if($level->activeRule)
                        <div class="text-xs text-gray-500 mt-3 space-y-1.5">
                            <div class="flex justify-center items-center gap-1.5"><span>🛏</span> {{ $level->activeRule->min_visit }}{{ $level->activeRule->max_visit ? '–'.$level->activeRule->max_visit : ($level->activeRule->min_visit ? '+' : '') }} visit</div>
                            <div class="flex justify-center items-center gap-1.5"><span>💰</span> {{ $level->activeRule->min_spending > 0 ? format_idr($level->activeRule->min_spending) : 'Gratis' }}</div>
                        </div>
                    @else
                        <div class="text-xs text-gray-500 mt-3">Level awal — gratis</div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Benefit Membership --}}
        <div class="mt-14 bg-white rounded-3xl shadow-lg shadow-brand-900/5 p-6 sm:p-8 reveal">
            <h3 class="font-bold text-brand-800 text-lg text-center">Benefit Membership</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
                @foreach(\App\Models\MembershipBenefit::where('is_active', true)->limit(6)->get() as $benefit)
                    <div class="border border-gray-100 rounded-2xl p-5 bg-brand-50/40 hover:bg-brand-50 transition-colors lift">
                        <div class="text-sm font-semibold text-brand-800">{{ $benefit->name }}</div>
                        <div class="text-xs text-gray-500 mt-1.5 leading-relaxed">{{ $benefit->description }}</div>
                        @if($benefit->level)
                            <div class="mt-3"><span class="text-[10px] font-semibold bg-brand-100 text-brand-700 px-2.5 py-1 rounded-full uppercase tracking-wide">{{ $benefit->level->name }}</span></div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- ============================= HOTELS ============================= --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-24">
    <div class="text-center max-w-2xl mx-auto reveal">
        <span class="text-xs font-bold tracking-[0.25em] uppercase text-brand-500">Lokasi Kami</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-brand-800 mt-3">Hotel Partner</h2>
    </div>
    <div class="grid sm:grid-cols-2 gap-6 mt-12">
        @foreach($hotels as $index => $hotel)
            <div class="relative rounded-3xl overflow-hidden lift reveal {{ $index === 0 ? 'reveal-l' : 'reveal-r' }}">
                <div class="hero-bg h-44 sm:h-52 flex items-center justify-center text-6xl">🏨</div>
                <div class="bg-white p-6 border border-t-0 border-gray-100 rounded-b-3xl">
                    <h3 class="font-bold text-brand-800 text-lg">{{ $hotel->name }}</h3>
                    <p class="text-sm text-gray-500 mt-1.5">{{ $hotel->city }}</p>
                    @if($hotel->address)
                        <p class="text-xs text-gray-400 mt-2">{{ $hotel->address }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>

{{-- ============================= CTA ============================= --}}
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-20 md:pb-24">
    <div class="hero-bg relative overflow-hidden rounded-[2rem] px-6 py-14 sm:p-16 text-center text-white shadow-2xl shadow-brand-900/30 reveal-scale">
        <div class="blob blob-gold !top-auto -bottom-24 -right-16 !w-64 !h-64 opacity-25" aria-hidden="true"></div>
        <div class="relative">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight">Siap Naik <span class="text-shimmer">Level?</span></h2>
            <p class="mt-5 text-brand-100/90 max-w-xl mx-auto">Bergabung sekarang — gratis untuk level Classic, atau langsung aktifkan Paid Membership dengan benefit penuh.</p>
            <div class="mt-9 flex flex-wrap gap-4 justify-center">
                <a href="{{ route('register.create') }}" class="bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-brand-900 font-bold px-8 py-4 rounded-2xl shadow-xl transition-all hover:scale-[1.03] active:scale-95">Daftar Sekarang →</a>
                <a href="{{ route('login') }}" class="border border-white/30 hover:bg-white/10 px-8 py-4 rounded-2xl font-semibold transition-all hover:scale-[1.03] active:scale-95">Member Login</a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    // Matikan fallback no-js (JS aktif)
    document.documentElement.classList.remove('no-js');

    function initLanding() {
        // ===== Scroll reveal (IntersectionObserver) =====
        const revealEls = () => document.querySelectorAll('.reveal:not(.visible), .reveal-l:not(.visible), .reveal-r:not(.visible), .reveal-scale:not(.visible)');

        if ('IntersectionObserver' in window) {
            const io = new IntersectionObserver((entries) => {
                entries.forEach((e) => {
                    if (e.isIntersecting) {
                        e.target.classList.add('visible');
                        io.unobserve(e.target);
                    }
                });
            }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
            revealEls().forEach((el) => io.observe(el));
        } else {
            // Browser lama: langsung tampilkan semua
            revealEls().forEach((el) => el.classList.add('visible'));
        }

        // Safety net: elemen yang entah kenapa belum visible setelah 2.5s → paksa tampil
        setTimeout(() => revealEls().forEach((el) => el.classList.add('visible')), 2500);

        // ===== Parallax (rAF + scroll, hanya non-touch utk performa) =====
        const pxItems = Array.from(document.querySelectorAll('[data-parallax]'));
        let ticking = false;
        function applyParallax() {
            const y = window.scrollY || window.pageYOffset || 0;
            pxItems.forEach((el) => {
                const speed = parseFloat(el.dataset.parallax) || 0;
                el.style.transform = 'translate3d(0,' + (y * speed).toFixed(1) + 'px,0)';
            });
            ticking = false;
        }
        window.addEventListener('scroll', () => {
            if (!ticking) { requestAnimationFrame(applyParallax); ticking = true; }
        }, { passive: true });
        applyParallax();

        // ===== Count-up stats =====
        const counters = document.querySelectorAll('[data-countup]');
        if ('IntersectionObserver' in window) {
            const cio = new IntersectionObserver((entries) => {
                entries.forEach((e) => {
                    if (!e.isIntersecting) return;
                    const el = e.target;
                    runCountup(el);
                    cio.unobserve(el);
                });
            }, { threshold: 0.4 });
            counters.forEach((el) => cio.observe(el));
        } else {
            counters.forEach(runCountup);
        }

        function runCountup(el) {
            if (el.dataset.done) return;
            el.dataset.done = '1';
            const target = parseInt(el.dataset.countup, 10) || 0;
            const dur = 1600, start = performance.now();
            const step = (now) => {
                const p = Math.min((now - start) / dur, 1);
                const eased = 1 - Math.pow(1 - p, 3);
                el.textContent = new Intl.NumberFormat('id-ID').format(Math.round(target * eased));
                if (p < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLanding);
    } else {
        initLanding();
    }
})();
</script>
@endpush
