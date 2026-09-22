@extends('emails.layouts.master')

@section('title', 'Visit / Transaction Confirmation')
@section('eyebrow', 'Konfirmasi Transaksi')
@section('preheader', 'Transaksi Anda telah tercatat: ' . $transaction->transaction_no)

@section('content')
<p>Dear <strong>{{ $member->full_name }}</strong>,</p>
<p>Transaksi Anda telah tercatat dengan rincian berikut:</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; background:#f0f7ff; border-radius:10px; border-left:4px solid #fbbf24;">
    <tr><td style="padding:18px 22px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
            <tr>
                <td style="padding:7px 0; color:#6b7280; width:42%;">Transaction No</td>
                <td style="padding:7px 0; font-weight:bold; color:#0f2a4a;">{{ $transaction->transaction_no }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Date</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $transaction->transaction_date->format('d M Y') }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Type</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $transaction->typeLabel() }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Hotel / Outlet</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">{{ $transaction->hotel->name }}{{ $transaction->outlet ? ' — ' . $transaction->outlet : '' }}</td>
            </tr>
            <tr>
                <td style="padding:7px 0; color:#6b7280; border-top:1px solid #dbeefe;">Amount</td>
                <td style="padding:7px 0; color:#111827; border-top:1px solid #dbeefe;">Rp{{ number_format((float) $transaction->amount, 0, ',', '.') }}</td>
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

<p>Riwayat visit &amp; transaksi lengkap dapat dilihat di Member Portal.</p>
@endsection

@section('action_url', route('login'))
@section('action_label', 'Lihat Riwayat Transaksi')

@section('closing', 'Terima kasih atas kunjungan Anda.')
