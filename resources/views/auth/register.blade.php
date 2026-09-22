@extends('layouts.guest')
@section('title', 'Registrasi Member')

@section('content')
<div class="max-w-3xl mx-auto py-12 px-4">
    <div class="text-center mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-brand-800">Registrasi Membership</h1>
        <p class="text-gray-500 mt-2 text-sm">Sistem otomatis melakukan pengecekan duplikasi data member.</p>
    </div>

    @if (session('duplicate_blocked'))
        <div class="bg-orange-50 border-l-4 border-orange-500 text-orange-800 rounded-lg px-4 py-4 mb-6 text-sm">
            <div class="font-semibold">⚠ MEMBER DATA MATCH</div>
            <div class="mt-1">Data yang dimasukkan memiliki kesamaan dengan member terdaftar ({{ session('duplicate_member') }}). Registrasi tidak dapat dilanjutkan.</div>
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mb-6">
                @foreach ($errors->all() as $error)
                    <div>• {{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}" id="registration-form" data-loading class="space-y-8">
            @csrf

            {{-- Step 1: Personal Data --}}
            <fieldset>
                <legend class="text-sm font-semibold uppercase tracking-wider text-brand-700 border-b border-gray-100 w-full pb-2 mb-4">1. Personal Data</legend>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Full Name *</label>
                        <input name="full_name" value="{{ old('full_name') }}" required class="dup-field mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" required class="dup-field mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Mobile Phone *</label>
                        <input name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx" class="dup-field mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
                        <input type="date" name="dob" value="{{ old('dob') }}" class="dup-field mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Gender</label>
                        <select name="gender" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                            <option value="">-- Pilih --</option>
                            <option value="male" @selected(old('gender') === 'male')>Male</option>
                            <option value="female" @selected(old('gender') === 'female')>Female</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Address</label>
                        <textarea name="address" rows="2" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">{{ old('address') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ID Type</label>
                        <select name="id_type" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                            <option value="">-- Pilih --</option>
                            @foreach(['ktp' => 'KTP', 'passport' => 'Passport', 'sim' => 'SIM', 'other' => 'Lainnya'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('id_type') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">ID Number / Passport</label>
                        <input name="id_number" value="{{ old('id_number') }}" class="dup-field mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Company</label>
                        <input name="company" value="{{ old('company') }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Occupation</label>
                        <input name="occupation" value="{{ old('occupation') }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
                    </div>
                </div>
            </fieldset>

            {{-- Step 2: Verifikasi Email (OTP) --}}
            <fieldset>
                <legend class="text-sm font-semibold uppercase tracking-wider text-brand-700 border-b border-gray-100 w-full pb-2 mb-4">2. Verifikasi Email (OTP) *</legend>
                <div class="grid sm:grid-cols-2 gap-4 items-start">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode OTP (6 digit) *</label>
                        <div class="mt-1 flex gap-2">
                            <input id="otp-code" name="otp_code" value="{{ old('otp_code') }}" inputmode="numeric" maxlength="6" placeholder="••••••"
                                   class="w-32 text-center tracking-[0.4em] font-mono text-lg border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-brand-500 outline-none">
                            <button type="button" id="btn-otp-send" class="bg-brand-800 hover:bg-brand-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg whitespace-nowrap">Kirim Kode OTP</button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Kode dikirim ke email Anda, berlaku 10 menit.</p>
                    </div>
                    <div class="sm:pt-8">
                        <div id="otp-status" class="text-sm min-h-[24px]"></div>
                        <button type="button" id="btn-otp-verify" disabled
                                class="mt-2 bg-emerald-600 hover:bg-emerald-500 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-semibold px-5 py-2.5 rounded-lg">Verifikasi Kode</button>
                    </div>
                </div>
            </fieldset>

            {{-- Step 3: Membership --}}
            <fieldset>
                <legend class="text-sm font-semibold uppercase tracking-wider text-brand-700 border-b border-gray-100 w-full pb-2 mb-4">3. Membership</legend>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Hotel *</label>
                        <select name="hotel_id" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                            <option value="">-- Pilih Hotel --</option>
                            @foreach ($hotels as $hotel)
                                <option value="{{ $hotel->id }}" @selected(old('hotel_id') == $hotel->id)>{{ $hotel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Membership Type *</label>
                        <div class="mt-1 grid grid-cols-2 gap-3">
                            <label class="border-2 border-gray-200 hover:border-brand-500 rounded-xl p-3 cursor-pointer flex flex-col has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                <input type="radio" name="membership_type" value="paid" class="mb-1" @checked(old('membership_type', 'free') === 'paid')>
                                <span class="text-sm font-semibold text-brand-800">Paid</span>
                                <span class="text-xs text-gray-500">{{ format_idr(\App\Models\Setting::get('paid_membership_price', 2200000)) }}</span>
                            </label>
                            <label class="border-2 border-gray-200 hover:border-brand-500 rounded-xl p-3 cursor-pointer flex flex-col has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                <input type="radio" name="membership_type" value="free" class="mb-1" @checked(old('membership_type', 'free') === 'free')>
                                <span class="text-sm font-semibold text-brand-800">Free</span>
                                <span class="text-xs text-gray-500">Level via visit &amp; spending</span>
                            </label>
                        </div>
                    </div>
                </div>
            </fieldset>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <div id="dup-status" class="text-sm w-full sm:w-auto"></div>
                <button type="submit" data-loading-text="Mendaftarkan…" class="w-full sm:w-auto bg-brand-800 hover:bg-brand-700 text-white font-semibold px-8 py-3 rounded-xl">Daftar Membership</button>
            </div>
        </form>
    </div>
</div>

{{-- Duplicate Match Modal --}}
<div id="dup-modal" class="hidden fixed inset-0 z-50 bg-black/60 items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-start gap-3">
            <div class="text-3xl">⚠️</div>
            <div>
                <h3 class="font-bold text-orange-700 text-lg">MEMBER DATA MATCH</h3>
                <p class="text-sm text-gray-600 mt-1">Data yang dimasukkan memiliki kesamaan dengan member yang sudah terdaftar. Registrasi tidak dapat dilanjutkan.</p>
            </div>
        </div>

        <div id="dup-matches" class="mt-5 space-y-4"></div>

        <div class="mt-6 flex justify-end gap-3">
            <a href="{{ route('login') }}" class="hidden text-brand-700 font-medium text-sm px-4 py-2 border border-brand-200 rounded-lg">Login</a>
            <button type="button" id="dup-close" class="bg-brand-800 text-white px-5 py-2 rounded-lg text-sm font-semibold">Tutup</button>
        </div>
    </div>
</div>

@push('styles')
<style>
    .field-chip { display:inline-flex; align-items:center; gap:4px; font-size:12px; padding:2px 10px; border-radius:999px; margin:2px; }
    .field-match { background:#dcfce7; color:#166534; }
    .field-similar { background:#fef3c7; color:#92400e; }
    .field-different { background:#f3f4f6; color:#6b7280; }
</style>
@endpush

@push('scripts')
<script>
const form = document.getElementById('registration-form');
const modal = document.getElementById('dup-modal');
const statusEl = document.getElementById('dup-status');
let blocked = false;
let typingTimer = null;

// ===== OTP (verifikasi email anti-spambot) =====
const emailInput = form.email;
const otpInput = document.getElementById('otp-code');
const otpStatus = document.getElementById('otp-status');
const btnSend = document.getElementById('btn-otp-send');
const btnVerify = document.getElementById('btn-otp-verify');
let otpVerified = false;
let otpSent = false;
let resendTimer = null;

const emailValid = () => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput.value.trim());

function otpStatusHtml(html) { otpStatus.innerHTML = html; }

function startResendCountdown(seconds) {
    clearInterval(resendTimer);
    btnSend.disabled = true;
    btnSend.classList.add('opacity-50', 'cursor-not-allowed');
    let left = seconds;
    btnSend.textContent = `Kirim Ulang (${left}s)`;
    resendTimer = setInterval(() => {
        left--;
        if (left <= 0) {
            clearInterval(resendTimer);
            btnSend.disabled = false;
            btnSend.classList.remove('opacity-50', 'cursor-not-allowed');
            btnSend.textContent = 'Kirim Ulang Kode';
        } else {
            btnSend.textContent = `Kirim Ulang (${left}s)`;
        }
    }, 1000);
}

function resetOtp(message) {
    otpVerified = false;
    otpSent = false;
    btnVerify.disabled = true;
    if (message) otpStatusHtml(`<span class="text-amber-600 text-xs">${message}</span>`);
}

btnSend.addEventListener('click', async () => {
    if (!emailValid()) {
        otpStatusHtml('<span class="text-red-600 text-xs">Isi email yang valid terlebih dahulu.</span>');
        emailInput.focus();
        return;
    }
    btnSend.disabled = true;
    btnSend.classList.add('opacity-50', 'cursor-not-allowed');
    otpStatusHtml('<span class="text-gray-400 text-xs animate-pulse">⏳ Mengirim kode OTP…</span>');
    try {
        const res = await fetch('{{ route('register.otp.send') }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
            body: JSON.stringify({email: emailInput.value.trim()}),
        });
        const json = await res.json();
        otpSent = true;
        otpInput.value = '';
        otpVerified = false;
        btnVerify.disabled = false;
        otpStatusHtml(`<span class="text-emerald-600 text-xs">✉ ${json.message}</span>`);
        otpInput.focus();
        startResendCountdown(json.resend_in || 60);
    } catch (e) {
        btnSend.disabled = false;
        otpStatusHtml('<span class="text-red-600 text-xs">Gagal mengirim OTP. Coba lagi.</span>');
    }
    btnSend.classList.remove('opacity-50');
});

btnVerify.addEventListener('click', async () => {
    if (!/\d{6}/.test(otpInput.value.trim())) {
        otpStatusHtml('<span class="text-red-600 text-xs">Masukkan 6 digit kode OTP.</span>');
        otpInput.focus();
        return;
    }
    btnVerify.disabled = true;
    otpStatusHtml('<span class="text-gray-400 text-xs animate-pulse">⏳ Memeriksa kode…</span>');
    try {
        const res = await fetch('{{ route('register.otp.verify') }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
            body: JSON.stringify({email: emailInput.value.trim(), code: otpInput.value.trim()}),
        });
        const json = await res.json();
        if (json.status === 'verified') {
            otpVerified = true;
            otpInput.readOnly = true;
            btnVerify.textContent = '✓ Terverifikasi';
            otpStatusHtml('<span class="text-emerald-600 text-xs">✓ Email berhasil diverifikasi.</span>');
        } else {
            otpVerified = false;
            btnVerify.disabled = false;
            otpStatusHtml(`<span class="text-red-600 text-xs">✕ ${json.message || 'Kode OTP salah.'}</span>`);
        }
    } catch (e) {
        btnVerify.disabled = false;
        otpStatusHtml('<span class="text-red-600 text-xs">Gagal memverifikasi. Coba lagi.</span>');
    }
});

emailInput.addEventListener('input', () => { if (otpVerified) resetOtp('Email diubah — verifikasi ulang OTP.'); });
otpInput.addEventListener('input', () => { otpInput.value = otpInput.value.replace(/\D/g, '').slice(0, 6); });

const fieldLabels = {name: 'Name', email: 'Email', phone: 'Phone', id_number: 'ID Number', dob: 'DOB'};

async function runCheck() {
    const data = {
        full_name: form.full_name.value.trim(),
        email: form.email.value.trim(),
        phone: form.phone.value.trim(),
        dob: form.dob.value,
        id_number: form.id_number.value.trim(),
    };
    if (!data.full_name || !data.email || !data.phone) { statusEl.innerHTML = ''; return; }

    statusEl.innerHTML = '<span class="text-gray-400 text-xs animate-pulse">⏳ Memeriksa duplikasi data…</span>';

    try {
        const res = await fetch('{{ route('register.check-duplicate') }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
            body: JSON.stringify(data),
        });
        const json = await res.json();

        if (json.status === 'clear') {
            blocked = false;
            statusEl.innerHTML = '<span class="text-emerald-600 text-xs">✓ Data valid — tidak ditemukan duplikasi</span>';
            return;
        }

        blocked = true;
        statusEl.innerHTML = '<span class="text-orange-600 text-xs">⚠ Kemungkinan duplikasi data ditemukan</span>';
        showModal(json.matches);
    } catch (e) {
        statusEl.innerHTML = '';
    }
}

function fieldChip(field, status) {
    const label = fieldLabels[field] || field;
    const icon = status === 'match' ? '✓' : status === 'similar' ? '≈' : '✕';
    return `<span class="field-chip field-${status}">${icon} ${label}: ${status === 'match' ? 'Match' : status === 'similar' ? 'Similar' : 'Different'}</span>`;
}

function showModal(matches) {
    const wrap = document.getElementById('dup-matches');
    wrap.innerHTML = matches.map(m => `
        <div class="border border-orange-200 rounded-xl p-4 bg-orange-50/50">
            <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                <div class="text-gray-500">Member ID</div><div class="font-semibold">${m.member_no}</div>
                <div class="text-gray-500">Name</div><div>${m.full_name}</div>
                <div class="text-gray-500">Email</div><div>${m.email}</div>
                <div class="text-gray-500">Phone</div><div>${m.phone}</div>
                <div class="text-gray-500">Hotel</div><div>${m.hotel}</div>
                <div class="text-gray-500">Level</div><div>${m.level}</div>
                <div class="text-gray-500">Status</div><div>${m.status}</div>
            </div>
            <div class="mt-3 border-t border-orange-200 pt-2">
                ${Object.entries(m.fields).map(([f, s]) => fieldChip(f, s)).join('')}
            </div>
        </div>`).join('');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

document.getElementById('dup-close').addEventListener('click', () => {
    modal.classList.add('hidden');
    modal.classList.remove('flex');
});

document.querySelectorAll('.dup-field').forEach(el => {
    el.addEventListener('input', () => {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(runCheck, 700);
    });
});

form.addEventListener('submit', (e) => {
    if (blocked) {
        e.preventDefault();
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        return;
    }
    if (!otpVerified) {
        e.preventDefault();
        if (!otpSent) {
            otpStatusHtml('<span class="text-red-600 text-xs">Klik "Kirim Kode OTP" dan verifikasi email Anda sebelum mendaftar.</span>');
        } else {
            otpStatusHtml('<span class="text-red-600 text-xs">Verifikasi kode OTP terlebih dahulu.</span>');
        }
        otpInput.focus();
        otpInput.closest('fieldset').scrollIntoView({behavior: 'smooth', block: 'center'});
    }
});
</script>
@endpush
@endsection
