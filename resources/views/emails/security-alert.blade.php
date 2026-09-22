@extends('emails.layouts.master')

@section('title', 'Security Alert — Aktivitas Akun CPC Anda')
@section('eyebrow', 'Notifikasi Keamanan')
@section('preheader', 'Kami mendeteksi aktivitas baru pada data membership Anda.')

@section('content')
@php
    $events = [
        'registration' => 'Registrasi membership baru telah dibuat dengan email ini.',
        'membership_activated' => 'Membership Anda telah diaktifkan.',
        'password_changed' => 'Password akun Member Portal Anda telah diubah.',
        'login_new_device' => 'Login ke Member Portal dari perangkat baru.',
    ];
    $eventText = $events[$event] ?? 'Aktivitas baru terdeteksi pada akun membership Anda.';
@endphp

<p>Dear <strong>{{ $member->full_name }}</strong>,</p>

<p>Sebagai bagian dari komitmen kami menjaga keamanan data Anda, kami menginformasikan aktivitas berikut pada akun membership Anda:</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#fffbeb; border-radius:10px; border-left:4px solid #f59e0b;">
    <tr><td style="padding:18px 22px; font-size:14px; color:#78350f;">
        <strong style="font-size:15px;">⚠ {{ $eventText }}</strong>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:13px; margin-top:10px;">
            <tr>
                <td style="padding:5px 0; color:#92400e; width:42%;">Member ID</td>
                <td style="padding:5px 0; font-weight:bold; color:#78350f;">{{ $member->member_no }}</td>
            </tr>
            <tr>
                <td style="padding:5px 0; color:#92400e; border-top:1px solid #fde68a;">Waktu</td>
                <td style="padding:5px 0; color:#78350f; border-top:1px solid #fde68a;">{{ now()->timezone(config('app.timezone'))->format('d M Y, H:i') }} ({{ config('app.timezone') }})</td>
            </tr>
        </table>
    </td></tr>
</table>

<p style="color:#6b7280; font-size:13px;">Jika aktivitas ini <strong>bukan</strong> dilakukan oleh Anda, segera ubah password Member Portal Anda melalui menu <em>Lupa Password</em> dan hubungi tim membership kami.</p>
@endsection

@section('action_url', route('login'))
@section('action_label', 'Buka Member Portal')

@section('closing', 'Terima kasih telah menjaga keamanan akun Anda.')
