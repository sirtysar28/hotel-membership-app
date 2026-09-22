@extends('layouts.admin')
@section('title', 'Payments')
@section('page-title', 'Payments')

@section('content')
<form method="GET" class="flex flex-wrap gap-2">
    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Member ID / nama…"
           class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-56 focus:ring-2 focus:ring-brand-500 outline-none">
    <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
        <option value="">Semua Status</option>
        @foreach($statuses as $value => $label)
            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <button class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
</form>

<div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Member</th>
                <th class="px-4 py-3">Invoice</th>
                <th class="px-4 py-3">Amount</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Verified By</th>
                <th class="px-4 py-3">Paid At</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($payments as $payment)
                <tr class="hover:bg-brand-50/40">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $payment->member->full_name }}</div>
                        <div class="text-xs text-gray-400 font-mono">{{ $payment->member->member_no }} · {{ $payment->member->hotel->name }}</div>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs">{{ $payment->invoice?->invoice_no ?? '-' }}</td>
                    <td class="px-4 py-3 font-semibold">{{ format_idr($payment->amount) }}</td>
                    <td class="px-4 py-3">{!! status_badge($payment->status) !!}</td>
                    <td class="px-4 py-3 text-xs">{{ $payment->verifier?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $payment->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if(in_array($payment->status, ['pending']))
                            <form method="POST" action="{{ route('admin.payments.submit', $payment) }}" class="inline">@csrf
                                <button class="border border-blue-200 text-blue-700 text-xs font-semibold px-3 py-1.5 rounded-lg">Payment Submitted</button>
                            </form>
                        @endif
                        @if(in_array($payment->status, ['pending', 'payment_submitted']) && in_array(auth()->user()->role, ['super_admin', 'finance']))
                                <form method="POST" action="{{ route('admin.payments.verify', $payment) }}" class="inline">@csrf
                                    <button class="bg-emerald-600 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">Verify</button>
                                </form>
                                <button onclick="rejectPayment({{ $payment->id }})" class="bg-red-50 text-red-600 text-xs font-semibold px-3 py-1.5 rounded-lg border border-red-200">Reject</button>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Tidak ada payment.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $payments->links() }}</div>

{{-- Reject modal --}}
<div id="reject-modal" class="hidden fixed inset-0 z-50 bg-black/60 items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <h3 class="font-bold text-red-700">Tolak Payment</h3>
        <form id="reject-form" method="POST" class="mt-4">
            @csrf
            <label class="block text-sm font-medium text-gray-700">Alasan (notes)</label>
            <textarea name="notes" rows="3" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5" placeholder="Bukti transfer tidak valid…"></textarea>
            <div class="flex justify-end gap-3 mt-4">
                <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-600 text-sm">Batal</button>
                <button class="bg-red-600 text-white px-5 py-2 rounded-lg text-sm font-semibold">Tolak</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function rejectPayment(id) {
    const modal = document.getElementById('reject-modal');
    document.getElementById('reject-form').action = '{{ url('admin/payments') }}/' + id + '/reject';
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}
</script>
@endpush
@endsection
