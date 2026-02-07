<x-mail::message>
# Your Plan Change is Scheduled

Hi {{ $user->name }},

Your plan change has been scheduled successfully.

**Plan Change Details:**

- **Current Plan:** {{ ucfirst($subscription->plan) }}
- **New Plan:** {{ ucfirst($newPlan) }}
- **Effective Date:** {{ $subscription->current_period_end->format('F j, Y') }}

You'll continue to enjoy your **{{ ucfirst($subscription->plan) }}** benefits until {{ $subscription->current_period_end->format('F j, Y') }}.

---

**What happens next?**

On {{ $subscription->current_period_end->format('F j, Y') }}:
- Your plan will automatically change to {{ ucfirst($newPlan) }}
- Your new monthly credits will be {{ $newPlanDetails['credits_per_month'] ?? 0 }}
- You'll be billed at the {{ ucfirst($newPlan) }} rate

---

**Changed your mind?**

You can cancel this scheduled change anytime before {{ $subscription->current_period_end->format('F j, Y') }} from your billing settings.

<x-mail::button :url="config('app.url') . '/pricing'">
Manage Subscription
</x-mail::button>

Need help? Contact us at info@stylely.ai.

Thanks for being a Stylely member!<br>
The {{ config('app.name') }} Team
</x-mail::message>
