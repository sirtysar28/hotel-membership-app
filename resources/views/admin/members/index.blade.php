@extends('layouts.admin')
@section('title', 'Members')
@section('page-title', 'Members')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari member…"
               class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-48 focus:ring-2 focus:ring-brand-500 outline-none">
        <select name="hotel_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
            <option value="">Semua Hotel</option>
            @foreach($hotels as $hotel)
                <option value="{{ $hotel->id }}" @selected(($filters['hotel_id'] ?? '') == $hotel->id)>{{ $hotel->name }}</option>
            @endforeach
        </select>
        <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
            <option value="">Semua Type</option>
            <option value="paid" @selected(($filters['type'] ?? '') === 'paid')>Paid</option>
            <option value="free" @selected(($filters['type'] ?? '') === 'free')>Free</option>
        </select>
        <select name="level_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
            <option value="">Semua Level</option>
            @foreach($levels as $level)
                <option value="{{ $level->id }}" @selected(($filters['level_id'] ?? '') == $level->id)>{{ $level->name }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
            <option value="">Semua Status</option>
            @foreach(['active', 'pending_payment', 'pending_review', 'inactive', 'expired'] as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <button class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
    </form>
    <a href="{{ route('admin.members.create') }}" class="bg-amber-400 hover:bg-amber-300 text-brand-900 font-semibold px-4 py-2 rounded-lg text-sm">+ Member Baru</a>
</div>

<div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Member ID</th>
                <th class="px-4 py-3">Name / Contact</th>
                <th class="px-4 py-3">Hotel</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Level</th>
                <th class="px-4 py-3">Visit</th>
                <th class="px-4 py-3">Spending</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Registered</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($members as $member)
                <tr class="hover:bg-brand-50/40">
                    <td class="px-4 py-3 font-mono text-xs">{{ $member->member_no }}</td>
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $member->full_name }}</div>
                        <div class="text-xs text-gray-400">{{ $member->email }} · {{ $member->fullPhone() }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs">{{ $member->hotel->name }}</td>
                    <td class="px-4 py-3 capitalize">{{ $member->membership_type }}</td>
                    <td class="px-4 py-3">{{ $member->level->name }}</td>
                    <td class="px-4 py-3">{{ $member->total_visits }}</td>
                    <td class="px-4 py-3">{{ format_idr($member->total_spending) }}</td>
                    <td class="px-4 py-3">{!! status_badge($member->status) !!}</td>
                    <td class="px-4 py-3 text-xs">{{ $member->created_at->format('d/m/Y') }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <a href="{{ route('admin.members.show', $member) }}" class="text-brand-600 hover:underline font-medium text-xs">Detail</a>
                        <a href="{{ route('admin.members.edit', $member) }}" class="text-gray-500 hover:underline font-medium text-xs ml-2">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="px-4 py-10 text-center text-gray-400">Tidak ada member.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $members->links() }}</div>
@endsection
