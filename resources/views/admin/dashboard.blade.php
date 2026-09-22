@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('page-title', 'Dashboard')

@section('content')
{{-- ===== Hero / Welcome ===== --}}
<div class="rounded-2xl bg-gradient-to-r from-brand-900 via-brand-800 to-brand-600 text-white p-5 sm:p-6 shadow-lg relative overflow-hidden">
    <div class="absolute -right-10 -top-10 w-48 h-48 rounded-full bg-gold-400/10 pointer-events-none" aria-hidden="true"></div>
    <div class="absolute right-16 bottom-0 w-24 h-24 rounded-full bg-white/5 pointer-events-none" aria-hidden="true"></div>
    <div class="relative flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg sm:text-xl font-bold">Selamat datang, {{ auth()->user()->name }} 👋</h2>
            <p class="text-brand-100/80 text-sm mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs bg-white/10 border border-white/20 px-3 py-1.5 rounded-full">{{ auth()->user()->roleLabel() }}</span>
            @if(auth()->user()->hotel)
                <span class="text-xs bg-gold-400/20 border border-gold-400/40 text-gold-300 px-3 py-1.5 rounded-full">{{ auth()->user()->hotel->name }}</span>
            @endif
        </div>
    </div>
</div>

{{-- ===== KPI Cards ===== --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4 mt-4">
    <div class="bg-white rounded-2xl shadow p-4 sm:p-5 relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-brand-50 group-hover:scale-125 transition-transform" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-gray-500">Total Members</div>
                <div class="text-2xl sm:text-3xl font-bold text-brand-800 mt-1">{{ number_format($totalMembers) }}</div>
                <div class="text-xs text-emerald-600 font-medium mt-1">{{ number_format($activeMembers) }} aktif</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand-600 to-brand-800 text-white flex items-center justify-center shadow shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-6.93M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow p-4 sm:p-5 relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-amber-50 group-hover:scale-125 transition-transform" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-gray-500">Paid Members</div>
                <div class="text-2xl sm:text-3xl font-bold text-brand-800 mt-1">{{ number_format($paidMembers) }}</div>
                @php $paidPct = $totalMembers ? round($paidMembers / $totalMembers * 100) : 0 @endphp
                <div class="mt-2 h-1.5 w-24 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full" style="width: {{ $paidPct }}%"></div></div>
                <div class="text-xs text-gray-400 mt-1">{{ $paidPct }}% dari total</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center shadow shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18m6 0V11l-6-4"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow p-4 sm:p-5 relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-emerald-50 group-hover:scale-125 transition-transform" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-gray-500">Free Members</div>
                <div class="text-2xl sm:text-3xl font-bold text-brand-800 mt-1">{{ number_format($freeMembers) }}</div>
                @php $freePct = $totalMembers ? round($freeMembers / $totalMembers * 100) : 0 @endphp
                <div class="mt-2 h-1.5 w-24 bg-gray-100 rounded-full overflow-hidden"><div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full" style="width: {{ $freePct }}%"></div></div>
                <div class="text-xs text-gray-400 mt-1">{{ $freePct }}% dari total</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center shadow shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-6 4h12M6 12v7a2 2 0 002 2h8a2 2 0 002-2v-7"/></svg>
            </div>
        </div>
    </div>
    <div class="bg-white rounded-2xl shadow p-4 sm:p-5 relative overflow-hidden group">
        <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-purple-50 group-hover:scale-125 transition-transform" aria-hidden="true"></div>
        <div class="relative flex items-start justify-between">
            <div>
                <div class="text-[11px] uppercase tracking-wider text-gray-500">Total Revenue</div>
                <div class="text-xl sm:text-2xl font-bold text-brand-800 mt-1">{{ format_idr($totalRevenue) }}</div>
                <div class="text-xs text-gray-400 mt-1.5">{{ format_idr($membershipRevenue) }} membership</div>
            </div>
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-purple-500 to-brand-700 text-white flex items-center justify-center shadow shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 9v1m8-5a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
            </div>
        </div>
    </div>
</div>

{{-- ===== Pending alerts ===== --}}
@if($pendingDuplicates > 0 || $pendingPayments > 0)
    <div class="mt-4 flex flex-wrap gap-3">
        @if($pendingDuplicates > 0)
            <a href="{{ route('admin.duplicates.index', ['status' => 'potential_duplicate']) }}" class="bg-orange-50 border border-orange-200 text-orange-700 rounded-xl px-4 py-2.5 text-sm font-medium hover:bg-orange-100 transition-colors">
                ⚠ {{ $pendingDuplicates }} duplicate check menunggu review
            </a>
        @endif
        @if($pendingPayments > 0)
            <a href="{{ route('admin.payments.index', ['status' => 'payment_submitted']) }}" class="bg-amber-50 border border-amber-200 text-amber-700 rounded-xl px-4 py-2.5 text-sm font-medium hover:bg-amber-100 transition-colors">
                ⏳ {{ $pendingPayments }} payment menunggu verifikasi
            </a>
        @endif
    </div>
@endif

{{-- ===== Charts row 1 ===== --}}
<div class="grid lg:grid-cols-3 gap-4 mt-4">
    {{-- Member growth: grouped bar --}}
    <div class="bg-white rounded-2xl shadow p-5 lg:col-span-2">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-brand-800 text-sm">Member Growth <span class="text-gray-400 font-normal">(6 bulan)</span></h3>
            <span class="text-xs text-gray-400">Bar • Paid vs Free</span>
        </div>
        <div class="mt-3 h-64 sm:h-72"><canvas id="chart-growth"></canvas></div>
    </div>

    {{-- Paid vs Free: donut --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <h3 class="font-semibold text-brand-800 text-sm">Paid vs Free</h3>
        <div class="mt-3 h-64 sm:h-72"><canvas id="chart-paidfree"></canvas></div>
    </div>
</div>

{{-- ===== Charts row 2 ===== --}}
<div class="grid lg:grid-cols-3 gap-4 mt-4">
    {{-- Level distribution: donut --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <h3 class="font-semibold text-brand-800 text-sm">Level Distribution</h3>
        <div class="mt-3 h-64 sm:h-72"><canvas id="chart-levels"></canvas></div>
    </div>

    {{-- Revenue trend: area --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-brand-800 text-sm">Revenue Trend</h3>
            <span class="text-xs text-gray-400">6 bulan</span>
        </div>
        <div class="mt-3 h-64 sm:h-72"><canvas id="chart-revenue"></canvas></div>
    </div>

    {{-- Status distribution: donut --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <h3 class="font-semibold text-brand-800 text-sm">Status Membership</h3>
        <div class="mt-3 h-64 sm:h-72"><canvas id="chart-status"></canvas></div>
    </div>
</div>

{{-- ===== Charts row 3 ===== --}}
<div class="grid lg:grid-cols-2 gap-4 mt-4">
    {{-- Revenue by type: horizontal bar --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <div class="flex items-center justify-between">
            <h3 class="font-semibold text-brand-800 text-sm">Revenue per Jenis Transaksi</h3>
            <span class="text-xs text-gray-400">Total keseluruhan</span>
        </div>
        <div class="mt-3 h-64 sm:h-72"><canvas id="chart-revenue-type"></canvas></div>
    </div>

    {{-- Hotel distribution + revenue summary --}}
    <div class="bg-white rounded-2xl shadow p-5">
        <h3 class="font-semibold text-brand-800 text-sm">Hotel Distribution</h3>
        <div class="mt-4 space-y-3">
            @foreach($hotelDistribution as $hotelName => $count)
                @php $pct = $totalMembers ? round($count / $totalMembers * 100) : 0 @endphp
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-36 sm:w-44 truncate text-gray-600" title="{{ $hotelName }}">{{ $hotelName }}</span>
                    <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-gold-400 to-amber-500 rounded-full transition-all" style="width: {{ max($pct, 2) }}%"></div>
                    </div>
                    <span class="w-20 text-right font-semibold whitespace-nowrap">{{ number_format($count) }} <span class="text-gray-400 font-normal text-xs">({{ $pct }}%)</span></span>
                </div>
            @endforeach
        </div>
        <div class="mt-5 pt-4 border-t border-gray-100 grid grid-cols-3 gap-3 text-center">
            <div class="bg-brand-50 rounded-xl py-3">
                <div class="text-[11px] text-brand-600 uppercase tracking-wide">Membership Rev.</div>
                <div class="font-bold text-brand-800 text-sm mt-0.5">{{ format_idr($membershipRevenue) }}</div>
            </div>
            <div class="bg-purple-50 rounded-xl py-3">
                <div class="text-[11px] text-purple-600 uppercase tracking-wide">Hotel Rev.</div>
                <div class="font-bold text-brand-800 text-sm mt-0.5">{{ format_idr($hotelRevenue) }}</div>
            </div>
            <div class="bg-emerald-50 rounded-xl py-3">
                <div class="text-[11px] text-emerald-600 uppercase tracking-wide">F&amp;B Rev.</div>
                <div class="font-bold text-brand-800 text-sm mt-0.5">{{ format_idr($fnbRevenue) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Recent members ===== --}}
<div class="bg-white rounded-2xl shadow mt-4 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-semibold text-brand-800 text-sm">Member Terbaru</h3>
        <a href="{{ route('admin.members.index') }}" class="text-xs text-brand-600 font-medium hover:underline">Lihat semua →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm min-w-[640px]">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-5 py-2.5">Member ID</th><th class="px-5 py-2.5">Name</th><th class="px-5 py-2.5 hidden lg:table-cell">Hotel</th>
                    <th class="px-5 py-2.5">Type</th><th class="px-5 py-2.5 hidden md:table-cell">Level</th><th class="px-5 py-2.5">Status</th><th class="px-5 py-2.5 hidden sm:table-cell">Registered</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($recentMembers as $member)
                    <tr class="hover:bg-brand-50/40">
                        <td class="px-5 py-2.5"><a href="{{ route('admin.members.show', $member) }}" class="font-mono text-xs text-brand-700 hover:underline">{{ $member->member_no }}</a></td>
                        <td class="px-5 py-2.5">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-full bg-gradient-to-br from-brand-600 to-brand-800 text-white flex items-center justify-center text-xs font-semibold shrink-0">
                                    {{ strtoupper(substr($member->full_name, 0, 1)) }}
                                </span>
                                <span class="font-medium truncate max-w-[10rem]">{{ $member->full_name }}</span>
                            </div>
                        </td>
                        <td class="px-5 py-2.5 text-xs hidden lg:table-cell">{{ $member->hotel->name }}</td>
                        <td class="px-5 py-2.5 capitalize">{{ $member->membership_type }}</td>
                        <td class="px-5 py-2.5 hidden md:table-cell">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full" style="background: {{ $member->level->card_color ?? '#1e6bb8' }}"></span>
                                {{ $member->level->name }}
                            </span>
                        </td>
                        <td class="px-5 py-2.5">{!! status_badge($member->status) !!}</td>
                        <td class="px-5 py-2.5 text-xs hidden sm:table-cell">{{ $member->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">Belum ada member.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}"></script>
<script>
(function () {
    // ===== Util =====
    const idr = (v) => 'Rp ' + new Intl.NumberFormat('id-ID').format(v);
    const COLORS = { navy:'#0f2a4a', blue:'#1e6bb8', gold:'#fbbf24', amber:'#f59e0b', emerald:'#10b981', purple:'#8b5cf6', rose:'#f43f5e', sky:'#0ea5e9' };
    Chart.defaults.font.family = "'Segoe UI', Arial, sans-serif";
    Chart.defaults.color = '#6b7280';

    // Plugin: teks di tengah donut
    const centerText = {
        id: 'centerText',
        afterDraw(chart, args, opts) {
            if (!opts || !opts.text) return;
            const { ctx, chartArea: { left, right, top, bottom } } = chart;
            const cx = (left + right) / 2, cy = (top + bottom) / 2;
            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = opts.mainColor || '#0f2a4a';
            ctx.font = 'bold 22px "Segoe UI", Arial';
            ctx.fillText(opts.text, cx, cy - (opts.sub ? 10 : 0));
            if (opts.sub) {
                ctx.fillStyle = '#9ca3af';
                ctx.font = '11px "Segoe UI", Arial';
                ctx.fillText(opts.sub, cx, cy + 14);
            }
            ctx.restore();
        }
    };

    // ===== 1. Member Growth — grouped bar =====
    const growth = {!! json_encode($growth) !!};
    if (document.getElementById('chart-growth')) {
        const ctx = document.getElementById('chart-growth').getContext('2d');
        const paidGrad = ctx.createLinearGradient(0, 0, 0, 280);
        paidGrad.addColorStop(0, '#f59e0b'); paidGrad.addColorStop(1, '#fbbf24');
        const freeGrad = ctx.createLinearGradient(0, 0, 0, 280);
        freeGrad.addColorStop(0, '#10b981'); freeGrad.addColorStop(1, '#34d399');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: growth.map(g => g.label),
                datasets: [
                    { label: 'Paid', data: growth.map(g => g.paid), backgroundColor: paidGrad, borderRadius: 7, maxBarThickness: 26 },
                    { label: 'Free', data: growth.map(g => g.free), backgroundColor: freeGrad, borderRadius: 7, maxBarThickness: 26 },
                ],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8 } } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } },
            },
        });
    }

    // ===== 2. Paid vs Free — donut =====
    if (document.getElementById('chart-paidfree')) {
        const ctx = document.getElementById('chart-paidfree').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Paid', 'Free'],
                datasets: [{ data: [{{ $paidMembers }}, {{ $freeMembers }}],
                    backgroundColor: [COLORS.amber, COLORS.emerald],
                    borderColor: '#ffffff', borderWidth: 3, hoverOffset: 8 }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, padding: 16 } },
                    centerText: { text: '{{ number_format($totalMembers) }}', sub: 'TOTAL MEMBER', mainColor: '#0f2a4a' },
                },
            },
            plugins: [centerText],
        });
    }

    // ===== 3. Level distribution — donut dengan warna kartu =====
    if (document.getElementById('chart-levels')) {
        const ctx = document.getElementById('chart-levels').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($levelDistribution->keys()) !!},
                datasets: [{
                    data: {!! json_encode($levelDistribution->values()) !!},
                    backgroundColor: {!! json_encode($levelColors->values()) !!},
                    borderColor: '#ffffff', borderWidth: 3, hoverOffset: 8,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, padding: 12 } },
                    centerText: { text: '{{ number_format($totalMembers) }}', sub: 'MEMBERS', mainColor: '#0f2a4a' },
                },
            },
            plugins: [centerText],
        });
    }

    // ===== 4. Revenue trend — area line =====
    const revenue = {!! json_encode($revenueGrowth) !!};
    if (document.getElementById('chart-revenue')) {
        const ctx = document.getElementById('chart-revenue').getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 280);
        grad.addColorStop(0, 'rgba(139,92,246,.35)');
        grad.addColorStop(1, 'rgba(139,92,246,0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: revenue.map(r => r.label),
                datasets: [{
                    label: 'Revenue', data: revenue.map(r => r.sum),
                    borderColor: COLORS.purple, borderWidth: 3,
                    backgroundColor: grad, fill: true, tension: .4,
                    pointBackgroundColor: '#fff', pointBorderColor: COLORS.purple,
                    pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 6,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => idr(c.parsed.y) } } },
                scales: {
                    y: { beginAtZero: true, ticks: { callback: (v) => v >= 1e9 ? (v/1e9)+'M' : v >= 1e6 ? (v/1e6)+'jt' : v >= 1e3 ? (v/1e3)+'rb' : v }, grid: { color: '#f3f4f6' } },
                    x: { grid: { display: false } },
                },
            },
        });
    }

    // ===== 5. Revenue per type — horizontal bar =====
    const revByType = {!! json_encode($revenueByType) !!};
    if (document.getElementById('chart-revenue-type')) {
        const ctx = document.getElementById('chart-revenue-type').getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 420, 0);
        grad.addColorStop(0, '#0f2a4a'); grad.addColorStop(1, '#1e6bb8');

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: Object.keys(revByType),
                datasets: [{ data: Object.values(revByType), backgroundColor: grad, borderRadius: 7, maxBarThickness: 24 }],
            },
            options: {
                indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: { callbacks: { label: (c) => idr(c.parsed.x) } } },
                scales: {
                    x: { beginAtZero: true, ticks: { callback: (v) => v >= 1e9 ? (v/1e9)+'M' : v >= 1e6 ? (v/1e6)+'jt' : v >= 1e3 ? (v/1e3)+'rb' : v }, grid: { color: '#f3f4f6' } },
                    y: { grid: { display: false } },
                },
            },
        });
    }

    // ===== 6. Status membership — donut =====
    const statusDist = {!! json_encode($statusDistribution) !!};
    if (document.getElementById('chart-status')) {
        const ctx = document.getElementById('chart-status').getContext('2d');
        const palette = { active: COLORS.emerald, pending_payment: COLORS.gold, pending_review: COLORS.amber, inactive: '#94a3b8', expired: COLORS.rose };
        const labels = { active: 'Active', pending_payment: 'Pending Payment', pending_review: 'Pending Review', inactive: 'Inactive', expired: 'Expired' };

        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(statusDist).map(k => labels[k] || k),
                datasets: [{
                    data: Object.values(statusDist),
                    backgroundColor: Object.keys(statusDist).map(k => palette[k] || '#94a3b8'),
                    borderColor: '#ffffff', borderWidth: 3, hoverOffset: 8,
                }],
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '62%',
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, padding: 12 } },
                    centerText: { text: '{{ number_format($activeMembers) }}', sub: 'ACTIVE', mainColor: '#059669' },
                },
            },
            plugins: [centerText],
        });
    }
})();
</script>
@endpush
@endsection
