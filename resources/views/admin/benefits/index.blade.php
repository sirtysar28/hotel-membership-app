@extends('layouts.admin')
@section('title', 'Benefits')
@section('page-title', 'Membership Benefits')

@section('content')
<div class="flex justify-between items-center">
    <p class="text-sm text-gray-500">Benefit dapat dikonfigurasi per level &amp; hotel — tidak perlu ubah source code.</p>
    <a href="{{ route('admin.benefits.create') }}" class="bg-amber-400 hover:bg-amber-300 text-brand-900 font-semibold px-4 py-2 rounded-lg text-sm">+ Benefit Baru</a>
</div>

<div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>
                <th class="px-4 py-3">Benefit</th>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3">Level</th>
                <th class="px-4 py-3">Hotel</th>
                <th class="px-4 py-3">Value</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y">
            @forelse($benefits as $benefit)
                <tr class="hover:bg-brand-50/40">
                    <td class="px-4 py-3">
                        <div class="font-medium">{{ $benefit->name }}</div>
                        <div class="text-xs text-gray-400">{{ $benefit->description }}</div>
                    </td>
                    <td class="px-4 py-3 text-xs">{{ \App\Http\Controllers\Admin\BenefitController::CATEGORIES[$benefit->category] ?? $benefit->category }}</td>
                    <td class="px-4 py-3 text-xs">{{ $benefit->level?->name ?? 'Semua' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $benefit->hotel?->name ?? 'Semua' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $benefit->value ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $benefit->is_active ? '✓ Aktif' : 'Nonaktif' }}</td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        <details class="inline-block relative">
                            <summary class="cursor-pointer list-none text-xs font-semibold text-brand-700">Grant ▾</summary>
                            <form method="POST" action="{{ route('admin.benefits.grant', $benefit) }}"
                                  class="absolute right-0 z-10 mt-2 bg-white border border-gray-200 rounded-xl shadow-xl p-4 w-64 text-left">
                                @csrf
                                <label class="block text-xs font-medium text-gray-600 mb-1">Member No</label>
                                <input name="member_no" placeholder="HCM-000001" required class="w-full border border-gray-300 rounded-lg px-2.5 py-2 text-sm">
                                <label class="block text-xs font-medium text-gray-600 mt-2 mb-1">Expires (opsional)</label>
                                <input type="date" name="expires_at" class="w-full border border-gray-300 rounded-lg px-2.5 py-2 text-sm">
                                <button class="mt-3 w-full bg-brand-800 text-white text-xs font-semibold py-2 rounded-lg">Grant Benefit</button>
                            </form>
                        </details>
                        <a href="{{ route('admin.benefits.edit', $benefit) }}" class="text-gray-500 hover:underline font-medium text-xs ml-2">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400">Belum ada benefit.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $benefits->links() }}</div>
@endsection
