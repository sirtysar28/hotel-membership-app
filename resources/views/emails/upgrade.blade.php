@extends('emails.layouts.master')

@section('title', 'Membership Upgrade — Congratulations!')
@section('eyebrow', 'Level Naik')
@section('preheader', 'Selamat! Level membership Anda telah naik. Nikmati benefit eksklusif level baru Anda.')
@section('accent', '#047857')

@section('content')
<p>Dear <strong>{{ $member->full_name }}</strong>,</p>

<p style="text-align:center; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:18px; margin:20px 0;">
    <span style="color:#065f46; font-size:13px; letter-spacing:2px; font-weight:bold;">CONGRATULATIONS!</span><br>
    <span style="font-size:16px; color:#374151;">
        <strong style="text-transform:uppercase; color:#6b7280;">{{ $oldLevel->name }}</strong>
        &nbsp;&rarr;&nbsp;
        <strong style="text-transform:uppercase; color:#b45309; font-size:20px;">{{ $newLevel->name }}</strong>
    </span>
</p>

<p>Your membership has been upgraded. Berikut ringkasan akun Anda:</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0f7ff; border-radius:10px; border-left:4px solid #fbbf24;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            <tr>
                <td style="padding:7px 0; color:#6b7280; width:42%;">Member ID</td>
                <td style="padding:7px 0; font-weight:bold; color:#0f2a4a;">{{ $member->member_no }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Level Baru</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;"><strong style="color:#b45309;">{{ $newLevel->name }}</strong></td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Total Visit</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $member->total_visits }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Total Spending</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">Rp{{ number_format((float) $member->total_spending, 0, ',', '.') }}</td>
            </tr>
        </table>
    </td></tr>
</table>

<p>Digital card baru telah diterbitkan dengan level <strong>{{ $newLevel->name }}</strong>. Nikmati benefit eksklusif level baru Anda!</p>
@endsection

@section('action_url', route('login'))
@section('action_label', 'Lihat Kartu & Benefit Baru')

@section('closing', 'Terus kumpulkan visit & spending untuk benefit yang lebih besar.')
