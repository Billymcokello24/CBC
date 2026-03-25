@extends('emails.layouts.master')

@section('content')
<h1>Welcome to the {{ config('app.name') }} Family!</h1>

<p>Hello {{ $admin->first_name }},</p>

<p>Congratulations! <strong>{{ $school->name }}</strong> has been successfully registered on {{ config('app.name') }}. We are thrilled to have you on board as we transform your school's digital operations together.</p>

<div class="credentials">
    <div class="credential-item">
        <span class="credential-label">School ID:</span>
        <span class="credential-value">{{ $school->id }}</span>
    </div>
    <div class="credential-item">
        <span class="credential-label">Admin Email:</span>
        <span class="credential-value">{{ $admin->email }}</span>
    </div>
</div>

<p>You can now access your dedicated school dashboard to set up your academic calendar, add staff, and enroll learners.</p>

<div class="button-wrapper">
    <a href="{{ route('login') }}" class="button">Access School Dashboard</a>
</div>

<p>To help you get started, we've prepared a "Quick Start Guide" which you can find in the help section of your dashboard.</p>

<p>Welcome to the future of school management!<br>The {{ config('app.name') }} Team</p>
@endsection
