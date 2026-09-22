@extends('emails.layouts.master')

@section('title', 'Welcome to Hotel Ciputra Membership!')
@section('eyebrow', 'Membership Aktif')
@section('preheader', 'Selamat datang! Membership Anda telah aktif. Berikut detail kartu membership Anda.')

@section('content')
<p>Dear <strong>{{ $member->full_name }}</strong>,</p>
<p>Terima kasih telah bergabung. Berikut detail membership Anda:</p>

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
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Membership Type</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe; text-transform:capitalize;">{{ $member->membership_type }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Level</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;"><strong style="color:#b45309;">{{ $member->level->name }}</strong></td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Valid Until</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $member->valid_until?->format('d M Y') }}</td>
            </tr>
        </table>
    </td></tr>
</table>

<p>Digital membership card Anda dengan QR code dapat diakses melalui Member Portal. Tunjukkan QR code setiap kali stay atau transaksi F&amp;B untuk mencatat visit &amp; spending Anda.</p>
@endsection

@section('action_url', route('login'))
@section('action_label', 'Buka Member Portal')

@section('closing', 'Nikmati pengalaman membership terbaik bersama kami.')
