@extends('emails.layouts.master')

@section('title', 'Informasi Tingkat Keanggotaan Anda')
@section('eyebrow', 'Penyesuaian Tier')
@section('preheader', 'Tingkat keanggotaan Anda telah disesuaikan sesuai aturan ketidakaktifan (12 bulan tanpa aktivitas).')
@section('accent', '#b45309')

@section('content')
<p>Dear <strong>{{ $member->full_name }}</strong>,</p>

<p>Sesuai aturan keanggotaan CPC (§8), tingkat keanggotaan gratis diturunkan satu tingkat apabila tidak ada aktivitas hotel yang memenuhi syarat selama <strong>12 bulan berturut-turut</strong>.</p>

<p style="text-align:center; background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:18px; margin:20px 0;">
    <span style="font-size:13px; color:#92400e; letter-spacing:2px; font-weight:bold;">PENYESUAIAN TINGKAT</span><br>
    <span style="font-size:16px; color:#374151;">
        <strong style="text-transform:uppercase; color:#6b7280;">{{ $oldLevel->name }}</strong>
        &nbsp;&rarr;&nbsp;
        <strong style="text-transform:uppercase; color:#b45309; font-size:20px;">{{ $newLevel->name }}</strong>
    </span>
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0f7ff; border-radius:10px; border-left:4px solid #fbbf24;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            <tr>
                <td style="padding:7px 0; color:#6b7280; width:42%;">Member ID</td>
                <td style="padding:7px 0; font-weight:bold; color:#0f2a4a;">{{ $member->member_no }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Aktivitas Terakhir</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $member->last_activity_date?->format('d M Y') ?? '-' }}</td>
            </tr>
        </table>
    </td></tr>
</table>

<p>Anda dapat naik kembali ke tingkat yang lebih tinggi dengan mencatatkan kunjungan/transaksi hotel yang memenuhi syarat: <strong>Classic</strong> (0–25), <strong>Privilege</strong> (26–50), <strong>Signature</strong> (&gt;50).</p>
@endsection

@section('action_url', route('login'))
@section('action_label', 'Lihat Keanggotaan Saya')

@section('closing', 'Satu kunjungan/transaksi yang memenuhi syarat cukup untuk mengatur ulang periode ketidakaktifan Anda.')
