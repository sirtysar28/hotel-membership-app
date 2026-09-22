@extends('emails.layouts.master')

@section('title', 'Voucher Redeemed — ' . $voucher->voucher_no)
@section('eyebrow', 'Penukaran Voucer')
@section('preheader', 'Penukaran voucer Anda telah disetujui manajer dan berhasil dicatat.')

@section('content')
<p>Dear <strong>{{ $member->full_name }}</strong>,</p>
<p>Permintaan penukaran voucer Anda telah <strong style="color:#047857;">DISETUJUI</strong> oleh manajer dan berhasil dicatat dengan rincian berikut:</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#ecfdf5; border-radius:10px; border-left:4px solid #10b981;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            <tr>
                <td style="padding:7px 0; color:#6b7280; width:42%;">ID Voucer</td>
                <td style="padding:7px 0; font-weight:bold; color:#065f46; font-family:'Courier New',monospace;">{{ $voucher->voucher_no }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #d1fae5;">Jenis Voucer</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #d1fae5;">{{ $voucher->type->name }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #d1fae5;">Ditukarkan di</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #d1fae5;">{{ $voucher->hotel?->name ?? '-' }}@if($voucher->outlet) — {{ $voucher->outlet }}@endif</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #d1fae5;">Waktu Penukaran</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #d1fae5;">{{ $voucher->redeemed_at?->format('d M Y, H:i') }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #d1fae5;">Disetujui Oleh</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #d1fae5;">{{ $manager->name }} (Manager)</td>
            </tr>
        </table>
    </td></tr>
</table>

<p>Riwayat penukaran voucer Anda dapat dilihat kapan saja melalui Member Portal menu <strong>Vouchers</strong>.</p>
@endsection

@section('action_url', route('portal.vouchers'))
@section('action_label', 'Lihat Voucer Saya')

@section('closing', 'Nikmati benefit membership Anda.')
