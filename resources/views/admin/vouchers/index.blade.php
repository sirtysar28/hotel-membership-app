@extends('layouts.admin')
@section('title', 'Voucer Management')
@section('page-title', 'Voucer Management')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <h2 class="text-xl font-bold text-brand-800">Voucer Management</h2>
        <p class="text-sm text-gray-500 mt-1">Instans voucer member (ID Voucer unik) — penerbitan otomatis saat aktivasi/perpanjangan membership.</p>
    </div>
    <a href="{{ route('admin.redemptions') }}" class="text-sm bg-amber-400 hover:bg-amber-300 text-brand-900 font-bold px-4 py-2.5 rounded-lg whitespace-nowrap">Approval Penukaran →</a>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mt-6">
    @foreach(['AVAILABLE', 'PENDING_APPROVAL', 'REDEEMED', 'EXPIRED', 'CANCELLED'] as $s)
        <a href="{{ route('admin.vouchers.index', ['status' => $s]) }}"
           class="bg-white rounded-2xl shadow p-5 hover:shadow-md transition {{ $status === $s ? 'ring-2 ring-brand-500' : '' }}">
            <div class="text-[11px] uppercase tracking-wider text-gray-500">{{ \App\Models\Voucher::STATUSES[$s] }}</div>
            <div class="text-3xl font-bold text-brand-800 mt-2">{{ $counts[$s] ?? 0 }}</div>
        </a>
    @endforeach
</div>

{{-- Filter --}}
<div class="bg-white rounded-2xl shadow mt-6">
    <form method="GET" class="p-4 sm:px-6 border-b border-gray-100 grid sm:grid-cols-4 gap-3">
        <input type="text" name="q" value="{{ $q }}" placeholder="Cari ID Voucer / member / email…"
               class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-2 focus:ring-brand-500 outline-none">
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-white">
            <option value="">Semua Status</option>
            @foreach(\App\Models\Voucher::STATUSES as $value => $label)
                <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="type" class="border border-gray-300 rounded-lg px-3 py-2.5 text-sm bg-white">
            <option value="">Semua Jenis</option>
            @foreach($types as $type)
                <option value="{{ $type->id }}" @selected($typeId == $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
        <button class="bg-brand-800 text-white px-4 py-2.5 rounded-lg text-sm font-semibold">Filter</button>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 sm:px-6 py-3">ID Voucer</th>
                    <th class="px-4 py-3">Jenis</th>
                    <th class="px-4 py-3">Member</th>
                    <th class="px-4 py-3">Sumber</th>
                    <th class="px-4 py-3">Berlaku s/d</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Penukaran</th>
                    @if(in_array(auth()->user()->role, ['super_admin', 'membership_admin']))
                        <th class="px-4 py-3"></th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($vouchers as $voucher)
                    <tr class="hover:bg-brand-50/40">
                        <td class="px-4 sm:px-6 py-3 font-mono text-xs font-semibold text-brand-800">{{ $voucher->voucher_no }}</td>
                        <td class="px-4 py-3">{{ $voucher->type->name }}</td>
                        <td class="px-4 py-3">
                            @if(in_array(auth()->user()->role, ['super_admin', 'hotel_admin', 'membership_admin']))
                                <a href="{{ route('admin.members.show', $voucher->member) }}" class="font-medium hover:underline">{{ $voucher->member->full_name }}</a>
                            @else
                                <span class="font-medium">{{ $voucher->member->full_name }}</span>
                            @endif
                            <div class="text-[11px] text-gray-400">{{ $voucher->member->member_no }} · {{ $voucher->member->hotel->code }}</div>
                        </td>
                        <td class="px-4 py-3 text-xs">{{ ucfirst(str_replace('_', ' ', $voucher->source)) }}</td>
                        <td class="px-4 py-3">{{ $voucher->expires_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3">{!! status_badge($voucher->status) !!}</td>
                        <td class="px-4 py-3 text-xs">
                            @if($voucher->redeemed_at)
                                {{ $voucher->redeemed_at->format('d M Y H:i') }}<br><span class="text-gray-400">by {{ $voucher->approver?->name ?? '-' }}</span>
                            @elseif($voucher->requested_at)
                                <span class="text-amber-600">req {{ $voucher->requested_at->format('d M Y H:i') }}</span><br><span class="text-gray-400">by {{ $voucher->requester?->name ?? '-' }}</span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        @if(in_array(auth()->user()->role, ['super_admin', 'membership_admin']))
                            <td class="px-4 py-3 text-right">
                                @if(in_array($voucher->status, [\App\Models\Voucher::STATUS_AVAILABLE, \App\Models\Voucher::STATUS_PENDING_APPROVAL]))
                                    <form method="POST" action="{{ route('admin.vouchers.cancel', $voucher) }}"
                                          onsubmit="return confirm('Batalkan voucer {{ $voucher->voucher_no }}?');">
                                        @csrf
                                        <input type="hidden" name="reason" value="Dibatalkan oleh admin">
                                        <button class="text-xs text-red-600 hover:text-red-800 font-medium">Batalkan</button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-6 py-10 text-center text-gray-400">Tidak ada voucer sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $vouchers->links() }}</div>
@endsection
