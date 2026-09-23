@extends('layouts.admin')
@section('title', 'Settings')
@section('page-title', 'Settings')

@use (\App\Support\Brand)
@section('content')
@php
    // Accessor aman: key yang belum ada di DB tidak memicu "Undefined array key"
    $get = fn ($key, $default = null) => $settings->get($key)?->value ?? $default;
    $has = fn ($key) => $settings->has($key);

    // Error validasi per-card (error bag terpisah antar section form)
    $bagErrors = fn (string $bag) => $errors->getBag($bag)->all();

    // Meta field branding: [key, label, deskripsi, kelas preview]
    $brandFields = [
        ['key' => 'logo_login', 'label' => 'Logo Halaman Login', 'hint' => 'Lockup besar di kartu login. Disarankan PNG transparan, lebar ±600px.', 'preview' => 'max-h-16'],
        ['key' => 'logo_landing', 'label' => 'Logo Landing Page', 'hint' => 'Ikon kecil di navbar halaman publik (kotak 9×9). Disarankan PNG/SVG persegi.', 'preview' => 'max-h-12'],
        ['key' => 'logo_admin', 'label' => 'Logo Dashboard Admin', 'hint' => 'Ikon kecil di sidebar admin (kotak 9×9). Disarankan PNG/SVG persegi.', 'preview' => 'max-h-12'],
        ['key' => 'favicon', 'label' => 'Favicon', 'hint' => 'Ikon tab browser. Disarankan ICO/PNG persegi 32×32 atau SVG.', 'preview' => 'max-h-10'],
    ];
@endphp
<div class="max-w-2xl space-y-6">

    {{-- Flash --}}
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    {{-- ================= GENERAL ================= --}}
    <div class="bg-white rounded-2xl shadow p-6 md:p-8">
        <h2 class="font-semibold text-brand-800 text-lg">General Settings</h2>

        @if ($bagErrors('general'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mt-4">
                @foreach ($bagErrors('general') as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="general">
            <div>
                <label class="block text-sm font-medium text-gray-700">Harga Paid Membership (Rp) *</label>
                <input type="number" name="paid_membership_price" min="0" step="1000" required
                       value="{{ old('paid_membership_price', $get('paid_membership_price', 2200000)) }}"
                       class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                <p class="text-xs text-gray-400 mt-1">Default: Rp2.200.000</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Prefix Member ID *</label>
                <input name="member_no_prefix" required maxlength="10"
                       value="{{ old('member_no_prefix', $get('member_no_prefix', 'HCM')) }}"
                       class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 font-mono uppercase">
                <p class="text-xs text-gray-400 mt-1">Contoh hasil: {{ $get('member_no_prefix', 'HCM') }}-000182</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Masa Berlaku Membership (tahun) *</label>
                <input type="number" name="membership_validity_years" min="1" required
                       value="{{ old('membership_validity_years', $get('membership_validity_years', 1)) }}"
                       class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
            </div>
            <div class="flex justify-end pt-2">
                <button class="bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">Simpan Settings</button>
            </div>
        </form>
    </div>

    {{-- ================= BRANDING (LOGO & FAVICON) ================= --}}
    <div class="bg-white rounded-2xl shadow p-6 md:p-8">
        <h2 class="font-semibold text-brand-800 text-lg">Branding — Logo &amp; Favicon</h2>
        <p class="text-sm text-gray-500 mt-1">
            Ubah logo halaman <strong>Login</strong>, <strong>Landing Page</strong>, <strong>Dashboard Admin</strong>, dan <strong>Favicon</strong> (ikon tab browser).
            Format: PNG / JPG / WEBP / SVG — favicon juga menerima ICO. Biarkan kosong jika tidak ingin mengubah.
        </p>

        @if ($bagErrors('branding'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mt-4">
                @foreach ($bagErrors('branding') as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="branding">

            @foreach ($brandFields as $field)
                @php $isCustom = Brand::hasCustom($field['key']); @endphp
                <div class="border border-gray-100 rounded-xl p-4">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <label class="block text-sm font-medium text-gray-700">{{ $field['label'] }}</label>
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium {{ $isCustom ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-500' }}">
                            {{ $isCustom ? 'Custom aktif' : 'Default bawaan' }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $field['hint'] }}</p>

                    <div class="mt-3 flex items-center gap-4 flex-wrap">
                        {{-- Preview saat ini --}}
                        <div class="w-40 h-20 border border-dashed border-gray-200 rounded-lg bg-gray-50 flex items-center justify-center overflow-hidden shrink-0">
                            <img id="preview-{{ $field['key'] }}" src="{{ Brand::url($field['key']) }}" alt="{{ $field['label'] }}" class="{{ $field['preview'] }} max-w-full object-contain">
                        </div>
                        <div class="flex-1 min-w-[220px] space-y-2">
                            <input type="file" name="{{ $field['key'] }}" accept="{{ $field['key'] === 'favicon' ? 'image/png,image/jpeg,image/webp,image/svg+xml,image/x-icon,.ico' : 'image/png,image/jpeg,image/webp,image/svg+xml' }}"
                                   onchange="previewBrandFile(this, 'preview-{{ $field['key'] }}', 'remove-{{ $field['key'] }}')"
                                   class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-brand-50 file:text-brand-800 file:font-medium file:cursor-pointer cursor-pointer">
                            @if ($isCustom)
                                <label class="flex items-center gap-2 text-xs text-red-600 cursor-pointer select-none">
                                    <input type="checkbox" id="remove-{{ $field['key'] }}" name="remove_{{ $field['key'] }}" value="1"
                                           class="rounded border-gray-300 text-red-600 focus:ring-red-300"
                                           onchange="this.closest('div.border').classList.toggle('opacity-60', this.checked)">
                                    Hapus &amp; kembalikan ke default bawaan
                                </label>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="flex justify-end pt-2">
                <button class="bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">Simpan Branding</button>
            </div>
        </form>
    </div>

    {{-- ================= SMTP / EMAIL ================= --}}
    <div class="bg-white rounded-2xl shadow p-6 md:p-8">
        <div class="flex items-center justify-between flex-wrap gap-2">
            <h2 class="font-semibold text-brand-800 text-lg">Pengaturan SMTP (Email Notifikasi)</h2>
            @php $currentMailer = $get('mail_mailer', config('mail.default')); @endphp
            <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $currentMailer === 'smtp' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                Mailer aktif: {{ strtoupper($currentMailer) }}
            </span>
        </div>
        <p class="text-sm text-gray-500 mt-1">
            Email notifikasi (welcome, upgrade, transaksi, renewal, reset password) dikirim menggunakan konfigurasi ini.
            Mode <strong>log</strong> hanya mencatat email tanpa mengirim (untuk development).
        </p>

        @if ($bagErrors('smtp'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mt-4">
                @foreach ($bagErrors('smtp') as $error)<div>• {{ $error }}</div>@endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('admin.settings.update') }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="section" value="smtp">

            <div>
                <label class="block text-sm font-medium text-gray-700">Mailer *</label>
                <select name="mail_mailer" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                    <option value="smtp" {{ old('mail_mailer', $currentMailer) === 'smtp' ? 'selected' : '' }}>SMTP (kirim email nyata)</option>
                    <option value="log" {{ old('mail_mailer', $currentMailer) === 'log' ? 'selected' : '' }}>Log (development — tidak mengirim)</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">SMTP Host</label>
                    <input name="smtp_host" placeholder="mail.trijayasolution.com"
                           value="{{ old('smtp_host', $get('smtp_host')) }}"
                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SMTP Port</label>
                    <input type="number" name="smtp_port" min="1" max="65535" placeholder="587"
                           value="{{ old('smtp_port', $get('smtp_port')) }}"
                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                    <p class="text-xs text-gray-400 mt-1">587 (TLS) / 465 (SSL) / 25</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Enkripsi *</label>
                <select name="smtp_encryption" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                    <option value="tls" {{ old('smtp_encryption', $get('smtp_encryption', 'tls')) === 'tls' ? 'selected' : '' }}>TLS (port 587)</option>
                    <option value="ssl" {{ old('smtp_encryption', $get('smtp_encryption', 'tls')) === 'ssl' ? 'selected' : '' }}>SSL (port 465)</option>
                    <option value="none" {{ old('smtp_encryption', $get('smtp_encryption', 'tls')) === 'none' ? 'selected' : '' }}>Tanpa Enkripsi</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">SMTP Username</label>
                    <input name="smtp_username" placeholder="no-reply@trijayasolution.com"
                           value="{{ old('smtp_username', $get('smtp_username')) }}"
                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">SMTP Password</label>
                    <div class="relative mt-1">
                        <input type="password" name="smtp_password" placeholder="{{ $has('smtp_password') ? '•••••••• (tersimpan — isi untuk ganti)' : 'Isi password SMTP' }}" autocomplete="new-password"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2.5 pr-11">
                        @include('partials.password-eye')
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">From Address</label>
                    <input type="email" name="mail_from_address" placeholder="no-reply@hotelciputra.com"
                           value="{{ old('mail_from_address', $get('mail_from_address')) }}"
                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">From Name</label>
                    <input name="mail_from_name" placeholder="Ciputra Premiere Club (CPC)"
                           value="{{ old('mail_from_name', $get('mail_from_name')) }}"
                           class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                </div>
            </div>

            <div class="flex items-center justify-between pt-2 flex-wrap gap-2">
                <p class="text-xs text-gray-400">Password SMTP disimpan terenkripsi di database.</p>
                <button class="bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">Simpan Pengaturan SMTP</button>
            </div>
        </form>
    </div>

    {{-- ================= TEST EMAIL ================= --}}
    <div class="bg-white rounded-2xl shadow p-6 md:p-8">
        <h2 class="font-semibold text-brand-800 text-lg">Test Email</h2>
        <p class="text-sm text-gray-500 mt-1">Kirim email percobaan (dengan template HTML resmi) untuk memverifikasi konfigurasi SMTP.</p>

        <form method="POST" action="{{ route('admin.settings.test-email') }}" class="mt-5 flex flex-col sm:flex-row gap-3 sm:items-end">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">Email Tujuan</label>
                <input type="email" name="test_email" required value="{{ old('test_email', auth()->user()->email) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5">
            </div>
            <button class="bg-amber-500 hover:bg-amber-400 text-white font-semibold px-6 py-2.5 rounded-lg text-sm whitespace-nowrap">
                Kirim Email Percobaan
            </button>
        </form>
    </div>

</div>
@push('scripts')
<script>
    /* Preview file branding sebelum diupload */
    function previewBrandFile(input, previewId, removeId) {
        const preview = document.getElementById(previewId);
        const removeBox = document.getElementById(removeId);
        const [file] = input.files;

        if (file && file.type.startsWith('image/')) {
            preview.src = URL.createObjectURL(file);
            preview.onload = () => URL.revokeObjectURL(preview.src);
        }

        // Pilih file baru = batalkan niat hapus/reset
        if (removeBox) {
            removeBox.checked = false;
            removeBox.closest('div.border').classList.remove('opacity-60');
        }
    }
</script>
@endpush
@endsection
