@extends('layouts.portal')
@section('title', 'Visit History')

@section('content')
<h1 class="text-2xl font-bold text-brand-800">Visit History</h1>

<div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Visit Date</th>
                <th class="px-4 py-3">Hotel</th>
                <th class="px-4 py-3">Transaction</th>
                <th class="px-4 py-3">Outlet</th>
                <th class="px-4 py-3 text-right">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($visits as $visit)
                <tr>
                    <td class="px-4 py-3">{{ $visit->visit_date->format('d M Y') }}</td>
                    <td class="px-4 py-3">{{ $visit->hotel->name }}</td>
                    <td class="px-4 py-3">
                        <div>{{ $visit->transaction?->typeLabel() ?? 'Visit' }}</div>
                        <div class="text-xs text-gray-400 font-mono">{{ $visit->transaction?->transaction_no }}</div>
                    </td>
                    <td class="px-4 py-3">{{ $visit->transaction?->outlet ?? '-' }}</td>
                    <td class="px-4 py-3 text-right">{{ format_idr($visit->transaction?->amount ?? 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada visit.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $visits->links() }}</div>
@endsection
