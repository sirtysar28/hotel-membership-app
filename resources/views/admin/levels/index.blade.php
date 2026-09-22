@extends('layouts.admin')
@section('title', 'Levels & Rules')
@section('page-title', 'Membership Levels & Rule Engine')

@section('content')
<div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-sm text-amber-800 mb-4">
    <strong>Rule Engine:</strong> threshold level dibaca dari database (bukan hard-coded). Perubahan di sini langsung berlaku pada evaluasi level berikutnya — tanpa ubah source code.
</div>

<div class="space-y-4">
    @foreach($levels as $level)
        @php($rule = $level->rules->firstWhere('is_active', true))
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-3">
                    <span class="w-4 h-8 rounded" style="background: {{ $level->card_color }}"></span>
                    <div>
                        <h3 class="font-bold text-brand-800">{{ $level->name }}</h3>
                        <div class="text-xs text-gray-400">{{ $level->description }}</div>
                    </div>
                </div>
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">Order #{{ $level->sort_order }}</span>
            </div>

            @if($rule)
                <form method="POST" action="{{ route('admin.levels.rules.update', $rule) }}" class="mt-5 grid sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Min Visit</label>
                        <input type="number" name="min_visit" min="0" value="{{ old('min_visit', $rule->min_visit) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Max Visit (kosong = ∞)</label>
                        <input type="number" name="max_visit" min="0" value="{{ old('max_visit', $rule->max_visit) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Min Spending (Rp)</label>
                        <input type="number" name="min_spending" min="0" step="1000" value="{{ old('min_spending', $rule->min_spending) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600">Evaluasi (bulan, 0 = lifetime)</label>
                        <input type="number" name="evaluation_period_months" min="0" value="{{ old('evaluation_period_months', $rule->evaluation_period_months) }}" class="mt-1 w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 text-sm text-gray-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="rounded" @checked($rule->is_active)> Aktif
                        </label>
                        <button class="bg-brand-800 text-white text-sm font-semibold px-4 py-2 rounded-lg">Simpan</button>
                    </div>
                </form>
            @else
                <div class="mt-4 text-sm text-gray-400">Belum ada rule aktif untuk level ini.</div>
            @endif

            <div class="mt-4 pt-3 border-t border-gray-100 flex flex-wrap gap-2">
                @foreach($level->benefits->where('is_active', true) as $benefit)
                    <span class="text-xs bg-brand-50 text-brand-700 px-2.5 py-1 rounded-full">{{ $benefit->name }}{{ $benefit->value ? ' (' . $benefit->value . ')' : '' }}</span>
                @endforeach
                <a href="{{ route('admin.benefits.index') }}" class="text-xs text-brand-600 hover:underline ml-1">Kelola benefit →</a>
            </div>
        </div>
    @endforeach
</div>
@endsection
