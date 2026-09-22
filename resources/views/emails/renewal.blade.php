@extends('emails.layouts.master')

@section('title', 'Membership Renewal Reminder')
@section('eyebrow', 'Pengingat Perpanjangan')
@section('preheader', 'Membership Anda akan segera berakhir. Segera lakukan perpanjangan.')
@section('accent', '#b45309')

@section('content')
<p>Dear <strong>{{ $member->full_name }}</strong>,</p>

<p style="text-align:center; background:#fffbeb; border:1px solid #fde68a; border-radius:10px; padding:18px; margin:20px 0;">
    <span style="color:#92400e; font-size:13px; letter-spacing:2px; font-weight:bold;">MASA BERLAKU MEMBERSHIP</span><br>
    <strong style="font-size:19px; color:#0f2a4a;">{{ $member->valid_until?->format('d M Y') ?? '-' }}</strong>
</p>

<p>Membership Anda (<strong>{{ $member->member_no }}</strong>) akan berakhir pada tanggal di atas. Segera lakukan perpanjangan untuk tetap menikmati benefit level <strong>{{ $member->level->name }}</strong> Anda.</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0f7ff; border-radius:10px; border-left:4px solid #fbbf24;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            <tr>
                <td style="padding:7px 0; color:#6b7280; width:42%;">Member ID</td>
                <td style="padding:7px 0; font-weight:bold; color:#0f2a4a;">{{ $member->member_no }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Level</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;"><strong style="color:#b45309;">{{ $member->level->name }}</strong></td>
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
@section('action_label', 'Perpanjang Membership')

@section('closing', 'Kami menantikan kunjungan Anda berikutnya.')
