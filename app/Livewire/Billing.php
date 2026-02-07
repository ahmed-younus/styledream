<?php

namespace App\Livewire;

use App\Models\CreditPurchase;
use App\Services\CurrencyService;
use App\Services\PricingService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Stripe\StripeClient;

#[Layout('layouts.app')]
class Billing extends Component
{
    public bool $hasPaymentMethod = false;
    public ?array $paymentMethod = null;
    public ?string $currentPlan = null;
    public ?array $planDetails = null;
    public ?string $error = null;
    public ?string $subscriptionStatus = null;
    public ?string $renewDate = null;

    public function mount()
    {
        $user = auth()->user();

        // Load current plan
        $subscription = $user->activeSubscription();
        if (!$subscription) {
            $subscription = $user->subscriptions()
                ->where('status', 'canceling')
                ->where('current_period_end', '>', now())
                ->orderBy('current_period_end', 'desc')
                ->first();
        }

        if ($subscription) {
            $this->currentPlan = $subscription->plan;
            $this->planDetails = PricingService::getPlan($subscription->plan);
            $this->subscriptionStatus = $subscription->status;
            $this->renewDate = $subscription->current_period_end->format('F j, Y');
        } else {
            $this->currentPlan = 'free';
            $this->planDetails = PricingService::getPlan('free');
        }

        // Load payment method
        $this->loadPaymentMethod();
    }

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
            \Log::error('Failed to load payment method on billing page', ['error' => $e->getMessage()]);
        }
    }

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
        $user = auth()->user();

        $paymentHistory = CreditPurchase::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('livewire.billing', [
            'paymentHistory' => $paymentHistory,
        ])->title(__('billing.title'));
    }
}
