@extends('emails.layouts.master')

@section('content')
<h1>Reset Your Password</h1>

<p>Hello {{ $user->first_name }},</p>

<p>You are receiving this email because we received a password reset request for your account. If you did not request a password reset, no further action is required.</p>

<div class="button-wrapper">
    <a href="{{ url(config('app.url').route('password.reset', ['token' => $token, 'email' => $user->email], false)) }}" class="button">Reset Password</a>
</div>

<p>This password reset link will expire in {{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire') }} minutes.</p>

<p>For security, please do not share this link with anyone.</p>

<div class="divider"></div>

<p style="font-size: 12px; color: #94a3b8;">
    If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
    <br>
    <a href="{{ url(config('app.url').route('password.reset', ['token' => $token, 'email' => $user->email], false)) }}" style="word-break: break-all; color: #2563eb;">
        {{ url(config('app.url').route('password.reset', ['token' => $token, 'email' => $user->email], false)) }}
    </a>
</p>

<p>Regards,<br>The {{ config('app.name') }} Team</p>
@endsection
