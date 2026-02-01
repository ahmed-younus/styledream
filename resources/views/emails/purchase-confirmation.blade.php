<x-mail::message>
# Thank You for Your Purchase!

Hi {{ $user->name }},

Your credit purchase has been confirmed and the credits have been added to your account.

**Order Details:**

- **Pack:** {{ ucwords(str_replace('_', ' ', $purchase->pack)) }}
- **Credits:** {{ $purchase->credits }}
- **Amount:** {{ strtoupper($purchase->currency) }} {{ number_format($purchase->amount / 100, 2) }}
- **Date:** {{ $purchase->created_at->format('F j, Y \a\t g:i A') }}

Your new credit balance is ready to use!

<x-mail::button :url="config('app.url') . '/studio'">
Start Trying On
</x-mail::button>

---

**Transaction ID:** {{ $purchase->stripe_session_id }}

If you have any questions about your purchase, please contact us at info@stylely.ai.

Thanks for choosing Stylely!<br>
The {{ config('app.name') }} Team
</x-mail::message>
