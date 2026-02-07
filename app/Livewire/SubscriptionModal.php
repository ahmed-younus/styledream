<?php

namespace App\Livewire;

use App\Mail\SubscriptionCancellationEmail;
use App\Mail\SubscriptionDowngradeEmail;
use App\Mail\SubscriptionReactivatedEmail;
use App\Services\CurrencyService;
use App\Services\PricingService;
use Illuminate\Support\Facades\Mail;
use Livewire\Component;
use Stripe\StripeClient;

class SubscriptionModal extends Component
{
    public bool $showModal = false;
    public ?string $targetPlan = null;
    public ?string $mode = null; // 'trial', 'new', 'upgrade', 'downgrade', 'cancel', 'manage'
    public ?string $currentPlan = null;
    public bool $hasPaymentMethod = false;
    public ?array $paymentMethod = null;
    public ?array $proration = null;
    public ?string $effectiveDate = null;
    public ?string $targetPlanPrice = null;
    public ?string $currentPlanPrice = null;
    public bool $processing = false;
    public ?string $error = null;
    public ?string $currency = null;
    public ?string $currencySymbol = null;

    // Plan details for display
    public ?array $planDetails = null;

    // Credit pack properties
    public ?string $creditPack = null;
    public ?array $creditPackDetails = null;
    public ?string $creditPackPrice = null;

    // Manage subscription properties
    public ?string $billingCycleStart = null;
    public ?string $billingCycleEnd = null;
    public ?int $subscriptionCredits = null;
    public bool $isCanceling = false;

    protected $listeners = ['openSubscriptionModal', 'openTrialModal', 'openManageModal', 'openCreditPackModal', 'closeModal'];

    public function mount()
    {
        $this->currency = CurrencyService::getUserCurrency();
        $this->currencySymbol = CurrencyService::getSymbol($this->currency);
    }

    /**
     * Open modal for trial activation (card required, no charge)
     */
    public function openTrialModal()
    {
        $this->reset(['error', 'proration', 'paymentMethod']);
        $this->mode = 'trial';
        $this->targetPlan = 'trial';
        $this->currentPlan = 'free';
        $this->showModal = true;
    }

    /**
     * Open modal for managing current subscription
     */
    public function openManageModal()
    {
        $this->reset(['error', 'proration', 'isCanceling']);
        $this->mode = 'manage';

        $user = auth()->user();
        $subscription = $user?->activeSubscription();

        if (!$subscription) {
            $subscription = $user?->subscriptions()
                ->where('status', 'canceling')
                ->where('current_period_end', '>', now())
                ->orderBy('current_period_end', 'desc')
                ->first();
        }

        if (!$subscription) {
            return;
        }

        $this->currentPlan = $subscription->plan;
        $this->targetPlan = $subscription->plan;
        $this->effectiveDate = $subscription->current_period_end->format('F j, Y');
        $this->billingCycleStart = $subscription->current_period_start->format('M j, Y');
        $this->billingCycleEnd = $subscription->current_period_end->format('M j, Y');
        $this->subscriptionCredits = $user->getSubscriptionCredits();
        $this->isCanceling = $subscription->status === 'canceling';

        // Load payment method
        $this->loadPaymentMethod();

        // Load plan details
        $this->planDetails = PricingService::getPlan($subscription->plan);
        $this->currentPlanPrice = $this->formatPrice(PricingService::getPlanPrice($subscription->plan, $this->currency));

        $this->showModal = true;
    }

    /**
     * Open modal for credit pack purchase
     */
    public function openCreditPackModal(string $pack)
    {
        $this->reset(['error', 'proration', 'paymentMethod', 'creditPack', 'creditPackDetails', 'creditPackPrice']);
        $this->mode = 'credit_pack';
        $this->creditPack = $pack;
        $this->creditPackDetails = PricingService::getCreditPack($pack);

        if (!$this->creditPackDetails) {
            $this->error = 'Invalid credit pack';
            return;
        }

        // Get currency-specific price
        $price = PricingService::getPackPrice($pack, $this->currency);
        $this->creditPackPrice = $this->formatPrice($price);

        $this->loadPaymentMethod();
        $this->showModal = true;
    }

    /**
     * Open modal for subscription action
     */
    public function openSubscriptionModal(string $plan)
    {
        $this->reset(['error', 'proration', 'paymentMethod']);
        $this->targetPlan = $plan;
        $user = auth()->user();

        // Get active subscription (including canceling)
        $subscription = $user?->activeSubscription();
        if (!$subscription) {
            $subscription = $user?->subscriptions()
                ->where('status', 'canceling')
                ->where('current_period_end', '>', now())
                ->orderBy('current_period_end', 'desc')
                ->first();
        }

        $planTiers = ['free' => 0, 'pro' => 1, 'premium' => 2];

        if (!$subscription) {
            // NEW SUBSCRIPTION (Free -> Pro/Premium)
            $this->mode = 'new';
            $this->currentPlan = 'free';
        } else {
            $this->currentPlan = $subscription->plan;
            $currentTier = $planTiers[$subscription->plan] ?? 0;
            $targetTier = $planTiers[$plan] ?? 0;
            $this->effectiveDate = $subscription->current_period_end->format('F j, Y');

            if ($plan === 'free') {
                // CANCEL to Free
                $this->mode = 'cancel';
            } elseif ($targetTier > $currentTier) {
                // UPGRADE
                $this->mode = 'upgrade';
                $this->loadPaymentMethod();
                $this->calculateProration($subscription);
            } else {
                // DOWNGRADE
                $this->mode = 'downgrade';
            }
        }

        // Load plan details
        $this->planDetails = PricingService::getPlan($plan);
        $this->targetPlanPrice = $this->formatPrice(PricingService::getPlanPrice($plan, $this->currency));
        if ($this->currentPlan && $this->currentPlan !== 'free') {
            $this->currentPlanPrice = $this->formatPrice(PricingService::getPlanPrice($this->currentPlan, $this->currency));
        }

        $this->showModal = true;
    }

    /**
     * Close the modal
     */
    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['error', 'processing']);
    }

    /**
     * Load user's saved payment method from Stripe
     */
    protected function loadPaymentMethod(): void
    {
        $user = auth()->user();
        if (!$user || !$user->stripe_customer_id) {
            return;
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $customer = $stripe->customers->retrieve($user->stripe_customer_id, [
                'expand' => ['invoice_settings.default_payment_method'],
            ]);

            $pm = $customer->invoice_settings->default_payment_method ?? null;
            if ($pm && isset($pm->card)) {
                $this->hasPaymentMethod = true;
                $this->paymentMethod = [
                    'brand' => ucfirst($pm->card->brand),
                    'last4' => $pm->card->last4,
                    'exp_month' => str_pad($pm->card->exp_month, 2, '0', STR_PAD_LEFT),
                    'exp_year' => $pm->card->exp_year,
                ];
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load payment method', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Calculate proration for upgrade
     */
    protected function calculateProration($subscription): void
    {
        try {
            $oldPrice = PricingService::getPlanPrice($subscription->plan, $this->currency);
            $newPrice = PricingService::getPlanPrice($this->targetPlan, $this->currency);

            // Calculate based on remaining days
            $now = now();
            $periodEnd = $subscription->current_period_end;
            $periodStart = $subscription->current_period_start;

            $totalDays = $periodStart->diffInDays($periodEnd);
            $remainingDays = max(0, $now->diffInDays($periodEnd, false));

            // Unused credit from current plan
            $unusedCredit = $totalDays > 0 ? (int) round(($remainingDays / $totalDays) * $oldPrice) : 0;

            // Prorated new plan cost
            $proratedNewCost = $totalDays > 0 ? (int) round(($remainingDays / $totalDays) * $newPrice) : $newPrice;

            // Amount to charge
            $upgradeAmount = max(0, $proratedNewCost - $unusedCredit);

            $this->proration = [
                'new_plan_price' => $this->formatPrice($newPrice),
                'prorated_credit' => $this->formatPrice($unusedCredit),
                'total_due' => $this->formatPrice($upgradeAmount),
                'raw_amount' => $upgradeAmount,
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to calculate proration', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Format price for display
     */
    protected function formatPrice(int $cents): string
    {
        return $this->currencySymbol . number_format($cents / 100, 2);
    }

    /**
     * Process downgrade action
     */
    public function processDowngrade()
    {
        $this->processing = true;
        $this->error = null;

        try {
            $user = auth()->user();
            $subscription = $user->activeSubscription();

            // Also check for canceling subscriptions
            if (!$subscription) {
                $subscription = $user->subscriptions()
                    ->where('status', 'canceling')
                    ->where('current_period_end', '>', now())
                    ->orderBy('current_period_end', 'desc')
                    ->first();
            }

            if (!$subscription) {
                throw new \Exception('No active subscription found');
            }

            // If subscription is canceling, reactivate it first on Stripe
            if ($subscription->status === 'canceling' && $subscription->stripe_subscription_id) {
                $stripe = new StripeClient(config('services.stripe.secret'));
                $stripe->subscriptions->update($subscription->stripe_subscription_id, [
                    'cancel_at_period_end' => false,
                ]);

                // Update local status back to active
                $subscription->update([
                    'status' => 'active',
                    'canceled_at' => null,
                ]);

                \Log::info('Reactivated canceling subscription for downgrade', [
                    'user_id' => $user->id,
                    'subscription_id' => $subscription->id,
                ]);
            }

            // Update local database with scheduled downgrade
            $subscription->update([
                'scheduled_plan' => $this->targetPlan,
                'scheduled_change_at' => $subscription->current_period_end,
            ]);

            // Send downgrade email
            try {
                Mail::to($user->email)->send(new SubscriptionDowngradeEmail($user, $subscription, $this->targetPlan));
            } catch (\Exception $e) {
                \Log::error('Failed to send downgrade email', ['error' => $e->getMessage()]);
            }

            $this->showModal = false;
            $this->dispatch('subscription-updated');

            // Set flash message for top of page
            session()->flash('success', __('pricing.downgrade_scheduled', [
                'plan' => ucfirst($this->targetPlan),
                'date' => $subscription->current_period_end->format('M d, Y'),
            ]));

            return $this->redirect(route('pricing'), navigate: true);

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            \Log::error('Downgrade failed', ['error' => $e->getMessage()]);
        } finally {
            $this->processing = false;
        }
    }

    /**
     * Process cancel to free action
     */
    public function processCancel()
    {
        $this->processing = true;
        $this->error = null;

        try {
            $user = auth()->user();
            $subscription = $user->activeSubscription();

            if (!$subscription || !$subscription->stripe_subscription_id) {
                throw new \Exception('No active subscription found');
            }

            $stripe = new StripeClient(config('services.stripe.secret'));

            // Cancel at period end
            $stripe->subscriptions->update($subscription->stripe_subscription_id, [
                'cancel_at_period_end' => true,
            ]);

            // Update local database
            $subscription->update([
                'status' => 'canceling',
                'canceled_at' => now(),
                'scheduled_plan' => null,
                'scheduled_change_at' => null,
            ]);

            // Send cancellation email
            try {
                Mail::to($user->email)->send(new SubscriptionCancellationEmail($user, $subscription));
            } catch (\Exception $e) {
                \Log::error('Failed to send cancellation email', ['error' => $e->getMessage()]);
            }

            $this->showModal = false;
            $this->dispatch('subscription-updated');

            // Set flash message for top of page
            session()->flash('success', __('pricing.subscription_canceled', [
                'date' => $subscription->current_period_end->format('M d, Y'),
            ]));

            return $this->redirect(route('pricing'), navigate: true);

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            \Log::error('Cancel failed', ['error' => $e->getMessage()]);
        } finally {
            $this->processing = false;
        }
    }

    /**
     * Reactivate a canceling subscription
     */
    public function processReactivate()
    {
        $this->processing = true;
        $this->error = null;

        try {
            $user = auth()->user();
            $subscription = $user->subscriptions()
                ->where('status', 'canceling')
                ->where('current_period_end', '>', now())
                ->orderBy('current_period_end', 'desc')
                ->first();

            if (!$subscription || !$subscription->stripe_subscription_id) {
                throw new \Exception('No canceling subscription found');
            }

            $stripe = new StripeClient(config('services.stripe.secret'));

            // Reactivate by removing cancel_at_period_end
            $stripe->subscriptions->update($subscription->stripe_subscription_id, [
                'cancel_at_period_end' => false,
            ]);

            // Update local database
            $subscription->update([
                'status' => 'active',
                'canceled_at' => null,
            ]);

            \Log::info('Subscription reactivated', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
            ]);

            // Send reactivation email
            try {
                Mail::to($user->email)->send(new SubscriptionReactivatedEmail($user, $subscription));
            } catch (\Exception $e) {
                \Log::error('Failed to send reactivation email', ['error' => $e->getMessage()]);
            }

            $this->showModal = false;
            $this->dispatch('subscription-updated');

            // Set flash message for top of page
            session()->flash('success', __('pricing.subscription_reactivated'));

            return $this->redirect(route('pricing'), navigate: true);

        } catch (\Exception $e) {
            $this->error = $e->getMessage();
            \Log::error('Reactivate failed', ['error' => $e->getMessage()]);
        } finally {
            $this->processing = false;
        }
    }

    /**
     * Remove saved payment method from Stripe
     */
    public function removePaymentMethod()
    {
        $user = auth()->user();
        if (!$user || !$user->stripe_customer_id) {
            return;
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            $paymentMethods = $stripe->paymentMethods->all([
                'customer' => $user->stripe_customer_id,
                'type' => 'card',
            ]);

            foreach ($paymentMethods->data as $pm) {
                $stripe->paymentMethods->detach($pm->id);
            }

            $stripe->customers->update($user->stripe_customer_id, [
                'invoice_settings' => ['default_payment_method' => null],
            ]);

            $this->hasPaymentMethod = false;
            $this->paymentMethod = null;

        } catch (\Exception $e) {
            $this->error = 'Failed to remove card. Please try again.';
            \Log::error('Failed to remove payment method', ['error' => $e->getMessage()]);
        }
    }

    public function render()
    {
        return view('livewire.subscription-modal');
    }
}
