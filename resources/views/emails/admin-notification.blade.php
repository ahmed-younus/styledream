<x-mail::message>
# Admin Notification

This is a copy of an email sent to a user.

**Email Type:** {{ ucfirst(str_replace('-', ' ', $templateName)) }}

@if(isset($data['user_name']))
**User:** {{ $data['user_name'] }}
@endif

@if(isset($data['user_email']))
**Email:** {{ $data['user_email'] }}
@endif

@if(isset($data['plan_name']))
**Plan:** {{ $data['plan_name'] }}
@endif

@if(isset($data['credits']))
**Credits:** {{ $data['credits'] }}
@endif

@if(isset($data['amount']))
**Amount:** {{ $data['currency'] ?? 'USD' }}{{ number_format(($data['amount'] ?? 0) / 100, 2) }}
@endif

---

This notification was sent because admin copy is enabled for this email type. You can disable this in the [Admin Panel]({{ config('app.url') }}/management/settings).

Thanks,<br>
{{ config('app.name') }} System
</x-mail::message>
