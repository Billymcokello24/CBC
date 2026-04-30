@extends('emails.layouts.master')

@section('content')
<h1>{{ $title }}</h1>

<p>Hello {{ $staff->first_name }},</p>

<div style="background-color: #f8fafc; padding: 25px; border-radius: 12px; border-left: 4px solid #4f46e5; margin: 24px 0; color: #1e293b;">
    <p style="margin: 0; font-size: 16px; line-height: 1.6;">
        {{ $messageBody }}
    </p>
</div>

@if($actionUrl)
<div class="button-wrapper">
    <a href="{{ $actionUrl }}" class="button">{{ $actionText }}</a>
</div>
@endif

<p style="margin-top: 32px;">If you have any questions regarding this update, please contact the institutional administration.</p>

<p>Best Regards,<br>The {{ config('app.name') }} Team</p>

<div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #94a3b8; text-align: center;">
    This is an automated notification from the {{ config('app.name') }} management system.
</div>
@endsection
