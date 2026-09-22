@extends('layouts.admin')
@section('title', 'Benefit Baru')
@section('page-title', 'Benefits — Baru')

@section('content')
<div class="max-w-2xl bg-white rounded-2xl shadow p-6 md:p-8">
    <h2 class="font-semibold text-brand-800 text-lg">Benefit Baru</h2>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm mt-4">
            @foreach ($errors->all() as $error)<div>• {{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.benefits.store') }}" class="mt-6 grid sm:grid-cols-2 gap-4">
        @csrf
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Nama Benefit *</label>
            <input name="name" value="{{ old('name') }}" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Category *</label>
            <select name="category" required class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                @foreach($categories as $value => $label)
                    <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Value</label>
            <input name="value" value="{{ old('value') }}" placeholder="20% / 1x Room Upgrade / …" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Level (opsional)</label>
            <select name="level_id" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                <option value="">Semua Level</option>
                @foreach($levels as $level)
                    <option value="{{ $level->id }}" @selected(old('level_id') == $level->id)>{{ $level->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Hotel (opsional)</label>
            <select name="hotel_id" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5 bg-white">
                <option value="">Semua Hotel</option>
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" @selected(old('hotel_id') == $hotel->id)>{{ $hotel->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="2" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2.5">{{ old('description') }}</textarea>
        </div>
        <label class="sm:col-span-2 flex items-center gap-2 text-sm text-gray-700">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" checked class="rounded"> Aktif
        </label>
        <div class="sm:col-span-2 flex justify-end gap-3">
            <a href="{{ route('admin.benefits.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 text-sm">Batal</a>
            <button class="bg-brand-800 text-white font-semibold px-6 py-2.5 rounded-lg text-sm">Simpan</button>
        </div>
    </form>
</div>
@endsection
