@extends('layouts.admin')
@section('title', 'Staff — Penukaran Voucer')
@section('page-title', 'Staff / Penukaran Voucer')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="font-semibold text-brand-800">Penukaran Voucer Member</h2>
        <p class="text-sm text-gray-500 mt-1">
            Cari voucer berdasarkan <strong>ID Voucer</strong> (contoh: <code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">VCH-260001-0001</code>) atau cari member untuk melihat voucernya.
            Pengajuan penukaran akan <span class="text-amber-700 font-medium">menunggu persetujuan manajer</span> sebelum dicatat.
        </p>

        <form method="GET" class="mt-4 flex gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="ID Voucer / Member ID / email / phone / nama…"
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
            <button class="bg-brand-800 text-white px-6 py-2.5 rounded-lg font-semibold text-sm">Cari</button>
        </form>
    </div>

    {{-- Voucher ditemukan --}}
    @if($voucher)
        <div class="bg-white rounded-2xl shadow mt-4 p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-400">ID Voucer</div>
                    <div class="font-mono font-bold text-brand-800 text-lg">{{ $voucher->voucher_no }}</div>
                    <div class="text-sm text-gray-600 mt-1">{{ $voucher->type->name }}</div>
                </div>
                <div class="text-right">
                    {!! status_badge($voucher->status) !!}
                    <div class="text-xs text-gray-400 mt-1">Berlaku s/d {{ $voucher->expires_at?->format('d M Y') ?? '-' }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-4 text-sm">
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="text-[11px] uppercase text-gray-400">Member</div>
                    <div class="font-semibold mt-0.5">{{ $voucher->member->full_name }}</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="text-[11px] uppercase text-gray-400">Member ID</div>
                    <div class="font-mono text-xs mt-1">{{ $voucher->member->member_no }}</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="text-[11px] uppercase text-gray-400">Level</div>
                    <div class="font-semibold mt-0.5">{{ $voucher->member->level->name }}</div>
                </div>
                <div class="bg-gray-50 rounded-xl p-3">
                    <div class="text-[11px] uppercase text-gray-400">Status Member</div>
                    <div class="mt-1">{!! status_badge($voucher->member->status) !!}</div>
                </div>
            </div>

            @if($voucher->isAvailable() && $voucher->member->status === 'active')
                {{-- Form ajukan penukaran --}}
                <form method="POST" action="{{ route('staff.vouchers.request', $voucher) }}" class="mt-6 border-t border-gray-100 pt-5 grid sm:grid-cols-2 gap-4">
                    @csrf
                    <div class="sm:col-span-2">
                        <div class="text-sm font-semibold text-brand-800 mb-3">Ajukan Penukaran</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hotel / Unit *</label>
                        <select name="hotel_id" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                            @foreach($hotels as $hotel)
                                <option value="{{ $hotel->id }}" @selected(old('hotel_id', auth()->user()->hotel_id ?? $voucher->member->hotel_id) == $hotel->id)>{{ $hotel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Outlet / Lokasi *</label>
                        <input name="outlet" value="{{ old('outlet') }}" required placeholder="Cipta Restaurant / Sky Bar / Gym…"
                               class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Catatan (opsional)</label>
                        <textarea name="request_note" rows="2" maxlength="300" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">{{ old('request_note') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <button class="bg-amber-400 hover:bg-amber-300 text-brand-900 font-bold px-8 py-3 rounded-xl w-full sm:w-auto">AJUKAN PENUKARAN</button>
                        <p class="text-xs text-gray-400 mt-2">Voucer akan berstatus PENDING_APPROVAL hingga disetujui manajer — tidak dapat diajukan ganda.</p>
                    </div>
                </form>
            @elseif($voucher->status === \App\Models\Voucher::STATUS_PENDING_APPROVAL)
                <div class="mt-5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm">
                    ⏳ Voucer ini sudah diajukan penukaran pada {{ $voucher->requested_at?->format('d M Y, H:i') }} oleh {{ $voucher->requester?->name ?? '-' }} — menunggu persetujuan manajer.
                </div>
            @elseif($voucher->status === \App\Models\Voucher::STATUS_REDEEMED)
                <div class="mt-5 bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 text-sm">
                    ✓ Voucer sudah ditukarkan pada {{ $voucher->redeemed_at?->format('d M Y, H:i') }} (disetujui {{ $voucher->approver?->name ?? '-' }}).
                </div>
            @else
                <div class="mt-5 bg-gray-50 border border-gray-200 text-gray-600 rounded-xl px-4 py-3 text-sm">
                    Voucer tidak dapat diajukan (status: {{ \App\Models\Voucher::STATUSES[$voucher->status] ?? $voucher->status }}).{{ !$voucher->isAvailable() && $voucher->expires_at && $voucher->expires_at->isPast() ? ' Masa berlaku voucer sudah berakhir.' : '' }}
                </div>
            @endif
        </div>
    @elseif($q !== '' && $members->isEmpty())
        <div class="bg-white rounded-2xl shadow p-8 mt-4 text-center text-gray-500 text-sm">
            Tidak ada voucer atau member yang cocok dengan "<strong>{{ $q }}</strong>".
        </div>
    @elseif($members->isNotEmpty())
        <div class="bg-white rounded-2xl shadow mt-4 p-6">
            <h3 class="font-semibold text-brand-800">Voucer Tersedia per Member</h3>
            <div class="mt-4 space-y-4">
                @foreach($members as $member)
                    <div class="border border-gray-200 rounded-xl p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <span class="font-semibold">{{ $member->full_name }}</span>
                                <span class="font-mono text-xs text-gray-400 ml-2">{{ $member->member_no }}</span>
                                <div class="text-xs text-gray-400">{{ $member->email }} · {{ $member->hotel->name }}</div>
                            </div>
                            <a href="{{ route('staff.members.show', $member) }}" class="text-xs text-brand-600 font-medium hover:underline">Profil member →</a>
                        </div>
                        @if($member->vouchers->isNotEmpty())
                            <div class="flex flex-wrap gap-2 mt-3">
                                @foreach($member->vouchers as $v)
                                    <a href="{{ route('staff.vouchers', ['q' => $v->voucher_no]) }}"
                                       class="inline-flex flex-col border border-gray-200 hover:border-brand-400 hover:bg-brand-50 rounded-lg px-3 py-2 transition">
                                        <span class="font-mono text-xs font-semibold text-brand-800">{{ $v->voucher_no }}</span>
                                        <span class="text-[11px] text-gray-500">{{ $v->type->name }} · s/d {{ $v->expires_at?->format('d M Y') ?? '-' }}</span>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="text-xs text-gray-400 mt-2">Tidak ada voucer berstatus AVAILABLE.</div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
