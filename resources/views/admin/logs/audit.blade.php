@extends('layouts.admin')
@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')

@section('content')
<form method="GET" class="flex gap-2">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari action / deskripsi…"
           class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-72 focus:ring-2 focus:ring-brand-500 outline-none">
    <button class="bg-brand-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Cari</button>
</form>

<div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr><th class="px-4 py-3">Waktu</th><th class="px-4 py-3">User</th><th class="px-4 py-3">Action</th><th class="px-4 py-3">Model</th><th class="px-4 py-3">Description</th><th class="px-4 py-3">IP</th></tr>
        </thead>
        <tbody class="divide-y">
            @forelse($logs as $log)
                <tr>
                    <td class="px-4 py-2.5 whitespace-nowrap text-xs">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td class="px-4 py-2.5">{{ $log->user?->name ?? '-' }}</td>
                    <td class="px-4 py-2.5"><code class="text-xs bg-gray-100 px-1.5 py-0.5 rounded">{{ $log->action }}</code></td>
                    <td class="px-4 py-2.5 text-xs">{{ $log->model_type ? $log->model_type . '#' . $log->model_id : '-' }}</td>
                    <td class="px-4 py-2.5 text-xs">{{ $log->description }}</td>
                    <td class="px-4 py-2.5 text-xs text-gray-400">{{ $log->ip_address }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada log.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
