@extends('emails.layouts.master')

@section('title', 'SMTP Test Email — Berhasil!')
@section('eyebrow', 'Pengaturan SMTP')
@section('preheader', 'Ini adalah email percobaan dari pengaturan SMTP Hotel Ciputra Membership.')
@section('accent', '#047857')

@section('content')
<p>Dear <strong>{{ $recipientName }}</strong>,</p>

<p style="text-align:center; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:16px; margin:20px 0;">
    <strong style="color:#065f46; font-size:15px; letter-spacing:1px;">✓ KONFIGURASI SMTP BERFUNGSI DENGAN BAIK</strong>
</p>

<p>Email ini dikirim dari halaman <strong>Admin &rarr; Settings &rarr; SMTP</strong> sebagai percobaan. Jika email ini sampai ke inbox Anda, konfigurasi SMTP sudah benar dan siap digunakan untuk seluruh email notifikasi sistem (welcome, upgrade, transaksi, renewal, reset password, dll).</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0f7ff; border-radius:10px; border-left:4px solid #fbbf24;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            <tr>
                <td style="padding:7px 0; color:#6b7280; width:42%;">Mailer</td>
                <td style="padding:7px 0; font-weight:bold; color:#0f2a4a;">{{ strtoupper($mailer) }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">SMTP Host</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $host ?: '-' }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Port</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $port ?: '-' }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Encryption</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ strtoupper($encryption) }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Dikirim</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ now()->format('d M Y H:i:s') }}</td>
            </tr>
        </table>
    </td></tr>
</table>
@endsection

@section('closing', 'Email percobaan — tidak perlu dibalas.')
