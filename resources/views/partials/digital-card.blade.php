@php
    /** @var \App\Models\Member $member */
    $card = $member->activeCard ?: $member->cards()->latest()->first();
    $qrData = $card ? route('staff.scan', ['t' => $card->token]) : '';
    $qrSvg = '';
    try {
        $qrSvg = $qrData ? QrCode::size(180)->margin(1)->generate($qrData) : '';
    } catch (\Throwable $e) {
        $qrSvg = '';
    }
@endphp
<div class="w-full max-w-sm mx-auto rounded-2xl overflow-hidden shadow-2xl text-white relative"
     style="background: linear-gradient(135deg, {{ $member->level->card_color ?? '#0f2a4a' }} 0%, #1a1a2e 100%);">
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 80% 20%, #fff 0%, transparent 40%), radial-gradient(circle at 10% 90%, #fff 0%, transparent 30%);"></div>

    <div class="relative p-6">
        <div class="flex justify-between items-start">
            <div>
                <div class="text-xs tracking-[0.3em] uppercase opacity-80">Hotel Ciputra</div>
                <div class="text-lg font-bold tracking-wide mt-0.5">{{ strtoupper($member->level->name) }} MEMBER</div>
            </div>
            <div class="text-right text-xs opacity-80">
                {{ ucfirst($member->membership_type) }}<br>Membership
            </div>
        </div>

        <div class="mt-8">
            <div class="text-xl font-semibold tracking-wide">{{ strtoupper($member->full_name) }}</div>
            <div class="text-sm opacity-90 mt-1">Member ID: {{ $member->member_no }}</div>
            <div class="text-xs opacity-70 mt-0.5">{{ $member->hotel->name }}</div>
        </div>

        <div class="mt-6 flex items-end justify-between gap-4">
            <div class="bg-white rounded-xl p-2">
                @if($qrSvg)
                    {!! $qrSvg !!}
                @else
                    <div class="w-[180px] h-[180px] flex items-center justify-center text-xs text-gray-500">QR unavailable</div>
                @endif
            </div>
            <div class="text-right pb-1">
                <div class="text-[10px] uppercase tracking-wider opacity-70">Valid Until</div>
                <div class="text-sm font-medium">{{ $card?->valid_until?->format('d M Y') ?? $member->valid_until?->format('d M Y') ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="relative bg-black/30 px-6 py-2 text-[10px] tracking-wider uppercase opacity-70 flex justify-between">
        <span>Digital Membership Card</span>
        <span>{{ $card?->is_active ? 'Active' : 'Replaced' }}</span>
    </div>
</div>
