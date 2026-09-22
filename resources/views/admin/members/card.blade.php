@extends('layouts.guest')
@section('title', 'Member Card')

@section('content')
<div class="max-w-lg mx-auto py-16 px-4">
    <h1 class="text-2xl font-bold text-brand-800 text-center">Digital Membership Card</h1>
    <div class="mt-8">@include('partials.digital-card', ['member' => $member])</div>
    <div class="text-center mt-6">
        <a href="{{ url()->previous() }}" class="text-brand-600 text-sm hover:underline">← Kembali</a>
    </div>
</div>
@endsection
