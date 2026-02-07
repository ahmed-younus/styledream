<?php

namespace App\Livewire;

use App\Models\Subscription;
use App\Services\CurrencyService;
use App\Services\PricingService;
use Livewire\Component;
use Stripe\StripeClient;

class Pricing extends Component
{
    public string $currency = 'usd';

    public function mount()
    {
        $this->currency = CurrencyService::getUserCurrency();

        // Sync subscription status with Stripe on page load
        $this->syncSubscriptionWithStripe();

        // Handle flash messages from query parameters (for JS-based actions)
        if (request()->query('trial_success')) {
            session()->flash('success', __('pricing.trial_activated'));
        } elseif (request()->query('subscription_success')) {
            session()->flash('success', __('pricing.subscription_activated'));
        } elseif (request()->query('upgrade_success')) {
            session()->flash('success', __('pricing.subscription_updated'));
        } elseif (request()->query('credits_success')) {
            session()->flash('success', __('pricing.credits_purchase_success'));
        }
    }

    /**
     * Sync user's subscription status with Stripe
     * This ensures local status matches Stripe (especially after portal visits)
     */
    protected function syncSubscriptionWithStripe(): void
    {
        $user = auth()->user();

        if (!$user || !$user->stripe_customer_id) {
            return;
        }

        // Only sync if user has a subscription
        $localSub = $user->subscriptions()
            ->whereIn('status', ['active', 'canceling'])
            ->first();

        if (!$localSub || !$localSub->stripe_subscription_id) {
            return;
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $stripeSub = $stripe->subscriptions->retrieve($localSub->stripe_subscription_id);

            // Determine correct local status
            $localStatus = $stripeSub->status;
            $cancelAtPeriodEnd = $stripeSub->cancel_at_period_end ?? false;

            if ($cancelAtPeriodEnd && $stripeSub->status === 'active') {
                $localStatus = 'canceling';
            }

            // Update if status changed
            $needsSave = false;

            if ($localSub->status !== $localStatus) {
                $localSub->status = $localStatus;
                $localSub->canceled_at = $stripeSub->canceled_at
                    ? \Carbon\Carbon::createFromTimestamp($stripeSub->canceled_at)
                    : ($cancelAtPeriodEnd ? now() : null);
                $needsSave = true;
            }

            // If subscription is canceling, clear any scheduled downgrade
            // (full cancellation takes precedence over scheduled plan change)
            if ($localStatus === 'canceling' && $localSub->scheduled_plan) {
                $localSub->scheduled_plan = null;
                $localSub->scheduled_change_at = null;
                $needsSave = true;
            }

            if ($needsSave) {
                $localSub->save();
            }

        } catch (\Exception $e) {
            \Log::error('Failed to sync subscription', ['error' => $e->getMessage()]);
        }
    }

    public function setCurrency(string $currency)
    {
        if (array_key_exists($currency, CurrencyService::CURRENCIES)) {
            $this->currency = $currency;
            CurrencyService::setUserCurrency($currency);
        }
    }

    public function getCurrentPlan(): string
    {
        $user = auth()->user();
        if (!$user) {
            return 'free';
        }

        // Check active subscription first
        $subscription = $user->activeSubscription();
        if ($subscription) {
            return $subscription->plan;
        }

        // Check for canceling subscription (user still has access to this plan until period ends)
        $cancelingSubscription = $user->subscriptions()
            ->where('status', 'canceling')
            ->where('current_period_end', '>', now())
            ->orderBy('current_period_end', 'desc')
            ->first();

        if ($cancelingSubscription) {
            return $cancelingSubscription->plan;
        }

        return 'free';
    }

    public function getActiveSubscription(): ?\App\Models\Subscription
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        // Get active subscription first
        $subscription = $user->activeSubscription();

        // If no active subscription, check for canceling subscription (user will still have access until period end)
        if (!$subscription) {
            $subscription = $user->subscriptions()
                ->where('status', 'canceling')
                ->orderBy('current_period_end', 'desc')
                ->first();
        }

        return $subscription;
    }

    public function render()
    {
        $subscription = $this->getActiveSubscription();

        return view('livewire.pricing', [
            'creditPacks' => PricingService::getCreditPacksForCurrency($this->currency),
            'plans' => PricingService::getPlansForCurrency($this->currency),
            'currency' => $this->currency,
            'currencySymbol' => CurrencyService::getSymbol($this->currency),
            'currencies' => CurrencyService::getSupportedCurrencies(),
            'currentPlan' => $this->getCurrentPlan(),
            'subscription' => $subscription,
            'subscriptionEndDate' => $subscription?->current_period_end?->format('F j, Y'),
        ])->layout('layouts.app', ['title' => 'Pricing']);
    }
}
