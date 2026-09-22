@extends('layouts.admin')
@section('title', 'Edit Member')
@section('page-title', 'Members — Edit')

@section('content')
<div class="max-w-3xl bg-white rounded-2xl shadow p-6 md:p-8">
    <h2 class="font-semibold text-brand-800 text-lg">Edit Member — {{ $member->member_no }}</h2>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mt-4">
            @foreach ($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.members.update', $member) }}" class="mt-6 grid sm:grid-cols-2 gap-4">
        @csrf
        @method('PUT')
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Full Name *</label>
            <input name="full_name" value="{{ old('full_name', $member->full_name) }}" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email *</label>
            <input type="email" name="email" value="{{ old('email', $member->email) }}" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Mobile Phone *</label>
            <div class="mt-1 flex gap-2">
                @include('admin.members._country-code', ['value' => old('phone_country_code', $member->phone_country_code ?: '+62')])
                <input name="phone" value="{{ old('phone', $member->phone) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2.5">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
            <input type="date" name="dob" value="{{ old('dob', $member->dob?->format('Y-m-d')) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Gender</label>
            <select name="gender" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                <option value="">--</option>
                <option value="male" @selected(old('gender', $member->gender) === 'male')>Male</option>
                <option value="female" @selected(old('gender', $member->gender) === 'female')>Female</option>
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea name="address" rows="2" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">{{ old('address', $member->address) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">ID Type</label>
            <select name="id_type" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                <option value="">--</option>
                @foreach(['ktp' => 'KTP', 'passport' => 'Passport', 'sim' => 'SIM', 'other' => 'Lainnya'] as $v => $l)
                    <option value="{{ $v }}" @selected(old('id_type', $member->id_type) === $v)>{{ $l }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">ID Number / Passport</label>
            <input name="id_number" value="{{ old('id_number', $member->id_number) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Company</label>
            <input name="company" value="{{ old('company', $member->company) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Occupation</label>
            <input name="occupation" value="{{ old('occupation', $member->occupation) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">No. Referensi Bukti Fisik (Accounting)</label>
            <input name="proof_reference" value="{{ old('proof_reference', $member->proof_reference) }}" maxlength="80" placeholder="mis. BUKTI-ACC-00123" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Hotel *</label>
            <select name="hotel_id" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" @selected(old('hotel_id', $member->hotel_id) == $hotel->id)>{{ $hotel->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Level *</label>
            <select name="level_id" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                @foreach($levels as $level)
                    <option value="{{ $level->id }}" @selected(old('level_id', $member->level_id) == $level->id)>{{ $level->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Valid Until</label>
            <input type="date" name="valid_until" value="{{ old('valid_until', $member->valid_until?->format('Y-m-d')) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div class="sm:col-span-2 flex justify-end gap-3 mt-2">
            <a href="{{ route('admin.members.show', $member) }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 text-sm">Batal</a>
            <button class="bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">Simpan</button>
        </div>
    </form>
</div>
@endsection
