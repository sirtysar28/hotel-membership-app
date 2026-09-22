@extends('layouts.admin')
@section('title', 'Users & Roles')
@section('page-title', 'Users & Roles')

@section('content')
<div class="flex justify-between items-center">
    <p class="text-sm text-gray-500">Akun <strong>Manager</strong> &amp; <strong>Staff</strong> yang baru dibuat wajib menunggu approval Super Admin sebelum dapat login (update #18).</p>
    <a href="{{ route('admin.users.create') }}" class="bg-amber-400 hover:bg-amber-300 text-brand-900 font-semibold px-4 py-2 rounded-lg text-sm whitespace-nowrap">+ User Baru</a>
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
                    <td class="px-4 py-3 text-xs">
                        @if($user->approval_status === 'pending')
                            <span class="inline-block bg-amber-100 text-amber-800 rounded-full px-2 py-0.5 font-semibold text-[10px]">MENUNGGU APPROVAL</span>
                        @elseif($user->approval_status === 'rejected')
                            <span class="inline-block bg-red-100 text-red-700 rounded-full px-2 py-0.5 font-semibold text-[10px]">DITOLAK</span>
                        @else
                            {{ $user->is_active ? '✓ Aktif' : 'Nonaktif' }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if($user->approval_status === 'pending')
                            <form method="POST" action="{{ route('admin.users.approve', $user) }}" class="inline"
                                  onsubmit="return confirm('SETUJUI akun {{ $user->email }}? Email notifikasi akan dikirim.')">@csrf
                                <button class="bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs px-3 py-1.5 rounded-lg">✓ Setujui</button>
                            </form>
                            <form method="POST" action="{{ route('admin.users.reject', $user) }}" class="inline"
                                  onsubmit="return confirm('TOLAK akun {{ $user->email }}? Login akan diblokir.')">@csrf
                                <button class="bg-red-500 hover:bg-red-400 text-white font-semibold text-xs px-3 py-1.5 rounded-lg">✕ Tolak</button>
                            </form>
                        @endif
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
