@extends('emails.layouts.master')

@section('title', 'Membership Expiration Reminder')
@section('eyebrow', 'Pemberitahuan Kedaluwarsa')
@section('preheader', 'Membership Anda telah atau segera berakhir. Hubungi Front Office kami.')
@section('accent', '#b91c1c')

@section('content')
<p>Dear <strong>{{ $member->full_name }}</strong>,</p>

<p style="text-align:center; background:#fef2f2; border:1px solid #fecaca; border-radius:10px; padding:18px; margin:20px 0;">
    <span style="color:#991b1b; font-size:13px; letter-spacing:2px; font-weight:bold;">MASA BERLAKU BERAKHIR PADA</span><br>
    <strong style="font-size:19px; color:#0f2a4a;">{{ $member->valid_until?->format('d M Y') ?? '-' }}</strong>
</p>

<p>Membership Anda (<strong>{{ $member->member_no }}</strong>) telah atau akan segera berakhir. Hubungi Front Office hotel untuk informasi perpanjangan agar benefit level <strong>{{ $member->level->name }}</strong> Anda dapat segera diaktifkan kembali.</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0f7ff; border-radius:10px; border-left:4px solid #fbbf24;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            <tr>
                <td style="padding:7px 0; color:#6b7280; width:42%;">Member ID</td>
                <td style="padding:7px 0; font-weight:bold; color:#0f2a4a;">{{ $member->member_no }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Hotel</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $member->hotel->name }}</td>
            </tr>
        </table>
    </td></tr>
</table>
@endsection

@section('action_url', route('login'))
@section('action_label', 'Hubungi Kami via Member Portal')

@section('closing', 'Jangan sampai kehilangan benefit eksklusif Anda.')
