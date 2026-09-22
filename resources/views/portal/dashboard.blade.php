@extends('layouts.portal')
@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-brand-800">HELLO, {{ strtoupper($member->full_name) }}</h1>
        <p class="text-sm text-gray-500">{{ $member->hotel->name }} · {{ ucfirst($member->membership_type) }} Membership</p>
    </div>
    <div class="text-sm">{!! status_badge($member->status) !!}</div>
</div>

<div class="grid lg:grid-cols-3 gap-6 mt-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex flex-wrap gap-6">
                <div>
                    <div class="text-[11px] uppercase tracking-wider text-gray-500">Member ID</div>
                    <div class="font-mono font-semibold text-brand-800 mt-1">{{ $member->member_no }}</div>
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-wider text-gray-500">Total Visit</div>
                    <div class="text-2xl font-bold text-brand-800 mt-1">{{ $member->total_visits }}</div>
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-wider text-gray-500">Total Spending</div>
                    <div class="text-2xl font-bold text-brand-800 mt-1">{{ format_idr($member->total_spending) }}</div>
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-wider text-gray-500">Current Level</div>
                    <div class="mt-1"><span class="inline-block px-3 py-1 rounded-lg text-white font-bold text-sm" style="background: {{ $member->level->card_color }}">{{ strtoupper($member->level->name) }}</span></div>
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-wider text-gray-500">Voucer Tersedia</div>
                    <a href="{{ route('portal.vouchers') }}" class="text-2xl font-bold text-brand-800 mt-1 hover:underline inline-flex items-center gap-2">{{ $availableVouchers }} <span class="text-sm font-medium text-brand-600">→</span></a>
                </div>
            </div>

            @if($progress['next'])
                <div class="mt-6 border-t border-gray-100 pt-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Next Level: <strong>{{ strtoupper($progress['next']->name) }}</strong></span>
                        <span class="font-semibold text-brand-700">{{ $progress['percent'] }}%</span>
                    </div>
                    <div class="mt-2 h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all" style="width: {{ $progress['percent'] }}%; background: linear-gradient(90deg,#1e6bb8,{{ $progress['next']->card_color }})"></div>
                    </div>
                    @if(($rule = $progress['rule'] ?? null))
                        <div class="mt-2 text-xs text-gray-400">
                            Persyaratan: {{ $rule->min_visit ? $rule->min_visit . ' visit' : '' }}{{ $rule->min_visit && $rule->min_spending > 0 ? ' & ' : '' }}{{ $rule->min_spending > 0 ? format_idr($rule->min_spending) . ' spending' : '' }}
                        </div>
                    @endif
                </div>
            @else
                <div class="mt-6 border-t border-gray-100 pt-4 text-sm text-amber-700 font-medium">⭐ Anda berada di level tertinggi. Terima kasih atas loyalitas Anda!</div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-brand-800">Visit Terakhir</h3>
                <a href="{{ route('portal.visits') }}" class="text-sm text-brand-600 font-medium hover:underline">Lihat semua →</a>
            </div>
            <div class="divide-y mt-2">
                @forelse($recentVisits as $visit)
                    <div class="py-3 flex justify-between items-center text-sm">
                        <div>
                            <div class="font-medium">{{ $visit->visit_date->format('d M Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $visit->hotel->name }} · {{ $visit->transaction?->typeLabel() ?? 'Visit' }}</div>
                        </div>
                        <div class="text-gray-600">{{ format_idr($visit->transaction?->amount ?? 0) }}</div>
                    </div>
                @empty
                    <div class="py-6 text-center text-gray-400 text-sm">Belum ada visit tercatat.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold text-brand-800 mb-4">Membership Card</h3>
            @include('partials.digital-card', ['member' => $member])
            <a href="{{ route('portal.card') }}" class="block mt-4 text-center text-sm text-brand-600 font-medium hover:underline">Lihat kartu & QR →</a>
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold text-brand-800">Membership Terms</h3>
            <ul class="mt-2 text-xs text-gray-500 space-y-2 list-disc pl-4">
                <li>Level membership dievaluasi otomatis setiap transaksi baru.</li>
                <li>Level berdasarkan jumlah visit dan/atau total spending sesuai ketentuan berlaku.</li>
                <li>Membership berlaku {{ \App\Models\Setting::get('membership_validity_years', 1) }} tahun dan dapat diperpanjang.</li>
                <li>QR code pada digital card untuk identifikasi saat stay &amp; transaksi F&amp;B.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
