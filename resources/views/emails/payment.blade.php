@extends('emails.layouts.master')

@section('title', 'Payment Verified — Membership Activated')
@section('eyebrow', 'Pembayaran Terverifikasi')
@section('preheader', 'Pembayaran membership Anda telah terverifikasi. Membership Anda kini AKTIF.')
@section('accent', '#047857')

@section('content')
<p>Dear <strong>{{ $member->full_name }}</strong>,</p>

<p style="text-align:center; background:#ecfdf5; border:1px solid #a7f3d0; border-radius:10px; padding:16px; margin:20px 0;">
    <strong style="color:#065f46; font-size:15px; letter-spacing:1px;">✓ PEMBAYARAN TERVERIFIKASI — MEMBERSHIP AKTIF</strong>
</p>

<p>Pembayaran membership Anda telah terverifikasi oleh tim Finance dan membership kini <strong>AKTIF</strong>:</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0f7ff; border-radius:10px; border-left:4px solid #fbbf24;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            <tr>
                <td style="padding:7px 0; color:#6b7280; width:42%;">Member ID</td>
                <td style="padding:7px 0; font-weight:bold; color:#0f2a4a;">{{ $member->member_no }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Amount</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">Rp{{ number_format((float) $payment->amount, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Paid At</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $payment->paid_at?->format('d M Y H:i') }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Valid Until</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $member->valid_until?->format('d M Y') }}</td>
            </tr>
        </table>
    </td></tr>
</table>

<p>Digital membership card telah dikirim terpisah ke email Anda.</p>
@endsection

@section('action_url', route('login'))
@section('action_label', 'Buka Member Portal')

@section('closing', 'Selamat menikmati seluruh benefit membership Anda.')
