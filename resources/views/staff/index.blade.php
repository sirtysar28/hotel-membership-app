@extends('layouts.admin')
@section('title', 'Staff — Search Member')
@section('page-title', 'Staff / Redeem Visit')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-2xl shadow p-6">
        <h2 class="font-semibold text-brand-800">Cari Member</h2>
        <p class="text-sm text-gray-500 mt-1">Cari berdasarkan Member ID, Email, Phone, atau Name. Staff juga dapat scan QR code member.</p>

        <form method="GET" class="mt-4 flex gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="HCM-000182 / email / phone / nama…"
                   class="flex-1 border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
            <button class="bg-brand-800 text-white px-6 py-2.5 rounded-lg font-semibold text-sm">Search</button>
        </form>

        <div class="mt-3 text-xs text-gray-400">Scan QR: arahkan scanner ke QR pada digital card — URL berformat <code>/staff/scan?t=…</code></div>
    </div>

    @if($q !== '' && $members->isEmpty())
        <div class="bg-white rounded-2xl shadow p-8 mt-4 text-center text-gray-500 text-sm">Tidak ada member yang cocok dengan "{{ $q }}".</div>
    @endif

    @if($members->isNotEmpty())
        <div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3">Member ID</th>
                        <th class="px-4 py-3">Name</th>
                        <th class="px-4 py-3">Level</th>
                        <th class="px-4 py-3">Hotel</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Visit / Spending</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($members as $member)
                        <tr class="hover:bg-brand-50/40">
                            <td class="px-4 py-3 font-mono text-xs">{{ $member->member_no }}</td>
                            <td class="px-4 py-3">
                                <div class="font-medium">{{ $member->full_name }}</div>
                                <div class="text-xs text-gray-400">{{ $member->email }} · {{ $member->phone }}</div>
                            </td>
                            <td class="px-4 py-3">{{ $member->level->name }}</td>
                            <td class="px-4 py-3 text-xs">{{ $member->hotel->name }}</td>
                            <td class="px-4 py-3">{!! status_badge($member->status) !!}</td>
                            <td class="px-4 py-3">{{ $member->total_visits }} / {{ format_idr($member->total_spending) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('staff.members.show', $member) }}" class="bg-brand-800 text-white px-3 py-1.5 rounded-lg text-xs font-semibold">Buka →</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
