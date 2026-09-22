@extends('layouts.portal')
@section('title', 'Benefit & Voucher')

@section('content')
<h1 class="text-2xl font-bold text-brand-800">Benefit Membership</h1>
<p class="text-sm text-gray-500 mt-1">Benefit level {{ $member->level->name }} di {{ $member->hotel->name }}.</p>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
    @forelse($levelBenefits as $benefit)
        <div class="bg-white rounded-2xl shadow p-5">
            <div class="flex justify-between items-start">
                <div class="text-xs uppercase tracking-wide text-gray-400">{{ \App\Http\Controllers\Admin\BenefitController::CATEGORIES[$benefit->category] ?? $benefit->category }}</div>
                @if($benefit->value)<span class="text-xs bg-amber-100 text-amber-800 px-2 py-0.5 rounded-full font-medium">{{ $benefit->value }}</span>@endif
            </div>
            <div class="font-semibold text-brand-800 mt-2">{{ $benefit->name }}</div>
            @if($benefit->description)<div class="text-sm text-gray-500 mt-1">{{ $benefit->description }}</div>@endif
        </div>
    @empty
        <div class="col-span-full bg-white rounded-2xl shadow p-8 text-center text-gray-400">Belum ada benefit terdaftar untuk level ini.</div>
    @endforelse
</div>

<div class="mt-10">
    <h2 class="text-lg font-bold text-brand-800">Voucher & Benefit Saya</h2>
    <div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Benefit</th>
                    <th class="px-4 py-3">Granted</th>
                    <th class="px-4 py-3">Expires</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($vouchers as $voucher)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $voucher->benefit->name }}</div>
                            <div class="text-xs text-gray-400">{{ $voucher->benefit->value }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $voucher->granted_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $voucher->expires_at?->format('d M Y') ?? '-' }}</td>
                        <td class="px-4 py-3">{!! status_badge($voucher->status) !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400">Belum ada voucher.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
