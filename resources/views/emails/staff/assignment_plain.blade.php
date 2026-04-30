{{ $title }}
------------------------------------------------------------
Hello {{ $staff->first_name }},

{{ $messageBody }}

@if($actionUrl)
{{ $actionText }}: {{ $actionUrl }}
@endif

If you have any questions regarding this update, please contact the institutional administration.

Best Regards,
The {{ config('app.name') }} Team

This is an automated notification from the {{ config('app.name') }} management system.
Institutional Administration Complex, Kenya.
