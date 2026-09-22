@extends('layouts.admin')
@section('title', 'Member Information')
@section('page-title', 'Member Information')

@section('content')
<div class="max-w-5xl grid lg:grid-cols-3 gap-6">
    {{-- Member info --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-400 font-mono">{{ $member->member_no }}</div>
                    <h2 class="text-xl font-bold text-brand-800">{{ $member->full_name }}</h2>
                    <div class="text-sm text-gray-500">{{ $member->email }} · {{ $member->fullPhone() }}</div>
                </div>
                <div class="text-right space-y-1">
                    {!! status_badge($member->status) !!}
                    <div class="text-sm font-semibold text-brand-700 mt-1">{{ $member->level->name }} · {{ ucfirst($member->membership_type) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                <div class="bg-brand-50 rounded-xl p-4">
                    <div class="text-[11px] uppercase text-gray-500">Current Level</div>
                    <div class="font-bold text-brand-800 mt-1">{{ $member->level->name }}</div>
                </div>
                <div class="bg-brand-50 rounded-xl p-4">
                    <div class="text-[11px] uppercase text-gray-500">Total Visit</div>
                    <div class="font-bold text-brand-800 mt-1">{{ $member->total_visits }}</div>
                </div>
                <div class="bg-brand-50 rounded-xl p-4">
                    <div class="text-[11px] uppercase text-gray-500">Total Spending</div>
                    <div class="font-bold text-brand-800 mt-1">{{ format_idr($member->total_spending) }}</div>
                </div>
                <div class="bg-brand-50 rounded-xl p-4">
                    <div class="text-[11px] uppercase text-gray-500">Valid Until</div>
                    <div class="font-bold text-brand-800 mt-1">{{ $member->valid_until?->format('d M Y') ?? '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Redeem form --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold text-brand-800">Redeem Visit / Input Transaksi</h3>
            <form method="POST" action="{{ route('staff.members.redeem', $member) }}" class="mt-4 grid sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Visit Date *</label>
                    <input type="date" name="visit_date" value="{{ old('visit_date', now()->toDateString()) }}" max="{{ now()->toDateString() }}" required
                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Hotel *</label>
                    <select name="hotel_id" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                        @foreach(\App\Models\Hotel::where('is_active', true)->get() as $hotel)
                            <option value="{{ $hotel->id }}" @selected(old('hotel_id', $member->hotel_id) == $hotel->id)>{{ $hotel->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Transaction Type *</label>
                    <select name="type" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                        @foreach(\App\Models\Transaction::TYPES as $value => $label)
                            <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Transaction No (manual, opsional)</label>
                    <input name="transaction_no" value="{{ old('transaction_no') }}" placeholder="HTL-{{ now()->format('Ymd') }}-001"
                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Outlet</label>
                    <input name="outlet" value="{{ old('outlet') }}" placeholder="Front Office / Cipta Restaurant / …"
                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Amount (Rp) *</label>
                    <input type="number" name="amount" min="0" step="1000" value="{{ old('amount') }}" required
                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="counts_as_visit" value="0">
                    <input type="checkbox" name="counts_as_visit" value="1" checked class="rounded"> Count as visit (+1)
                </label>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="2" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">{{ old('notes') }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <button class="bg-amber-400 hover:bg-amber-300 text-brand-900 font-bold px-8 py-3 rounded-xl w-full sm:w-auto">SUBMIT REDEEM</button>
                </div>
            </form>
        </div>

        {{-- Voucer member (CPC v2.0 §5, §10) --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h3 class="font-semibold text-brand-800">Voucer Member</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Tersedia {{ $voucherCounts['AVAILABLE'] ?? 0 }} · Menunggu persetujuan {{ $voucherCounts['PENDING_APPROVAL'] ?? 0 }} · Sudah ditukar {{ $voucherCounts['REDEEMED'] ?? 0 }}</p>
                </div>
                <a href="{{ route('staff.vouchers', ['q' => $member->member_no]) }}" class="text-xs bg-brand-800 text-white px-3 py-2 rounded-lg font-semibold">Ajukan Penukaran →</a>
            </div>
            <div class="overflow-x-auto mt-3">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr><th class="px-3 py-2">ID Voucer</th><th class="px-3 py-2">Jenis</th><th class="px-3 py-2">Berlaku s/d</th><th class="px-3 py-2">Status</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($memberVouchers as $v)
                            <tr class="hover:bg-brand-50/40">
                                <td class="px-3 py-2">
                                    <a href="{{ route('staff.vouchers', ['q' => $v->voucher_no]) }}" class="font-mono text-xs font-semibold text-brand-700 hover:underline">{{ $v->voucher_no }}</a>
                                </td>
                                <td class="px-3 py-2">{{ $v->type->name }}</td>
                                <td class="px-3 py-2">{{ $v->expires_at?->format('d M Y') ?? '-' }}</td>
                                <td class="px-3 py-2">{!! status_badge($v->status) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400">Belum ada voucer.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent transactions --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold text-brand-800">Transaksi Terakhir</h3>
            <div class="overflow-x-auto mt-3">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr><th class="px-3 py-2">Date</th><th class="px-3 py-2">Type</th><th class="px-3 py-2">No</th><th class="px-3 py-2">Outlet</th><th class="px-3 py-2 text-right">Amount</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($recentTransactions as $txn)
                            <tr>
                                <td class="px-3 py-2">{{ $txn->transaction_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">{{ $txn->typeLabel() }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $txn->transaction_no }}</td>
                                <td class="px-3 py-2">{{ $txn->outlet ?? '-' }}</td>
                                <td class="px-3 py-2 text-right">{{ format_idr($txn->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-4 text-center text-gray-400">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Digital card --}}
    <div>
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold text-brand-800 mb-4">Digital Card</h3>
            @include('partials.digital-card', ['member' => $member])
        </div>
    </div>
</div>
@endsection
