@extends('emails.layouts.master')

@section('content')
<h1>Welcome to {{ config('app.name') }}!</h1>

<p>Hello {{ $user->first_name }},</p>

<p>Your account has been successfully created. You can now log in and start using our platform to manage your educational needs.</p>

<div class="credentials">
    <div class="credential-item">
        <span class="credential-label">Login URL:</span>
        <span class="credential-value">{{ route('login') }}</span>
    </div>
    <div class="credential-item">
        <span class="credential-label">Email:</span>
        <span class="credential-value">{{ $user->email }}</span>
    </div>
    <div class="credential-item">
        <span class="credential-label">Password:</span>
        <span class="credential-value">{{ $password }}</span>
    </div>
</div>

<p><strong>Note:</strong> For security reasons, we recommend that you change your password immediately after your first login.</p>

<div class="button-wrapper">
    <a href="{{ route('login') }}" class="button">Log In Now</a>
</div>

<p>If you didn't expect this email, you can safely ignore it.</p>

<p>Welcome aboard,<br>The {{ config('app.name') }} Team</p>
@endsection
