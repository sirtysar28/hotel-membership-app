@extends('layouts.admin')
@section('title', 'Hotel Baru')
@section('page-title', 'Hotels — Baru')

@section('content')
<div class="max-w-xl bg-white rounded-2xl shadow p-6 md:p-8">
    <h2 class="font-semibold text-brand-800 text-lg">Hotel Baru</h2>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mt-4">
            @foreach ($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.hotels.store') }}" class="mt-6 grid sm:grid-cols-2 gap-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Code *</label>
            <input name="code" value="{{ old('code') }}" required maxlength="10" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 font-mono uppercase">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Name *</label>
            <input name="name" value="{{ old('name') }}" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">City</label>
            <input name="city" value="{{ old('city') }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Phone</label>
            <input name="phone" value="{{ old('phone') }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Address</label>
            <textarea name="address" rows="2" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">{{ old('address') }}</textarea>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div class="sm:col-span-2 flex justify-end gap-3">
            <a href="{{ route('admin.hotels.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 text-sm">Batal</a>
            <button class="bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">Simpan</button>
        </div>
    </form>
</div>
@endsection
