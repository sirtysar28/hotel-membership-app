@extends('emails.layouts.master')

@section('title', 'Paket Voucer CPC Anda Telah Diterbitkan')
@section('eyebrow', 'Voucer Membership')
@section('preheader', 'Paket voucer membership Anda telah diterbitkan dan siap digunakan.')

@section('content')
@php
    $byType = collect($vouchers)->groupBy(fn ($v) => $v->type->name);
@endphp
<p>Dear <strong>{{ $member->full_name }}</strong>,</p>
<p>Selamat! Paket voucer membership Anda telah diterbitkan dengan total <strong>{{ count($vouchers) }} voucer</strong>@if($period) untuk periode keanggotaan <strong>{{ $period->label() }}</strong>@endif. Berikut rincian voucer Anda:</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0f7ff; border-radius:10px; border-left:4px solid #fbbf24;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            @foreach($byType as $typeName => $group)
                <tr>
                    <td style="padding:7px 0; color:#6b7280; border-top: {{ $loop->first ? 'none' : '1px solid #dbeefe' }};">{{ $typeName }}</td>
                    <td style="padding:7px 0; font-weight:bold; color:#0f2a4a; text-align:right; border-top: {{ $loop->first ? 'none' : '1px solid #dbeefe' }};">{{ $group->count() }} voucer</td>
                </tr>
            @endforeach
            <tr>
                <td style="padding:9px 0 0 0; color:#92400e; border-top:2px solid #fbbf24;"><strong>Total</strong></td>
                <td style="padding:9px 0 0 0; font-weight:bold; color:#92400e; text-align:right; border-top:2px solid #fbbf24;">{{ count($vouchers) }} voucer</td>
            </tr>
        </table>
    </td></tr>
</table>

<p>Semua ID Voucer unik Anda (contoh: <code style="background:#f3f4f6; padding:2px 6px; border-radius:4px; font-family:'Courier New',monospace;">{{ $vouchers[0]->voucher_no ?? 'VCH-…' }}</code>) dapat dilihat di Member Portal. Voucer berlaku sampai <strong>{{ $period?->end_date->format('d M Y') ?? $member->valid_until?->format('d M Y') ?? '-' }}</strong> (mengikuti masa berlaku periode keanggotaan) dan tidak dapat diakumulasikan ke periode berikutnya.</p>
<p>Penukaran voucer diajukan oleh staf hotel di outlet dan disetujui oleh manajer — Anda akan menerima notifikasi email setiap kali penukaran disetujui.</p>
@endsection

@section('action_url', route('portal.vouchers'))
@section('action_label', 'Lihat Voucer Saya')

@section('closing', 'Nikmati benefit membership Anda.')
