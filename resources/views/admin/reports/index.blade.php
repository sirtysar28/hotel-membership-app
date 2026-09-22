@extends('layouts.admin')
@section('title', 'Reports')

@php
    $reportTitles = ['members' => 'Member Report', 'revenue' => 'Revenue Report', 'visits' => 'Visit Report', 'duplicates' => 'Duplicate Report'];
    $current = $type;
@endphp
@section('page-title', 'Reports — ' . ($reportTitles[$current] ?? ''))

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3">
    <div class="flex flex-wrap gap-2 text-sm">
        @foreach($reportTitles as $key => $label)
            <a href="{{ route('admin.reports.index', $key) }}" class="px-4 py-2 rounded-lg {{ $current === $key ? 'bg-brand-800 text-white font-semibold' : 'border border-gray-300 text-gray-600 hover:bg-gray-50' }}">{{ $label }}</a>
        @endforeach
    </div>
    <div class="flex gap-2">
        <a href="{{ route('admin.reports.export', ['type' => $current, 'format' => 'csv']) }}?{{ http_build_query(request()->only(['hotel_id','type','level_id','status','from','to','preset','month'])) }}"
           class="bg-emerald-600 text-white text-xs font-semibold px-4 py-2 rounded-lg">Export CSV</a>
        <a href="{{ route('admin.reports.export', ['type' => $current, 'format' => 'pdf']) }}?{{ http_build_query(request()->only(['hotel_id','type','level_id','status','from','to','preset','month'])) }}"
           class="bg-red-600 text-white text-xs font-semibold px-4 py-2 rounded-lg">Export PDF</a>
    </div>
</div>

<div class="bg-white rounded-2xl shadow p-5 mt-4">
    <form method="GET" class="flex flex-wrap items-end gap-3 text-sm">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Hotel</label>
            <select name="hotel_id" class="border border-gray-300 rounded-lg px-3 py-2 bg-white">
                <option value="">Semua</option>
                @foreach($hotels as $hotel)
                    <option value="{{ $hotel->id }}" @selected(($filters['hotel_id'] ?? '') == $hotel->id)>{{ $hotel->name }}</option>
                @endforeach
            </select>
        </div>
        @if($current === 'members')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Type</label>
                <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 bg-white">
                    <option value="">Semua</option>
                    <option value="paid" @selected(($filters['type'] ?? '') === 'paid')>Paid</option>
                    <option value="free" @selected(($filters['type'] ?? '') === 'free')>Free</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Level</label>
                <select name="level_id" class="border border-gray-300 rounded-lg px-3 py-2 bg-white">
                    <option value="">Semua</option>
                    @foreach($levels as $level)
                        <option value="{{ $level->id }}" @selected(($filters['level_id'] ?? '') == $level->id)>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 bg-white">
                    <option value="">Semua</option>
                    @foreach(['active','pending_payment','pending_review','inactive','expired'] as $st)
                        <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ ucwords(str_replace('_',' ',$st)) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if($current === 'duplicates')
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 bg-white">
                    <option value="">Semua</option>
                    @foreach(['potential_duplicate','confirmed','rejected','merged'] as $st)
                        <option value="{{ $st }}" @selected(($filters['status'] ?? '') === $st)>{{ ucwords(str_replace('_',' ',$st)) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        {{-- Update #15 — dropdown per tanggal / per bulan utk laporan harian/bulanan --}}
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Periode</label>
            <select name="preset" id="rp-preset" class="border border-gray-300 rounded-lg px-3 py-2 bg-white">
                <option value="semua" @selected(($filters['preset'] ?? 'semua') === 'semua')>Semua Waktu</option>
                <option value="hari_ini" @selected(($filters['preset'] ?? '') === 'hari_ini')>Hari Ini</option>
                <option value="kemarin" @selected(($filters['preset'] ?? '') === 'kemarin')>Kemarin</option>
                <option value="7_hari" @selected(($filters['preset'] ?? '') === '7_hari')>7 Hari Terakhir</option>
                <option value="30_hari" @selected(($filters['preset'] ?? '') === '30_hari')>30 Hari Terakhir</option>
                <option value="bulan_ini" @selected(($filters['preset'] ?? '') === 'bulan_ini')>Bulan Ini</option>
                <option value="bulan" @selected(($filters['preset'] ?? '') === 'bulan')>Per Bulan…</option>
                <option value="rentang" @selected(($filters['preset'] ?? '') === 'rentang')>Rentang Tanggal…</option>
            </select>
        </div>
        <div id="rp-month" class="{{ ($filters['preset'] ?? '') === 'bulan' ? '' : 'hidden' }}">
            <label class="block text-xs font-medium text-gray-600 mb-1">Pilih Bulan</label>
            <input type="month" name="month" value="{{ $filters['month'] ?? '' }}" class="border border-gray-300 rounded-lg px-3 py-2 bg-white">
        </div>
        <div id="rp-range" class="flex gap-2 {{ ($filters['preset'] ?? '') === 'rentang' ? '' : 'hidden' }}">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
                <input type="date" name="from" value="{{ $filters['from'] ?? '' }}" class="border border-gray-300 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
                <input type="date" name="to" value="{{ $filters['to'] ?? '' }}" class="border border-gray-300 rounded-lg px-3 py-2">
            </div>
        </div>
        <button class="bg-brand-600 text-white px-4 py-2 rounded-lg font-medium">Terapkan</button>
    </form>
    @if(!empty($filters['from']) && !empty($filters['to']))
        <p class="text-xs text-gray-400 mt-3">Periode terpilih: {{ \Illuminate\Support\Carbon::parse($filters['from'])->translatedFormat('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($filters['to'])->translatedFormat('d M Y') }}</p>
    @endif
</div>

<div class="bg-white rounded-2xl shadow mt-4 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 text-left text-xs uppercase text-gray-500">
            <tr>@foreach($headers as $h)<th class="px-4 py-3 whitespace-nowrap">{{ $h }}</th>@endforeach</tr>
        </thead>
        <tbody class="divide-y">
            @forelse($rows as $row)
                <tr class="hover:bg-brand-50/30">@foreach($row as $cell)<td class="px-4 py-2.5 whitespace-nowrap">{{ $cell }}</td>@endforeach</tr>
            @empty
                <tr><td class="px-4 py-10 text-center text-gray-400" colspan="{{ count($headers) }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

@push('scripts')
<script>
    // Toggle input periode laporan berdasarkan preset (update #15)
    const rpPreset = document.getElementById('rp-preset');
    if (rpPreset) {
        const rpMonth = document.getElementById('rp-month');
        const rpRange = document.getElementById('rp-range');
        const sync = () => {
            rpMonth.classList.toggle('hidden', rpPreset.value !== 'bulan');
            rpRange.classList.toggle('hidden', rpPreset.value !== 'rentang');
        };
        rpPreset.addEventListener('change', sync);
        sync();
    }
</script>
@endpush
@endsection
