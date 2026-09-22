@extends('layouts.portal')
@section('title', 'My Profile')

@section('content')
<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-brand-800">My Profile</h1>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mt-4">
            @foreach ($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('portal.profile.update') }}" class="bg-white rounded-2xl shadow p-6 mt-4 grid sm:grid-cols-2 gap-4">
        @csrf
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Full Name</label>
            <input value="{{ $member->full_name }}" disabled class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50 text-gray-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input value="{{ $member->email }}" disabled class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50 text-gray-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Mobile Phone</label>
            <input name="phone" value="{{ old('phone', $member->phone) }}" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Date of Birth</label>
            <input value="{{ $member->dob?->format('Y-m-d') ?? '' }}" type="date" disabled class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50 text-gray-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Hotel</label>
            <input value="{{ $member->hotel->name }}" disabled class="mt-1 w-full border border-gray-200 rounded-lg px-3 py-2.5 bg-gray-50 text-gray-500">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea name="address" rows="2" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">{{ old('address', $member->address) }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Company</label>
            <input name="company" value="{{ old('company', $member->company) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Occupation</label>
            <input name="occupation" value="{{ old('occupation', $member->occupation) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div class="sm:col-span-2 flex justify-end">
            <button class="bg-brand-800 hover:bg-brand-700 text-white font-semibold px-6 py-2.5 rounded-lg">Simpan Perubahan</button>
        </div>
    </form>

    <p class="text-xs text-gray-400 mt-3">Perubahan nama, email, dan tanggal lahir hanya dapat dilakukan melalui Admin hotel.</p>
</div>
@endsection
