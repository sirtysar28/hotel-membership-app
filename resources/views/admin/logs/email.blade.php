@extends('layouts.admin')
@section('title', 'Email Logs')
@section('page-title', 'Email Logs')

@section('content')
<form method="GET" class="flex gap-2">
    <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
        <option value="">Semua Type</option>
        @foreach($emailTypes as $value => $label)
            <option value="{{ $value }}" @selected(request('type') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari email…"
           class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-brand-500 outline-none">
    <button class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Filter</button>
</form>

<div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr><th class="px-4 py-3">Waktu</th><th class="px-4 py-3">To</th><th class="px-4 py-3">Type</th><th class="px-4 py-3">Subject</th><th class="px-4 py-3">Member</th><th class="px-4 py-3">Status</th></tr>
        </thead>
        <tbody class="divide-y">
            @forelse($logs as $log)
                <tr>
                    <td class="px-4 py-2.5 whitespace-nowrap text-xs">{{ $log->sent_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="px-4 py-2.5">{{ $log->to_email }}</td>
                    <td class="px-4 py-2.5"><span class="text-xs bg-brand-50 text-brand-700 px-2 py-0.5 rounded-full capitalize">{{ $log->type }}</span></td>
                    <td class="px-4 py-2.5 text-xs">{{ $log->subject }}</td>
                    <td class="px-4 py-2.5 text-xs">{{ $log->member?->member_no ?? '-' }}</td>
                    <td class="px-4 py-2.5 text-xs">{{ $log->status ? '✓ Sent' : '✕ Failed' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada email terkirim.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
