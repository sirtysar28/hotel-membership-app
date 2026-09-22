@extends('emails.layouts.master')

@section('title', 'Membership Diamond Berakhir — Konversi ke Signature')
@section('eyebrow', 'Kedaluwarsa Diamond')
@section('preheader', 'Masa keanggotaan Diamond Anda telah berakhir dan dikonversi ke tingkat Ciputra Signature.')
@section('accent', '#7c3aed')

@section('content')
<p>Dear <strong>{{ $member->full_name }}</strong>,</p>

<p>Masa keanggotaan <strong style="color:#7c3aed;">CIPUTRA DIAMOND</strong> Anda telah berakhir dan tidak diperpanjang.</p>

<p style="text-align:center; background:#f5f3ff; border:1px solid #ddd6fe; border-radius:10px; padding:18px; margin:20px 0;">
    <span style="font-size:13px; color:#6b7280; letter-spacing:2px; font-weight:bold;">SESUAI ATURAN CPC §7</span><br>
    <span style="font-size:16px; color:#374151;">
        <strong style="text-transform:uppercase; color:#7c3aed;">DIAMOND — EXPIRED</strong>
        &nbsp;&rarr;&nbsp;
        <strong style="text-transform:uppercase; color:#0f2a4a; font-size:20px;">SIGNATURE</strong>
    </span>
</p>

<p>Keanggotaan Anda kini berlanjut sebagai anggota gratis di tingkat <strong>Ciputra Signature</strong>. Selanjutnya, tingkat keanggotaan Anda akan mengikuti aturan aktivitas &amp; ketidakaktifan tier gratis (turun satu tingkat setelah 12 bulan tanpa aktivitas hotel yang memenuhi syarat).</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0f7ff; border-radius:10px; border-left:4px solid #7c3aed;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            <tr>
                <td style="padding:7px 0; color:#6b7280; width:42%;">Member ID</td>
                <td style="padding:7px 0; font-weight:bold; color:#0f2a4a;">{{ $member->member_no }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Berlaku s/d</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $member->valid_until?->format('d M Y') ?? '-' }}</td>
            </tr>
        </table>
    </td></tr>
</table>

<p>Ingin menikmati kembali 12 voucer Diamond &amp; benefit eksklusifnya? Lakukan perpanjangan sekarang — periode keanggotaan baru akan langsung aktif dengan paket voucer baru.</p>
@endsection

@section('action_url', route('login'))
@section('action_label', 'Perpanjang Keanggotaan Diamond')

@section('closing', 'Voucer yang belum digunakan dari periode lama telah kedaluwarsa sesuai masa berlakunya dan tidak dapat dipindahkan ke periode berikutnya (§6).')
