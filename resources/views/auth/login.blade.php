@extends('layouts.auth')
@section('title', 'Login')

@section('content')
<div class="text-center mb-6">
    <h1 class="text-xl font-bold text-brand-800">Login</h1>
    <p class="text-sm text-gray-500 mt-1">Admin, Staff &amp; Member Portal</p>
</div>

@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-4">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('login.attempt') }}" data-loading class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email') }}" required autofocus
               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
    </div>
    <div>
        <div class="flex items-center justify-between mb-1">
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs text-brand-600 hover:text-brand-800 hover:underline">Lupa password?</a>
        </div>
        <div class="relative">
            <input type="password" name="password" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2.5 pr-11 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            @include('partials.password-eye')
        </div>
    </div>

    {{-- Captcha 5 karakter --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Keamanan (Captcha)</label>
        <div class="flex items-center gap-2">
            <input type="text" name="captcha" required maxlength="5" autocomplete="off" placeholder="5 karakter"
                   style="text-transform:uppercase;"
                   class="w-28 border border-gray-300 rounded-lg px-3 py-2.5 font-mono tracking-widest uppercase focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
            <img src="{{ route('captcha') }}" alt="Captcha" id="captcha-image" title="Kode keamanan"
                 class="h-[46px] rounded-lg border border-gray-200 cursor-pointer select-none" onclick="refreshCaptcha()">
            <button type="button" onclick="refreshCaptcha()" title="Ganti kode"
                    class="p-2 text-gray-400 hover:text-brand-700 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
        <p class="text-xs text-gray-400 mt-1">Klik gambar untuk mengganti kode.</p>
    </div>

    <label class="flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" name="remember" class="rounded"> Remember me
    </label>
    <button type="submit" data-loading-text="Memproses…" class="w-full bg-brand-800 hover:bg-brand-700 text-white font-semibold py-3 rounded-lg transition-colors">Login</button>
</form>

<p class="text-center text-sm text-gray-500 mt-6">
    Belum jadi member? <a href="{{ route('register.create') }}" class="text-brand-600 font-medium hover:underline">Daftar di sini</a>
</p>

@push('scripts')
<script>
    function refreshCaptcha() {
        const img = document.getElementById('captcha-image');
        img.src = '{{ route('captcha') }}' + '?t=' + Date.now();
    }
</script>
@endpush
@endsection
