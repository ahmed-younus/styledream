<x-mail::message>
# Welcome Back!

Hi {{ $user->name }},

Great news! Your **{{ ucfirst($subscription->plan) }}** subscription has been reactivated.

**Subscription Details:**

- **Plan:** {{ ucfirst($subscription->plan) }}
- **Monthly Credits:** {{ $planDetails['credits_per_month'] ?? 'Unlimited' }}
- **Status:** Active
- **Next Billing Date:** {{ $subscription->current_period_end->format('F j, Y') }}

Your subscription will now continue as normal, and you'll retain all your {{ ucfirst($subscription->plan) }} benefits.

<x-mail::button :url="config('app.url') . '/studio'">
Continue Creating
</x-mail::button>

---

**Manage Your Subscription**

You can manage your subscription, update payment methods, or view your billing history anytime from your [billing portal]({{ config('app.url') }}/billing).

Need help? Contact us at info@stylely.ai.

Thanks for staying with Stylely!<br>
The {{ config('app.name') }} Team
</x-mail::message>
