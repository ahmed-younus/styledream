<x-mail::message>
# Welcome to Stylely {{ ucfirst($subscription->plan) }}!

Hi {{ $user->name }},

Thank you for subscribing to Stylely! Your subscription is now active.

<x-mail::panel>
**Subscription Details:**

- **Plan:** {{ ucfirst($subscription->plan) }}
- **Monthly Credits:** {{ $planDetails['credits_per_month'] ?? 'Unlimited' }}
- **Status:** Active
- **Next Billing Date:** {{ $subscription->current_period_end->format('F j, Y') }}
</x-mail::panel>

@if(isset($planDetails['features']) && count($planDetails['features']) > 0)
**Your Plan Benefits:**
@foreach($planDetails['features'] as $feature)
- {{ $feature }}
@endforeach
@endif

<x-mail::button :url="config('app.url') . '/studio'">
Start Trying On Outfits
</x-mail::button>

---

**Manage Your Subscription**

You can manage your subscription, update payment methods, or cancel anytime from your account settings or by visiting your [billing portal]({{ config('app.url') }}/billing).

Need help? Contact us at info@stylely.ai.

Thanks for being a Stylely member!<br>
The {{ config('app.name') }} Team
</x-mail::message>
