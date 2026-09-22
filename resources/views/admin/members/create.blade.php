@extends('layouts.admin')
@section('title', 'Tambah Member')
@section('page-title', 'Members — Tambah')

@section('content')
<div class="max-w-3xl bg-white rounded-2xl shadow p-6 md:p-8">
    <h2 class="font-semibold text-brand-800 text-lg">Registrasi Member oleh Admin</h2>
    <p class="text-sm text-gray-500 mt-1">Sistem juga melakukan duplicate check pada halaman publik; di sini pengecekan dilakukan manual oleh admin.</p>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mt-4">
            @foreach ($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.members.store') }}" class="mt-6 grid sm:grid-cols-2 gap-4">
        @csrf
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Full Name *</label>
            <input name="full_name" value="{{ old('full_name') }}" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Mobile Phone *</label>
            <div class="mt-1 flex gap-2">
                @include('admin.members._country-code', ['value' => old('phone_country_code', '+62')])
                <input name="phone" value="{{ old('phone') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
            <input type="date" name="dob" value="{{ old('dob') }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Gender</label>
            <select name="gender" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                <option value="">--</option>
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
                <option value="">--</option>
                @foreach(['ktp' => 'KTP', 'passport' => 'Passport', 'sim' => 'SIM', 'other' => 'Lainnya'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('id_type') === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">ID Number / Passport</label>
            <input name="id_number" value="{{ old('id_number') }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Company</label>
            <input name="company" value="{{ old('company') }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Occupation</label>
            <input name="occupation" value="{{ old('occupation') }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">No. Referensi Bukti Fisik (Accounting)</label>
            <input name="proof_reference" value="{{ old('proof_reference') }}" maxlength="80" placeholder="mis. BUKTI-ACC-00123" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Hotel *</label>
            <select name="hotel_id" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                <option value="">-- Pilih --</option>
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" @selected(old('hotel_id') == $hotel->id)>{{ $hotel->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Membership Type *</label>
            <select name="membership_type" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                <option value="free" @selected(old('membership_type') === 'free')>Free (aktif langsung)</option>
                <option value="paid" @selected(old('membership_type') === 'paid')>Paid ({{ format_idr(\App\Models\Setting::get('paid_membership_price', 2200000)) }})</option>
            </select>
        </div>
        <div class="sm:col-span-2 flex justify-end gap-3 mt-2">
            <a href="{{ route('admin.members.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 text-sm">Batal</a>
            <button class="bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">Simpan</button>
        </div>
    </form>
</div>
@endsection
