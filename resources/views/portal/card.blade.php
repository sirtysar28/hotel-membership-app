@extends('layouts.portal')
@section('title', 'Digital Membership Card')

@section('content')
<div class="max-w-lg mx-auto">
    <h1 class="text-2xl font-bold text-brand-800 mb-6 text-center">Digital Membership Card</h1>

    @include('partials.digital-card', ['member' => $member])

    <div class="bg-white rounded-2xl shadow p-6 mt-6 text-sm">
        <div class="space-y-2">
            <div class="flex justify-between"><span class="text-gray-500">Level</span><span class="font-semibold">{{ $member->level->name }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Card No</span><span class="font-mono">{{ $member->activeCard?->card_no ?? $member->member_no }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Issued</span><span>{{ $member->activeCard?->issued_at?->format('d M Y') ?? '-' }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Valid Until</span><span>{{ $member->valid_until?->format('d M Y') ?? '-' }}</span></div>
        </div>
        <p class="text-xs text-gray-400 mt-4">Tunjukkan QR code ini kepada staff hotel untuk identifikasi. QR berisi token, bukan data pribadi Anda.</p>
    </div>
</div>
@endsection
