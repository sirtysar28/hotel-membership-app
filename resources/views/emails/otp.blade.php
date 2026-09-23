@extends('emails.layouts.master')

@section('title', 'Kode OTP Verifikasi Registrasi')
@section('eyebrow', 'Verifikasi Email')
@section('preheader', 'Gunakan kode OTP berikut untuk menyelesaikan registrasi membership Anda. Berlaku 10 menit.')

@section('content')
<p>Dear <strong>{{ $email }}</strong>,</p>
<p>Anda (atau seseorang yang menggunakan email ini) meminta kode OTP untuk menyelesaikan <strong>registrasi Ciputra Premiere Club (CPC)</strong>. Gunakan kode berikut:</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:24px 0;">
    <tr>
        <td align="center" style="background:#0f2a4a; border-radius:12px; padding:26px 20px; border-bottom:4px solid #fbbf24;">
            <div style="color:#fbbf24; font-size:11px; letter-spacing:4px; font-weight:bold; text-transform:uppercase;">Kode OTP Anda</div>
            <div style="color:#ffffff; font-size:38px; font-weight:bold; letter-spacing:12px; margin-top:10px; font-family:'Courier New',monospace;">{{ $otp->code }}</div>
            <div style="color:#93c5fd; font-size:12px; margin-top:12px;">Berlaku {{ \App\Services\OtpService::TTL_MINUTES }} menit · Maks {{ \App\Services\OtpService::MAX_ATTEMPTS }} percobaan</div>
        </td>
    </tr>
</table>

<p style="color:#6b7280; font-size:13px;">Jika Anda tidak merasa melakukan registrasi, abaikan email ini — tidak ada tindakan lebih lanjut yang diperlukan. Jangan bagikan kode OTP ini kepada siapa pun, termasuk pihak yang mengaku sebagai staf hotel.</p>
@endsection

@section('closing', 'Sampai jumpa di Ciputra Premiere Club!')
