@extends('layouts.guest')
@section('title', 'Registrasi Berhasil')

@section('content')
<div class="max-w-xl mx-auto py-16 px-4">
    <div class="bg-white rounded-2xl shadow-lg p-8 text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl">✓</div>

        @if($member->status === 'active')
            <h1 class="text-2xl font-bold text-brand-800 mt-4">Registrasi Berhasil!</h1>
            <p class="text-gray-600 mt-2 text-sm">Selamat datang di Hotel Ciputra Membership, <strong>{{ $member->full_name }}</strong>.</p>
        @else
            <h1 class="text-2xl font-bold text-brand-800 mt-4">Registrasi Diterima</h1>
            <p class="text-gray-600 mt-2 text-sm">Terima kasih, <strong>{{ $member->full_name }}</strong>. Membership Anda menunggu konfirmasi pembayaran.</p>
        @endif

        <div class="mt-6 bg-brand-50 rounded-xl p-5 text-left text-sm space-y-2">
            <div class="flex justify-between"><span class="text-gray-500">Member ID</span><span class="font-semibold">{{ $member->member_no }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Hotel</span><span>{{ $member->hotel->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Membership</span><span class="capitalize">{{ $member->membership_type }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Level</span><span>{{ $member->level->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Status</span>{!! status_badge($member->status) !!}</div>
            @if($member->membership_type === 'paid')
                <div class="flex justify-between"><span class="text-gray-500">Amount</span><span class="font-semibold">{{ format_idr($member->payments->first()?->amount ?? \App\Models\Setting::get('paid_membership_price', 2200000)) }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Payment Status</span>{!! status_badge($member->payments->first()?->status ?? 'pending') !!}</div>
            @endif
        </div>

        @if($member->status === 'active')
            <p class="text-xs text-gray-500 mt-4">Digital membership card &amp; welcome email telah dikirim ke <strong>{{ $member->email }}</strong>.</p>
        @else
            <p class="text-xs text-gray-500 mt-4">Setelah pembayaran diverifikasi tim kami, membership akan aktif dan digital card dikirim ke email Anda.</p>
        @endif

        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ route('home') }}" class="bg-brand-800 text-white px-5 py-2.5 rounded-lg text-sm font-semibold">Kembali ke Home</a>
        </div>
    </div>
</div>
@endsection
