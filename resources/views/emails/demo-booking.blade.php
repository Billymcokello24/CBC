<x-mail::message>
# New Demo Request

A new school has requested a demo of the CBC Learning Platform.

**School Details:**
- **School Name:** {{ $data['school_name'] }}
- **Contact Person:** {{ $data['contact_person'] }}
- **Email:** {{ $data['email'] }}
- **Phone:** {{ $data['phone'] }}

**Message:**
{{ $data['message'] ?? 'No message provided.' }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
