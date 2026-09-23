@extends('layouts.portal')
@section('title', 'Vouchers')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-brand-800">MY VOUCHERS</h1>
        <p class="text-sm text-gray-500">Voucher membership Anda — berlaku mengikuti masa berlaku periode keanggotaan.</p>
    </div>
    <div class="text-sm">{!! status_badge($member->status) !!}</div>
</div>

{{-- Summary cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
    @foreach([
        ['status' => 'AVAILABLE', 'label' => 'Tersedia', 'icon' => '🎟'],
        ['status' => 'PENDING_APPROVAL', 'label' => 'Menunggu Persetujuan', 'icon' => '⏳'],
        ['status' => 'REDEEMED', 'label' => 'Sudah Ditukarkan', 'icon' => '✓'],
        ['status' => 'EXPIRED', 'label' => 'Kedaluwarsa', 'icon' => '⌛'],
    ] as $card)
        <a href="{{ route('portal.vouchers', ['status' => $card['status']]) }}"
           class="bg-white rounded-2xl shadow p-5 hover:shadow-md transition {{ $status === $card['status'] ? 'ring-2 ring-brand-500' : '' }}">
            <div class="flex items-center justify-between">
                <div class="text-[11px] uppercase tracking-wider text-gray-500">{{ $card['label'] }}</div>
                <span class="text-lg">{{ $card['icon'] }}</span>
            </div>
            <div class="text-3xl font-bold text-brand-800 mt-2">{{ $counts[$card['status']] ?? 0 }}</div>
        </a>
    @endforeach
</div>

{{-- Filter --}}
<div class="bg-white rounded-2xl shadow mt-6">
    <div class="p-4 sm:px-6 border-b border-gray-100 flex flex-wrap items-center gap-2">
        <span class="text-sm text-gray-500 mr-1">Status:</span>
        <a href="{{ route('portal.vouchers') }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $status === '' ? 'bg-brand-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Semua</a>
        @foreach(['AVAILABLE', 'PENDING_APPROVAL', 'REDEEMED', 'EXPIRED', 'CANCELLED'] as $s)
            <a href="{{ route('portal.vouchers', ['status' => $s]) }}" class="px-3 py-1.5 rounded-lg text-xs font-medium {{ $status === $s ? 'bg-brand-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">{{ \App\Models\Voucher::STATUSES[$s] }}</a>
        @endforeach
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 sm:px-6 py-3">ID Voucer</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Diterbitkan</th>
                    <th class="px-4 py-3">Berlaku s/d</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($vouchers as $voucher)
                    <tr class="hover:bg-brand-50/40 {{ $voucher->status === 'EXPIRED' || $voucher->status === 'CANCELLED' ? 'opacity-60' : '' }}">
                        <td class="px-4 sm:px-6 py-3 font-mono text-xs font-semibold text-brand-800">{{ $voucher->voucher_no }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $voucher->type->name }}</div>
                            @if($voucher->discount_percent)
                                <div class="text-[11px] font-semibold text-emerald-700">{{ $voucher->discountLabel() }}</div>
                            @endif
                            @if($voucher->period)
                                <div class="text-[11px] text-gray-400">Periode {{ $voucher->period->label() }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $voucher->issued_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $voucher->expires_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3">{!! status_badge($voucher->status) !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-400">Belum ada voucher{{ $status ? ' dengan status ' . \App\Models\Voucher::STATUSES[$status] : '' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $vouchers->links() }}</div>

{{-- Riwayat pengajuan penukaran --}}
<div class="bg-white rounded-2xl shadow mt-6 overflow-x-auto">
    <div class="p-4 sm:px-6 border-b border-gray-100">
        <h3 class="font-semibold text-brand-800">Riwayat Penukaran</h3>
        <p class="text-xs text-gray-400 mt-0.5">Penukaran diajukan staf outlet dan disetujui manajer hotel.</p>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 sm:px-6 py-3">Waktu</th>
                <th class="px-4 py-3">ID Voucer</th>
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3">Lokasi</th>
                <th class="px-4 py-3">Diajukan Oleh</th>
                <th class="px-4 py-3">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($requests as $req)
                <tr>
                    <td class="px-4 sm:px-6 py-3">{{ $req->created_at->format('d M Y, H:i') }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $req->voucher_no }}</td>
                    <td class="px-4 py-3">{{ $req->type?->name ?? $req->voucher?->type?->name ?? '-' }}</td>
                    <td class="px-4 py-3">{{ ($req->hotel?->name ?? '-') . ($req->outlet ? ' — ' . $req->outlet : '') }}</td>
                    <td class="px-4 py-3">{{ $req->requester?->name ?? '-' }}</td>
                    <td class="px-4 py-3">
                        {!! status_badge($req->status) !!}
                        @if($req->status === 'REJECTED' && $req->rejection_reason)
                            <div class="text-[11px] text-gray-400 mt-1">Alasan: {{ $req->rejection_reason }}</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada riwayat penukaran.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
