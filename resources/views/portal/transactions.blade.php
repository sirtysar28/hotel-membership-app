@extends('layouts.portal')
@section('title', 'Transaction History')

@section('content')
<h1 class="text-2xl font-bold text-brand-800">Transaction History</h1>

<div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Type</th>
                <th class="px-4 py-3">Transaction No</th>
                <th class="px-4 py-3">Hotel / Outlet</th>
                <th class="px-4 py-3">Visit</th>
                <th class="px-4 py-3 text-right">Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($transactions as $txn)
                <tr>
                    <td class="px-4 py-3">{{ $txn->transaction_date->format('d M Y') }}</td>
                    <td class="px-4 py-3">{{ $txn->typeLabel() }}</td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $txn->transaction_no }}</td>
                    <td class="px-4 py-3">{{ $txn->hotel->name }}{{ $txn->outlet ? ' · ' . $txn->outlet : '' }}</td>
                    <td class="px-4 py-3">{{ $txn->counts_as_visit ? '✓ +1' : '—' }}</td>
                    <td class="px-4 py-3 text-right">{{ format_idr($txn->amount) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada transaksi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $transactions->links() }}</div>
@endsection
