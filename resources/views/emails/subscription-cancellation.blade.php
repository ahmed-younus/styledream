<x-mail::message>
# Your Subscription Cancellation

Hi {{ $user->name }},

We're sorry to see you go! Your **{{ ucfirst($subscription->plan) }}** subscription has been scheduled for cancellation.

**Important Details:**

- **Current Plan:** {{ ucfirst($subscription->plan) }}
- **Access Until:** {{ $subscription->current_period_end->format('F j, Y') }}
- **Status:** Canceling

You'll continue to have full access to your {{ ucfirst($subscription->plan) }} benefits until **{{ $subscription->current_period_end->format('F j, Y') }}**.

After that date:
- You'll be moved to the Free plan
- Your subscription credits will reset
- You can still use any purchased credits

---

**Changed your mind?**

You can reactivate your subscription anytime before {{ $subscription->current_period_end->format('F j, Y') }} to keep your benefits.

<x-mail::button :url="config('app.url') . '/pricing'">
Reactivate Subscription
</x-mail::button>

---

We'd love to hear your feedback! If there's anything we can do better, please let us know at info@stylely.ai.

Thank you for being a Stylely member!<br>
The {{ config('app.name') }} Team
</x-mail::message>
