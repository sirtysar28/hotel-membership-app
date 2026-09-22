@extends('emails.layouts.master')

@section('title', 'Akun Anda Telah Disetujui — CPC Membership')
@section('eyebrow', 'Approval Akun')
@section('preheader', 'Akun staff/manajer Anda telah disetujui dan kini dapat digunakan untuk login.')
@section('accent', '#047854')

@section('content')
<p>Dear <strong>{{ $user->name }}</strong>,</p>

<p>Permintaan akun Anda telah <strong style="color:#047854;">DISETUJUI</strong> dan kini aktif.</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0f7ff; border-radius:10px; border-left:4px solid #047854;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            <tr>
                <td style="padding:7px 0; color:#6b7280; width:42%;">Email Login</td>
                <td style="padding:7px 0; font-weight:bold; color:#0f2a4a;">{{ $user->email }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Role</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $user->roleLabel() }}</td>
            </tr>
            @if($user->hotel)
                <tr>
                    <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Unit / Hotel</td>
                    <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $user->hotel->name }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Disetujui Oleh</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $approver->name }} ({{ $approver->roleLabel() }})</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Waktu Approval</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ now()->translatedFormat('d M Y, H:i') }}</td>
            </tr>
        </table>
    </td></tr>
</table>

<p>Silakan login menggunakan email di atas beserta password yang telah dibuat admin. Segera amankan akun Anda — jangan bagikan kredensial kepada siapa pun.</p>
@endsection

@section('action_url', route('login'))
@section('action_label', 'Login Sekarang')

@section('closing', 'Email ini adalah notifikasi keamanan (update #18): pembuatan akun staff/manajer memerlukan persetujuan Super Admin.')
