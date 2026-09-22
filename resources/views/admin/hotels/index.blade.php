@extends('layouts.admin')
@section('title', 'Hotels')
@section('page-title', 'Hotels')

@section('content')
<div class="flex justify-end">
    <a href="{{ route('admin.hotels.create') }}" class="bg-amber-400 hover:bg-amber-300 text-brand-900 font-semibold px-4 py-2 rounded-lg text-sm">+ Hotel Baru</a>
</div>

<div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr><th class="px-4 py-3">Code</th><th class="px-4 py-3">Name</th><th class="px-4 py-3">City</th><th class="px-4 py-3">Members</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody class="divide-y">
            @forelse($hotels as $hotel)
                <tr class="hover:bg-brand-50/40">
                    <td class="px-4 py-3 font-mono">{{ $hotel->code }}</td>
                    <td class="px-4 py-3 font-medium">{{ $hotel->name }}</td>
                    <td class="px-4 py-3">{{ $hotel->city }}</td>
                    <td class="px-4 py-3">{{ number_format($hotel->members()->count()) }}</td>
                    <td class="px-4 py-3 text-xs">{{ $hotel->is_active ? '✓ Aktif' : 'Nonaktif' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.hotels.edit', $hotel) }}" class="text-brand-600 hover:underline font-medium text-xs">Edit</a>
                        <form method="POST" action="{{ route('admin.hotels.destroy', $hotel) }}" class="inline" onsubmit="return confirm('Hapus hotel ini?')">@csrf @method('DELETE')
                            <button class="text-red-500 hover:underline font-medium text-xs ml-2">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada hotel.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $hotels->links() }}</div>
@endsection
