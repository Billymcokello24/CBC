<x-mail::message>
# New Demo Request

You have received a new request for a system demonstration.

**School Name:** {{ $details['school_name'] }}  
**Contact Person:** {{ $details['contact_person'] }}  
**Email:** {{ $details['email'] }}  
**Phone:** {{ $details['phone'] }}  

**Message:**  
{{ $details['message'] ?? 'No additional message provided.' }}

<x-mail::button :url="config('app.url')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
