@extends('layouts.admin')
@section('title', 'User Baru')
@section('page-title', 'Users — Baru')

@section('content')
<div class="max-w-xl bg-white rounded-2xl shadow p-6 md:p-8">
    <h2 class="font-semibold text-brand-800 text-lg">User Baru</h2>
    <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mt-3">ℹ Akun dengan role <strong>Manager</strong> / <strong>Staff</strong> dibuat sebagai <em>pending</em> dan tidak dapat login sebelum disetujui di halaman Users (update #18).</p>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mt-4">
            @foreach ($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.users.store') }}" class="mt-6 grid sm:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Name *</label>
            <input name="name" value="{{ old('name') }}" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Password *</label>
            <div class="relative mt-1">
                <input type="password" name="password" required minlength="8"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 pr-11">
                @include('partials.password-eye')
            </div>
            <p class="text-xs text-gray-400 mt-1">Minimal 8 karakter.</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Role *</label>
            <select name="role" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                @foreach($roles as $value => $label)
                    <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Hotel <span class="text-xs text-gray-400">(untuk Hotel Admin / Staff)</span></label>
            <select name="hotel_id" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                <option value="">-</option>
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" @selected(old('hotel_id') == $hotel->id)>{{ $hotel->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2 flex justify-end gap-3">
            <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 text-sm">Batal</a>
            <button class="bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">Simpan</button>
        </div>
    </form>
</div>
@endsection
