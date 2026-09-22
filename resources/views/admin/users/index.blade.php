@extends('layouts.admin')
@section('title', 'Users & Roles')
@section('page-title', 'Users & Roles')

@section('content')
<div class="flex justify-end">
    <a href="{{ route('admin.users.create') }}" class="bg-amber-400 hover:bg-amber-300 text-brand-900 font-semibold px-4 py-2 rounded-lg text-sm">+ User Baru</a>
</div>

<div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr><th class="px-4 py-3">Name</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Role</th><th class="px-4 py-3">Hotel</th><th class="px-4 py-3">Status</th><th class="px-4 py-3"></th></tr>
        </thead>
        <tbody class="divide-y">
            @forelse($users as $user)
                <tr class="hover:bg-brand-50/40">
                    <td class="px-4 py-3 font-medium">{{ $user->name }}</td>
                    <td class="px-4 py-3 text-xs">{{ $user->email }}</td>
                    <td class="px-4 py-3">{{ $user->roleLabel() }}</td>
                    <td class="px-4 py-3 text-xs">{{ $user->hotel?->name ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs">{{ $user->is_active ? '✓ Aktif' : 'Nonaktif' }}</td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.users.edit', $user) }}" class="text-brand-600 hover:underline font-medium text-xs">Edit</a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline" onsubmit="return confirm('Hapus user ini?')">@csrf @method('DELETE')
                            <button class="text-red-500 hover:underline font-medium text-xs ml-2">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400">Belum ada user.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $users->links() }}</div>
@endsection
