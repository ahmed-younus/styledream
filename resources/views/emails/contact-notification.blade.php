<x-mail::message>
# New Contact Inquiry

You have received a new contact inquiry from the website.

**From:** {{ $inquiry->name }}
**Email:** {{ $inquiry->email }}
**Subject:** {{ $inquiry->subject }}

---

**Message:**

{{ $inquiry->message }}

---

**Submitted at:** {{ $inquiry->created_at->format('F j, Y \a\t g:i A') }}

<x-mail::button :url="config('app.url')">
View Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
