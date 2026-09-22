@extends('emails.layouts.master')

@section('title', 'Reset Password Akun Anda')
@section('eyebrow', 'Keamanan Akun')
@section('preheader', 'Link reset password ini berlaku ' . $expires . ' menit.')
@section('accent', '#1e6bb8')

@section('content')
<p>Dear <strong>{{ $user->name }}</strong>,</p>

<p>Kami menerima permintaan reset password untuk akun Anda dengan email <strong>{{ $user->email }}</strong>.</p>

<p>Silakan klik tombol di bawah ini untuk membuat password baru:</p>
@endsection

@section('action_url', $resetUrl)
@section('action_label', 'Reset Password Saya')

@section('closing', 'Jika Anda tidak meminta reset password, abaikan email ini — password Anda tidak akan berubah.')
