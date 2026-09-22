@extends('layouts.admin')
@section('title', 'Detail Member')
@section('page-title', 'Members — Detail')

@section('content')
<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Info utama --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div class="text-xs text-gray-400 font-mono">{{ $member->member_no }}</div>
                    <h2 class="text-xl font-bold text-brand-800">{{ $member->full_name }}</h2>
                    <div class="text-sm text-gray-500">{{ $member->email }} · {{ $member->fullPhone() }}</div>
                </div>
                <div class="flex flex-col items-end gap-2">
                    {!! status_badge($member->status) !!}
                    <div class="text-sm font-semibold text-brand-700">{{ $member->level->name }} · {{ ucfirst($member->membership_type) }} · {{ $member->hotel->name }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                <div class="bg-brand-50 rounded-xl p-4"><div class="text-[11px] uppercase text-gray-500">Total Visit</div><div class="font-bold text-brand-800 mt-1">{{ $member->total_visits }}</div></div>
                <div class="bg-brand-50 rounded-xl p-4"><div class="text-[11px] uppercase text-gray-500">Total Spending</div><div class="font-bold text-brand-800 mt-1">{{ format_idr($member->total_spending) }}</div></div>
                <div class="bg-brand-50 rounded-xl p-4"><div class="text-[11px] uppercase text-gray-500">Joined</div><div class="font-bold text-brand-800 mt-1">{{ $member->joined_at?->format('d M Y') ?? '-' }}</div></div>
                <div class="bg-brand-50 rounded-xl p-4"><div class="text-[11px] uppercase text-gray-500">Valid Until</div><div class="font-bold text-brand-800 mt-1">{{ $member->valid_until?->format('d M Y') ?? '-' }}</div></div>
            </div>

            <div class="grid sm:grid-cols-2 gap-x-6 gap-y-2 mt-6 text-sm border-t border-gray-100 pt-4">
                <div class="flex justify-between"><span class="text-gray-500">Date of Birth</span><span>{{ $member->dob?->format('d M Y') ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Gender</span><span>{{ gender_label($member->gender) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">ID</span><span>{{ strtoupper($member->id_type ?? '') }} {{ $member->id_number ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Company</span><span>{{ $member->company ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Occupation</span><span>{{ $member->occupation ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Bukti Fisik (Acc.)</span><span>{{ $member->proof_reference ?? '-' }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Address</span><span class="text-right max-w-[220px]">{{ $member->address ?? '-' }}</span></div>
            </div>

            {{-- Aksi --}}
            <div class="flex flex-wrap gap-2 mt-6 border-t border-gray-100 pt-4">
                <a href="{{ route('admin.members.edit', $member) }}" class="bg-brand-800 text-white text-xs font-semibold px-4 py-2 rounded-lg">Edit Data</a>
                <a href="{{ route('staff.members.show', $member) }}" class="bg-amber-400 text-brand-900 text-xs font-semibold px-4 py-2 rounded-lg">Redeem Visit</a>
                <form method="POST" action="{{ route('admin.members.reissue-card', $member) }}">@csrf
                    <button class="border border-brand-200 text-brand-700 text-xs font-semibold px-4 py-2 rounded-lg">Re-issue Digital Card</button>
                </form>
                <form method="POST" action="{{ route('admin.members.renewal', $member) }}">@csrf
                    <button class="border border-brand-200 text-brand-700 text-xs font-semibold px-4 py-2 rounded-lg">Kirim Renewal Reminder</button>
                </form>
            </div>

            {{-- Ganti status --}}
            <form method="POST" action="{{ route('admin.members.status', $member) }}" class="flex items-end gap-2 mt-4 bg-gray-50 rounded-xl p-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Ubah Status</label>
                    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                        @foreach(['pending_payment', 'pending_review', 'active', 'inactive', 'expired'] as $status)
                            <option value="{{ $status }}" @selected($member->status === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="bg-brand-600 text-white text-sm px-4 py-2 rounded-lg">Update</button>
            </form>
        </div>

        {{-- Payments --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex justify-between items-center">
                <h3 class="font-semibold text-brand-800">Payments</h3>
                <a href="{{ route('admin.payments.index', ['q' => $member->member_no]) }}" class="text-xs text-brand-600 hover:underline">Kelola →</a>
            </div>
            <div class="overflow-x-auto mt-3">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr><th class="px-3 py-2">Invoice</th><th class="px-3 py-2">Amount</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Verified</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($member->payments as $payment)
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs">{{ $payment->invoice?->invoice_no ?? '-' }}</td>
                                <td class="px-3 py-2">{{ format_idr($payment->amount) }}</td>
                                <td class="px-3 py-2">{!! status_badge($payment->status) !!}</td>
                                <td class="px-3 py-2 text-xs">{{ $payment->verifier?->name ?? '-' }} {{ $payment->verified_at?->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400">Tidak ada payment.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Transaksi & visit --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold text-brand-800">Transaksi Terakhir</h3>
            <div class="overflow-x-auto mt-3">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr><th class="px-3 py-2">Date</th><th class="px-3 py-2">Type</th><th class="px-3 py-2">No</th><th class="px-3 py-2">Hotel</th><th class="px-3 py-2 text-right">Amount</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($member->transactions as $txn)
                            <tr>
                                <td class="px-3 py-2">{{ $txn->transaction_date->format('d/m/Y') }}</td>
                                <td class="px-3 py-2">{{ $txn->typeLabel() }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $txn->transaction_no }}</td>
                                <td class="px-3 py-2 text-xs">{{ $txn->hotel->name }}</td>
                                <td class="px-3 py-2 text-right">{{ format_idr($txn->amount) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-3 py-4 text-center text-gray-400">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Benefit member --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold text-brand-800">Benefit / Voucher Member</h3>
            <div class="overflow-x-auto mt-3">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                        <tr><th class="px-3 py-2">Benefit</th><th class="px-3 py-2">Granted</th><th class="px-3 py-2">Expires</th><th class="px-3 py-2">Status</th></tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($member->benefits as $mb)
                            <tr>
                                <td class="px-3 py-2">{{ $mb->benefit->name }} <span class="text-xs text-gray-400">{{ $mb->benefit->value }}</span></td>
                                <td class="px-3 py-2 text-xs">{{ $mb->granted_at?->format('d/m/Y') }}</td>
                                <td class="px-3 py-2 text-xs">{{ $mb->expires_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-3 py-2">{!! status_badge($mb->status) !!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-3 py-4 text-center text-gray-400">Belum ada benefit.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Voucer & periode keanggotaan (CPC v2.0 §3, §5) --}}
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <h3 class="font-semibold text-brand-800">Voucer &amp; Periode Keanggotaan</h3>
                <a href="{{ route('admin.vouchers.index', ['q' => $member->member_no]) }}" class="text-xs bg-brand-800 text-white px-3 py-2 rounded-lg font-semibold">Kelola Voucer →</a>
            </div>

            <div class="text-xs text-gray-500">
                Available: <strong>{{ $voucherCounts['AVAILABLE'] ?? 0 }}</strong> ·
                Pending: <strong>{{ $voucherCounts['PENDING_APPROVAL'] ?? 0 }}</strong> ·
                Redeemed: <strong>{{ $voucherCounts['REDEEMED'] ?? 0 }}</strong> ·
                Expired: <strong>{{ $voucherCounts['EXPIRED'] ?? 0 }}</strong> ·
                Cancelled: <strong>{{ $voucherCounts['CANCELLED'] ?? 0 }}</strong>
            </div>

            @if($member->vouchers->isNotEmpty())
                <div class="overflow-x-auto mt-3">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                            <tr><th class="px-3 py-2">ID Voucer</th><th class="px-3 py-2">Jenis</th><th class="px-3 py-2">Berlaku s/d</th><th class="px-3 py-2">Status</th></tr>
                        </thead>
                        <tbody class="divide-y">
                            @foreach($member->vouchers->take(10) as $v)
                                <tr>
                                    <td class="px-3 py-2 font-mono text-xs">{{ $v->voucher_no }}</td>
                                    <td class="px-3 py-2">{{ $v->type->name }}</td>
                                    <td class="px-3 py-2">{{ $v->expires_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="px-3 py-2">{!! status_badge($v->status) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($member->vouchers->count() > 10)
                        <div class="text-xs text-gray-400 text-center py-2">+{{ $member->vouchers->count() - 10 }} voucer lainnya…</div>
                    @endif
                </div>
            @else
                <div class="text-sm text-gray-400 py-3">Belum ada voucer.</div>
            @endif

            @if($member->periods->isNotEmpty())
                <div class="border-t border-gray-100 mt-4 pt-3">
                    <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Riwayat Periode</div>
                    <div class="space-y-1.5 text-sm">
                        @foreach($member->periods as $period)
                            <div class="flex justify-between items-center">
                                <span>Periode #{{ $period->period_no }} · {{ $period->start_date->format('d M Y') }} – {{ $period->end_date->format('d M Y') }}</span>
                                <span class="text-xs {{ $period->status === 'active' ? 'text-emerald-600 font-semibold' : 'text-gray-400' }}">{{ ucfirst($period->status) }}{{ $period->price > 0 ? ' · ' . format_idr((float) $period->price) : '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Kolom kanan: kartu + evaluasi level --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold text-brand-800 mb-4">Digital Card</h3>
            @include('partials.digital-card', ['member' => $member])
        </div>

        @php($qualified = $engine->qualifiedLevel($member))
        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold text-brand-800">Evaluasi Level (Rule Engine)</h3>
            <div class="text-sm mt-3 space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Current Level</span><span class="font-semibold">{{ $member->level->name }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Qualified Level</span>
                    <span class="font-semibold {{ $qualified && $qualified->level_id !== $member->level_id ? 'text-emerald-600' : '' }}">{{ $qualified?->level->name ?? '-' }}</span>
                </div>
            </div>
            @if($qualified && $qualified->level_id !== $member->level_id)
                <div class="mt-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs rounded-lg p-3">
                    Member memenuhi syarat level {{ $qualified->level->name }}. Upgrade berjalan otomatis saat transaksi/redeem berikutnya.
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow p-6">
            <h3 class="font-semibold text-brand-800">Visits Terakhir</h3>
            <div class="divide-y mt-2 text-sm">
                @forelse($member->visits as $visit)
                    <div class="py-2 flex justify-between">
                        <div>{{ $visit->visit_date->format('d M Y') }}<div class="text-xs text-gray-400">{{ $visit->hotel->name }}</div></div>
                        <div class="text-xs text-gray-400">{{ $visit->staff?->name }}</div>
                    </div>
                @empty
                    <div class="py-4 text-center text-gray-400">Belum ada visit.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
