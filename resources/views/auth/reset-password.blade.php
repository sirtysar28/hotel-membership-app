@extends('layouts.auth')
@section('title', 'Reset Password')

@section('content')
<div class="text-center mb-6">
    <h1 class="text-xl font-bold text-brand-800">Reset Password</h1>
    <p class="text-sm text-gray-500 mt-1">Buat password baru untuk akun <strong>{{ $email }}</strong></p>
</div>

@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-4">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('password.update') }}" data-loading class="space-y-4">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
        <div class="relative">
            <input type="password" name="password" required minlength="8" autofocus
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 pr-11 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            @include('partials.password-eye')
        </div>
        <p class="text-xs text-gray-400 mt-1">Minimal 8 karakter.</p>
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
        <div class="relative">
            <input type="password" name="password_confirmation" required minlength="8"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 pr-11 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            @include('partials.password-eye')
        </div>
    </div>

    <button type="submit" data-loading-text="Menyimpan…" class="w-full bg-brand-800 hover:bg-brand-700 text-white font-semibold py-3 rounded-lg transition-colors">
        Simpan Password Baru
    </button>
</form>

<p class="text-center text-sm text-gray-500 mt-6">
    <a href="{{ route('login') }}" class="text-brand-600 font-medium hover:underline">&larr; Kembali ke halaman login</a>
</p>
@endsection
