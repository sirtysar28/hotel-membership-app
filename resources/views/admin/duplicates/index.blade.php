@extends('layouts.admin')
@section('title', 'Duplicate Review')
@section('page-title', 'Duplicate Review')

@section('content')
<div class="flex gap-2 mb-4 text-sm">
    <a href="{{ route('admin.duplicates.index') }}" class="px-3 py-1.5 rounded-lg {{ $status ? 'border border-gray-300 text-gray-600' : 'bg-brand-800 text-white font-semibold' }}">Semua</a>
    @foreach(['potential_duplicate', 'confirmed', 'rejected', 'merged'] as $s)
        <a href="{{ route('admin.duplicates.index', ['status' => $s]) }}" class="px-3 py-1.5 rounded-lg {{ $status === $s ? 'bg-brand-800 text-white font-semibold' : 'border border-gray-300 text-gray-600' }}">{{ ucwords(str_replace('_', ' ', $s)) }}</a>
    @endforeach
</div>

<div class="space-y-4">
    @forelse($duplicates as $dup)
        <div class="bg-white rounded-2xl shadow p-6">
            <div class="flex flex-wrap justify-between items-start gap-4">
                <div class="flex-1 min-w-[260px]">
                    <div class="flex items-center gap-2">
                        {!! status_badge($dup->status) !!}
                        <span class="text-xs text-gray-400">detected {{ $dup->created_at->format('d M Y H:i') }}</span>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4 mt-4">
                        {{-- Data submitted --}}
                        <div class="border border-gray-100 rounded-xl p-4 bg-gray-50/50">
                            <div class="text-xs uppercase tracking-wide text-gray-400 mb-2">Data Registrasi Baru</div>
                            <div class="text-sm space-y-1">
                                <div class="font-medium">{{ $dup->submitted_data['full_name'] ?? '-' }}</div>
                                <div class="text-gray-500">{{ $dup->submitted_data['email'] ?? '-' }}</div>
                                <div class="text-gray-500">{{ $dup->submitted_data['phone'] ?? '-' }}</div>
                                <div class="text-gray-500">{{ $dup->submitted_data['id_number'] ?? '-' }}</div>
                            </div>
                            <div class="mt-3 border-t border-gray-200 pt-2 flex flex-wrap">
                                @foreach(($dup->matched_fields ?? []) as $field => $state)
                                    <span class="text-xs px-2 py-0.5 rounded-full mr-1 mb-1 {{ $state === 'match' ? 'bg-emerald-100 text-emerald-700' : ($state === 'similar' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500') }}">
                                        {{ $state === 'match' ? '✓' : ($state === 'similar' ? '≈' : '✕') }} {{ ucwords(str_replace('_', ' ', $field)) }}: {{ ucfirst($state) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Existing member --}}
                        <div class="border border-brand-100 rounded-xl p-4 bg-brand-50/50">
                            <div class="text-xs uppercase tracking-wide text-brand-400 mb-2">Existing Member</div>
                            @if($dup->matchedMember)
                                <div class="text-sm space-y-1">
                                    <div class="font-mono text-xs text-brand-600">{{ $dup->matchedMember->member_no }}</div>
                                    <div class="font-medium">{{ $dup->matchedMember->full_name }}</div>
                                    <div class="text-gray-500">{{ $dup->matchedMember->email }} · {{ $dup->matchedMember->phone }}</div>
                                    <div class="text-gray-500">{{ $dup->matchedMember->hotel->name }} · {{ $dup->matchedMember->level->name }}</div>
                                </div>
                            @else
                                <div class="text-sm text-gray-400">Member sudah dihapus.</div>
                            @endif
                        </div>
                    </div>

                    @if($dup->status === 'potential_duplicate')
                        <form method="POST" action="{{ route('admin.duplicates.resolve', $dup) }}" class="mt-4 flex flex-wrap items-end gap-2">
                            @csrf
                            <div class="flex-1 min-w-[200px]">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Resolution Note</label>
                                <input name="resolution_note" placeholder="Catatan keputusan…" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            </div>
                            <input type="hidden" name="decision" value="confirmed">
                            <button onclick="this.form.decision.value='confirmed'" class="bg-emerald-600 text-white text-xs font-semibold px-4 py-2 rounded-lg">Konfirmasi Duplikat</button>
                            <button onclick="this.form.decision.value='rejected'" class="border border-gray-300 text-gray-600 text-xs font-semibold px-4 py-2 rounded-lg">Bukan Duplikat</button>
                            <button onclick="this.form.decision.value='merged'" class="border border-gray-300 text-gray-600 text-xs font-semibold px-4 py-2 rounded-lg">Tandai Merged</button>
                        </form>
                    @else
                        <div class="mt-4 text-xs text-gray-500">
                            Resolved {{ $dup->resolved_at?->format('d M Y H:i') }} oleh {{ $dup->resolver?->name ?? '-' }}
                            @if($dup->resolution_note) — "{{ $dup->resolution_note }}" @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-2xl shadow p-10 text-center text-gray-400">Tidak ada data duplicate check.</div>
    @endforelse
</div>

<div class="mt-4">{{ $duplicates->links() }}</div>
@endsection
