@extends('layouts.admin')
@section('title', 'Approval Penukaran Voucer')
@section('page-title', 'Approval Penukaran Voucer')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3">
    <div>
        <h2 class="text-xl font-bold text-brand-800">Approval Penukaran Voucer</h2>
        <p class="text-sm text-gray-500 mt-1">
            Pengajuan penukaran oleh staf memerlukan persetujuan manajer sebelum voucer berstatus REDEEMED.
            @if(auth()->user()->isManagerLike()) Anda berwenang menyetujui/menolak. @else Akun Anda hanya dapat melihat riwayat. @endif
        </p>
    </div>
    <a href="{{ route('admin.vouchers.index') }}" class="text-sm bg-brand-800 text-white px-4 py-2.5 rounded-lg font-semibold whitespace-nowrap">← Voucer Management</a>
</div>

{{-- Antrian pending --}}
<div class="bg-white rounded-2xl shadow mt-6 overflow-x-auto">
    <div class="p-4 sm:px-6 border-b border-gray-100 flex items-center justify-between">
        <h3 class="font-semibold text-brand-800">Menunggu Persetujuan ({{ $pending->total() }})</h3>
        <span class="text-xs bg-amber-100 text-amber-800 px-2.5 py-1 rounded-full font-medium">PENDING_APPROVAL</span>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 sm:px-6 py-3">Diajukan</th>
                <th class="px-4 py-3">Voucer</th>
                <th class="px-4 py-3">Member</th>
                <th class="px-4 py-3">Lokasi / Outlet</th>
                <th class="px-4 py-3">Diajukan Oleh</th>
                @if($canDecide)
                    <th class="px-4 py-3 text-right">Keputusan</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($pending as $req)
                <tr>
                    <td class="px-4 sm:px-6 py-3">{{ $req->created_at->format('d M Y, H:i') }}</td>
                    <td class="px-4 py-3">
                        <div class="font-mono text-xs font-semibold text-brand-800">{{ $req->voucher_no }}</div>
                        <div class="text-[11px] text-gray-400">{{ $req->voucher?->type?->name ?? $req->type?->name ?? '-' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $req->member->full_name }}</div>
                        <div class="text-[11px] text-gray-400">{{ $req->member->member_no }} · {{ $req->member->hotel->code }}</div>
                    </td>
                    <td class="px-4 py-3">
                        {{ $req->hotel?->name ?? '-' }}@if($req->outlet) — {{ $req->outlet }}@endif
                        @if($req->request_note)
                            <div class="text-[11px] text-gray-400 mt-0.5">“{{ $req->request_note }}”</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">{{ $req->requester?->name ?? '-' }}</td>
                    @if($canDecide)
                        <td class="px-4 py-3">
                            <div class="flex flex-col sm:flex-row gap-2 justify-end">
                                @unless($req->requested_by === auth()->id())
                                    <form method="POST" action="{{ route('admin.redemptions.approve', $req) }}"
                                          onsubmit="return confirm('SETUJUI penukaran {{ $req->voucher_no }}? Voucer akan berstatus REDEEMED & email notifikasi terkirim ke member.');">
                                        @csrf
                                        <button class="w-full bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2 rounded-lg whitespace-nowrap">✓ SETUJUI</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.redemptions.reject', $req) }}" class="reject-form flex gap-1"
                                          data-voucher="{{ $req->voucher_no }}">
                                        @csrf
                                        <input type="text" name="rejection_reason" placeholder="Alasan penolakan…" required maxlength="300"
                                               class="w-40 border border-gray-300 rounded-lg px-2.5 py-2 text-xs focus:ring-2 focus:ring-red-300 outline-none">
                                        <button class="bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold px-4 py-2 rounded-lg whitespace-nowrap">✕ TOLAK</button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400 italic">Anda pengaju — tidak bisa memutuskan sendiri</span>
                                @endunless
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $canDecide ? 6 : 5 }}" class="px-6 py-10 text-center text-gray-400">Tidak ada permintaan menunggu persetujuan. ✓</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $pending->links() }}</div>

{{-- Riwayat keputusan --}}
<div class="bg-white rounded-2xl shadow mt-8 overflow-x-auto">
    <div class="p-4 sm:px-6 border-b border-gray-100">
        <h3 class="font-semibold text-brand-800">Riwayat Keputusan</h3>
        <p class="text-xs text-gray-400 mt-0.5">Riwayat persetujuan &amp; penolakan (beserta alasan) wajib terjaga.</p>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 sm:px-6 py-3">Diputuskan</th>
                <th class="px-4 py-3">Voucer</th>
                <th class="px-4 py-3">Member</th>
                <th class="px-4 py-3">Lokasi</th>
                <th class="px-4 py-3">Pengaju → Pemutus</th>
                <th class="px-4 py-3">Keputusan</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($history as $req)
                <tr>
                    <td class="px-4 sm:px-6 py-3">{{ $req->decided_at?->format('d M Y, H:i') ?? $req->created_at->format('d M Y, H:i') }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $req->voucher_no }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $req->member->full_name }}</div>
                        <div class="text-[11px] text-gray-400">{{ $req->member->member_no }}</div>
                    </td>
                    <td class="px-4 py-3">{{ ($req->hotel?->name ?? '-') . ($req->outlet ? ' — ' . $req->outlet : '') }}</td>
                    <td class="px-4 py-3 text-xs">{{ $req->requester?->name ?? '-' }} → <strong>{{ $req->decider?->name ?? '-' }}</strong></td>
                    <td class="px-4 py-3">
                        {!! status_badge($req->status) !!}
                        @if($req->status === 'REJECTED' && $req->rejection_reason)
                            <div class="text-[11px] text-gray-400 mt-1">Alasan: {{ $req->rejection_reason }}</div>
                        @endif
                        @if($req->status === 'APPROVED')
                            <a href="{{ route('admin.redemptions.slip', $req) }}" target="_blank"
                               class="inline-block mt-1.5 text-[11px] font-semibold text-brand-700 hover:text-brand-900 border border-brand-200 hover:border-brand-400 rounded-lg px-2.5 py-1">🖨 Cetak Slip</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-6 py-10 text-center text-gray-400">Belum ada riwayat keputusan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $history->links() }}</div>
@endsection
