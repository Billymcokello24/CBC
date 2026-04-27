<x-mail::message>
# Welcome to {{ config('app.name') }}

Hello,

Thank you for starting your school registration. Please use the verification code below to confirm your email address and continue with your account setup:

<x-mail::panel>
# {{ $otp }}
</x-mail::panel>

This code will expire in 15 minutes. If you did not request this, please ignore this email.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
