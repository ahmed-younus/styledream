<x-mail::message>
# New Brand Registration

A new brand has registered on StyleDream.

**Brand Details:**

- **Brand Name:** {{ $registration->brand_name }}
- **Website:** {{ $registration->website }}
- **Contact Email:** {{ $registration->contact_email }}
@if($registration->contact_name)
- **Contact Name:** {{ $registration->contact_name }}
@endif
@if($registration->phone)
- **Phone:** {{ $registration->phone }}
@endif

@if($registration->message)
**Message:**
{{ $registration->message }}
@endif

---

**Submitted at:** {{ $registration->created_at->format('F j, Y \a\t g:i A') }}

<x-mail::button :url="config('app.url')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
