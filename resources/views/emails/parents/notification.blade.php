@extends('emails.layouts.master')

@section('content')
<h1>Parent Account Update</h1>

<p>Hello {{ $guardian->first_name }},</p>

@if($additionalNote)
<div style="background-color: #fff7ed; padding: 15px; border-radius: 8px; border-left: 4px solid #f97316; margin-bottom: 24px; color: #9a3412; font-size: 14px;">
    <strong>Account Note:</strong> {{ $additionalNote }}
</div>
@endif

<p>Your parental account at {{ config('app.name') }} has been processed. You can now access the Parent Portal to monitor your child's progress, view reports, and manage financial communications.</p>

<div style="background-color: #f8fafc; padding: 20px; border-radius: 12px; margin: 24px 0;">
    <h3 style="margin-top: 0; color: #1e293b; font-size: 16px;">Associated Students:</h3>
    <ul style="margin: 0; padding-left: 20px; color: #475569;">
        @foreach($students as $student)
            <li><strong>{{ $student->full_name }}</strong> (Adm: {{ $student->admission_number }})</li>
        @endforeach
    </ul>
</div>

<div style="background-color: #f1f5f9; padding: 20px; border-radius: 12px; margin: 24px 0; border-left: 4px solid #4f46e5;">
    <h3 style="margin-top: 0; color: #1e293b; font-size: 16px;">Login Credentials:</h3>
    <p style="margin: 8px 0;"><strong>Email:</strong> {{ $guardian->email }}</p>
    @if($password)
    <p style="margin: 8px 0;"><strong>Temporary Password:</strong> <span style="font-family: monospace; background: #e2e8f0; padding: 2px 6px; border-radius: 4px;">{{ $password }}</span></p>
    @endif
    <p style="margin: 8px 0; font-size: 14px; color: #64748b;"><em>You will be required to change your password upon first login if using the temporary password.</em></p>
</div>

<div class="button-wrapper">
    <a href="{{ $resetUrl }}" class="button">Set/Reset Your Password</a>
</div>

<p style="font-size: 14px; color: #64748b; text-align: center; margin-top: 32px;"> 
    Or login directly if you already have your credentials: 
</p>
<div class="button-wrapper" style="margin-top: 8px; text-align: center;">
    <a href="{{ route('login') }}" style="color: #4f46e5; text-decoration: underline; font-weight: 600;">System Login Page</a>
</div>

<p>If you have any questions or encounter issues logging in, please contact the school administration.</p>

<p>Best Regards,<br>The {{ config('app.name') }} Team</p>
@endsection
