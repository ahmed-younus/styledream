<?php

namespace App\Http\Controllers;

use App\Models\CreditPurchase;
use App\Models\CreditTransaction;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Customer;
use Stripe\BillingPortal\Session as BillingSession;

class PaymentController extends Controller
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Create a Stripe Checkout session for credit pack purchase
     * Supports: Cards, Google Pay, Apple Pay, Link
     */
    public function createCheckout(Request $request)
    {
        $request->validate([
            'pack' => 'required|string|in:small,medium,large,mega',
        ]);

        $pack = config("credits.packs.{$request->pack}");
        if (!$pack) {
            abort(404, 'Credit pack not found');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        // Get or create Stripe customer
        $user = auth()->user();
        $customerId = $this->getOrCreateStripeCustomer($user);

        $session = Session::create([
            'customer' => $customerId,
            // automatic_payment_methods enables: Cards, Google Pay, Apple Pay, Link, etc.
            'payment_method_types' => null,
            'payment_method_options' => [
                'card' => [
                    'setup_future_usage' => 'off_session',
                ],
            ],
            'line_items' => [[
                'price_data' => [
                    'currency' => $pack['currency'],
                    'product_data' => [
                        'name' => $pack['label'],
                        'description' => "StyleDream Credit Pack - {$pack['credits']} credits for virtual try-on",
                        'images' => [config('app.url') . '/images/logo.png'],
                    ],
                    'unit_amount' => $pack['price'],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('pricing'),
            'metadata' => [
                'user_id' => $user->id,
                'type' => 'credit_pack',
                'pack' => $request->pack,
                'credits' => $pack['credits'],
            ],
            'allow_promotion_codes' => true,
        ]);

        return redirect($session->url);
    }

    /**
     * Create a Stripe Checkout session for subscription
     * Supports: Cards, Google Pay, Apple Pay, Link
     * Handles upgrades by cancelling old subscription
     */
    public function createSubscription(Request $request)
    {
        $request->validate([
            'plan' => 'required|string|in:pro,premium',
        ]);

        $plans = config('subscriptions.plans');
        $plan = $plans[$request->plan] ?? null;

        if (!$plan) {
            abort(404, 'Plan not found');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $user = auth()->user();
        $customerId = $this->getOrCreateStripeCustomer($user);

        // Get old plan before cancelling (for prorated credits calculation)
        $oldPlan = $user->subscription_tier ?? 'free';
        $oldSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if ($oldSubscription) {
            $oldPlan = $oldSubscription->plan;
        }

        // Check for existing active subscriptions on Stripe and cancel them
        // This prevents duplicate subscriptions
        $this->cancelExistingStripeSubscriptions($customerId);

        // Also clean up our local subscription records
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'canceled', 'canceled_at' => now()]);

        $session = Session::create([
            'customer' => $customerId,
            'payment_method_types' => null, // Enables all available methods
            'line_items' => [[
                'price' => $plan['stripe_price_id'],
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => route('subscription.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('pricing'),
            'metadata' => [
                'user_id' => $user->id,
                'type' => 'subscription',
                'plan' => $request->plan,
                'old_plan' => $oldPlan, // Store old plan for prorated credits
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => $request->plan,
                    'old_plan' => $oldPlan,
                ],
            ],
            'allow_promotion_codes' => true,
        ]);

        return redirect($session->url);
    }

    /**
     * Cancel all existing Stripe subscriptions for a customer
     * Called before creating a new subscription to prevent duplicates
     */
    protected function cancelExistingStripeSubscriptions(string $customerId): void
    {
        try {
            $subscriptions = \Stripe\Subscription::all([
                'customer' => $customerId,
                'status' => 'active',
            ]);

            foreach ($subscriptions->data as $subscription) {
                $subscription->cancel();
                \Log::info('Cancelled existing subscription before new purchase', [
                    'subscription_id' => $subscription->id,
                    'customer_id' => $customerId,
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to cancel existing subscriptions', [
                'error' => $e->getMessage(),
                'customer_id' => $customerId,
            ]);
        }
    }

    /**
     * Handle successful credit pack payment return
     */
    public function success(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('pricing')->with('error', __('pricing.payment_failed'));
        }

        // Check if already processed
        $existingPurchase = CreditPurchase::where('stripe_session_id', $sessionId)->first();
        if ($existingPurchase) {
            return redirect()->route('pricing')->with('success', __('pricing.credits_added', ['credits' => $existingPurchase->credits]));
        }

        // Verify session with Stripe
        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);

            if ($session->payment_status === 'paid') {
                $this->fulfillCreditOrder($session);
                $credits = $session->metadata->credits ?? 0;
                return redirect()->route('pricing')->with('success', __('pricing.credits_added', ['credits' => $credits]));
            }
        } catch (\Exception $e) {
            \Log::error('Stripe session retrieval failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('pricing')->with('error', __('pricing.payment_processing'));
    }

    /**
     * Handle successful subscription return
     */
    public function subscriptionSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('pricing')->with('error', __('pricing.payment_failed'));
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve([
                'id' => $sessionId,
                'expand' => ['subscription'],
            ]);

            if ($session->status === 'complete' && $session->subscription) {
                // Check if subscription already exists in our DB
                $existingSub = Subscription::where('stripe_subscription_id', $session->subscription->id)->first();

                if (!$existingSub) {
                    // Get plan from metadata or price ID
                    $plan = $session->metadata->plan ?? $this->getPlanFromPriceId(
                        $session->subscription->items->data[0]->price->id ?? null
                    );

                    // Create subscription in our database
                    $subscription = Subscription::create([
                        'user_id' => auth()->id(),
                        'plan' => $plan,
                        'status' => $session->subscription->status,
                        'stripe_subscription_id' => $session->subscription->id,
                        'stripe_customer_id' => $session->customer,
                        'current_period_start' => \Carbon\Carbon::createFromTimestamp($session->subscription->current_period_start),
                        'current_period_end' => \Carbon\Carbon::createFromTimestamp($session->subscription->current_period_end),
                    ]);

                    // Update user's subscription tier
                    auth()->user()->update([
                        'subscription_tier' => $plan,
                        'subscription_ends_at' => $subscription->current_period_end,
                    ]);

                    // Add prorated credits (only difference if upgrading)
                    if ($subscription->status === 'active') {
                        $oldPlan = $session->metadata->old_plan ?? 'free';
                        $this->addProratedSubscriptionCredits($subscription, $oldPlan);
                    }

                    \Log::info('Subscription created via success callback', [
                        'user_id' => auth()->id(),
                        'plan' => $plan,
                        'subscription_id' => $session->subscription->id,
                    ]);
                }

                return redirect()->route('pricing')->with('success', __('pricing.subscription_activated'));
            }
        } catch (\Exception $e) {
            \Log::error('Stripe subscription session retrieval failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        }

        return redirect()->route('pricing')->with('info', __('pricing.subscription_processing'));
    }

    /**
     * Redirect to Stripe Customer Portal for subscription management
     */
    public function billingPortal(Request $request)
    {
        $user = auth()->user();

        if (!$user->stripe_customer_id) {
            return redirect()->route('pricing')->with('error', __('pricing.no_subscription'));
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = BillingSession::create([
            'customer' => $user->stripe_customer_id,
            'return_url' => route('pricing'),
        ]);

        return redirect($session->url);
    }

    /**
     * Handle Stripe webhook events
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $sig = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig, $webhookSecret);
        } catch (\Exception $e) {
            \Log::error('Stripe webhook signature verification failed', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            // Credit pack payments
            case 'checkout.session.completed':
                $session = $event->data->object;
                if ($session->mode === 'payment' && $session->payment_status === 'paid') {
                    $this->fulfillCreditOrder($session);
                }
                break;

            case 'checkout.session.async_payment_succeeded':
                $session = $event->data->object;
                if ($session->mode === 'payment') {
                    $this->fulfillCreditOrder($session);
                }
                break;

            case 'checkout.session.async_payment_failed':
                $session = $event->data->object;
                \Log::warning('Async payment failed', ['session_id' => $session->id]);
                break;

            // Subscription events
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $subscription = $event->data->object;
                $this->handleSubscriptionUpdate($subscription);
                break;

            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                $this->handleSubscriptionCancelled($subscription);
                break;

            case 'invoice.paid':
                $invoice = $event->data->object;
                if ($invoice->subscription) {
                    $this->handleSubscriptionRenewal($invoice);
                }
                break;

            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                if ($invoice->subscription) {
                    $this->handleSubscriptionPaymentFailed($invoice);
                }
                break;
        }

        return response('OK');
    }

    /**
     * Get or create a Stripe customer for the user
     */
    protected function getOrCreateStripeCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            return $user->stripe_customer_id;
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $customer = Customer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
            ],
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    /**
     * Fulfill the credit pack order by adding credits to user account
     */
    protected function fulfillCreditOrder($session)
    {
        $userId = $session->metadata->user_id ?? null;
        $credits = (int) ($session->metadata->credits ?? 0);
        $pack = $session->metadata->pack ?? 'unknown';

        if (!$userId || !$credits) {
            \Log::error('Invalid session metadata for fulfillment', [
                'session_id' => $session->id,
                'user_id' => $userId,
                'credits' => $credits,
            ]);
            return;
        }

        // Check if already fulfilled
        if (CreditPurchase::where('stripe_session_id', $session->id)->exists()) {
            \Log::info('Order already fulfilled', ['session_id' => $session->id]);
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            \Log::error('User not found for fulfillment', ['user_id' => $userId]);
            return;
        }

        // Record purchase
        CreditPurchase::create([
            'user_id' => $userId,
            'pack' => $pack,
            'credits' => $credits,
            'amount' => $session->amount_total,
            'currency' => $session->currency,
            'stripe_session_id' => $session->id,
            'stripe_payment_intent' => $session->payment_intent,
        ]);

        // Add credits to user account
        $this->creditService->addCredits(
            $user,
            $credits,
            CreditTransaction::TYPE_PURCHASE,
            "Credit pack purchase: {$pack}",
            $session->id
        );

        \Log::info('Credits added successfully', [
            'user_id' => $userId,
            'credits' => $credits,
            'session_id' => $session->id,
        ]);
    }

    /**
     * Handle subscription creation/update
     */
    protected function handleSubscriptionUpdate($stripeSubscription)
    {
        $userId = $stripeSubscription->metadata->user_id ?? null;
        $plan = $stripeSubscription->metadata->plan ?? null;

        if (!$userId) {
            // Try to find user by customer ID
            $user = User::where('stripe_customer_id', $stripeSubscription->customer)->first();
            if ($user) {
                $userId = $user->id;
            }
        }

        if (!$userId) {
            \Log::error('User not found for subscription', ['subscription_id' => $stripeSubscription->id]);
            return;
        }

        $subscription = Subscription::updateOrCreate(
            ['stripe_subscription_id' => $stripeSubscription->id],
            [
                'user_id' => $userId,
                'plan' => $plan ?? $this->getPlanFromPriceId($stripeSubscription->items->data[0]->price->id ?? null),
                'status' => $stripeSubscription->status,
                'stripe_customer_id' => $stripeSubscription->customer,
                'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                'canceled_at' => $stripeSubscription->canceled_at
                    ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->canceled_at)
                    : null,
            ]
        );

        // If subscription is active, add monthly credits
        if ($subscription->status === 'active' && $subscription->wasRecentlyCreated) {
            $this->addSubscriptionCredits($subscription);
        }

        \Log::info('Subscription updated', [
            'subscription_id' => $stripeSubscription->id,
            'user_id' => $userId,
            'status' => $stripeSubscription->status,
        ]);
    }

    /**
     * Handle subscription cancellation
     */
    protected function handleSubscriptionCancelled($stripeSubscription)
    {
        Subscription::where('stripe_subscription_id', $stripeSubscription->id)
            ->update([
                'status' => 'canceled',
                'canceled_at' => now(),
            ]);

        \Log::info('Subscription cancelled', ['subscription_id' => $stripeSubscription->id]);
    }

    /**
     * Handle subscription renewal (monthly credits)
     */
    protected function handleSubscriptionRenewal($invoice)
    {
        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

        if ($subscription && $subscription->status === 'active') {
            $this->addSubscriptionCredits($subscription);
        }
    }

    /**
     * Handle failed subscription payment
     */
    protected function handleSubscriptionPaymentFailed($invoice)
    {
        Subscription::where('stripe_subscription_id', $invoice->subscription)
            ->update(['status' => 'past_due']);

        \Log::warning('Subscription payment failed', ['subscription_id' => $invoice->subscription]);
    }

    /**
     * Add monthly credits for subscription (full amount, used for renewals)
     */
    protected function addSubscriptionCredits(Subscription $subscription)
    {
        $plans = config('subscriptions.plans');
        $plan = $plans[$subscription->plan] ?? null;

        if (!$plan || !isset($plan['credits_per_month'])) {
            return;
        }

        $user = User::find($subscription->user_id);
        if (!$user) {
            return;
        }

        $credits = $plan['credits_per_month'];

        $this->creditService->addCredits(
            $user,
            $credits,
            CreditTransaction::TYPE_SUBSCRIPTION,
            "Monthly subscription credits: {$subscription->plan}",
            $subscription->stripe_subscription_id
        );

        \Log::info('Subscription credits added', [
            'user_id' => $subscription->user_id,
            'credits' => $credits,
            'plan' => $subscription->plan,
        ]);
    }

    /**
     * Add prorated credits for subscription upgrade/new subscription
     * Only adds the difference when upgrading (e.g., Pro→Premium = +400 not +500)
     * Credit packs are never affected - only subscription credits are prorated
     */
    protected function addProratedSubscriptionCredits(Subscription $subscription, string $oldPlan)
    {
        $plans = config('subscriptions.plans');
        $newPlanConfig = $plans[$subscription->plan] ?? null;

        if (!$newPlanConfig || !isset($newPlanConfig['credits_per_month'])) {
            return;
        }

        $user = User::find($subscription->user_id);
        if (!$user) {
            return;
        }

        $newCredits = $newPlanConfig['credits_per_month'];
        $oldCredits = 0;

        // Get old plan credits (free = 0)
        if ($oldPlan !== 'free' && isset($plans[$oldPlan])) {
            $oldCredits = $plans[$oldPlan]['credits_per_month'] ?? 0;
        }

        // Calculate prorated credits (difference only)
        // Upgrade: pro(100) → premium(500) = +400
        // New subscription: free(0) → pro(100) = +100
        // Downgrade: premium(500) → pro(100) = 0 (no extra credits)
        $creditsToAdd = max(0, $newCredits - $oldCredits);

        if ($creditsToAdd <= 0) {
            \Log::info('No prorated credits to add (downgrade or same plan)', [
                'user_id' => $subscription->user_id,
                'old_plan' => $oldPlan,
                'new_plan' => $subscription->plan,
            ]);
            return;
        }

        $this->creditService->addCredits(
            $user,
            $creditsToAdd,
            CreditTransaction::TYPE_SUBSCRIPTION,
            $oldPlan === 'free'
                ? "New subscription credits: {$subscription->plan}"
                : "Upgrade credits: {$oldPlan} → {$subscription->plan} (+{$creditsToAdd})",
            $subscription->stripe_subscription_id
        );

        \Log::info('Prorated subscription credits added', [
            'user_id' => $subscription->user_id,
            'old_plan' => $oldPlan,
            'new_plan' => $subscription->plan,
            'old_credits' => $oldCredits,
            'new_credits' => $newCredits,
            'credits_added' => $creditsToAdd,
        ]);
    }

    /**
     * Get plan name from Stripe price ID
     */
    protected function getPlanFromPriceId(?string $priceId): string
    {
        if (!$priceId) {
            return 'unknown';
        }

        $plans = config('subscriptions.plans');
        foreach ($plans as $name => $plan) {
            if ($plan['stripe_price_id'] === $priceId) {
                return $name;
            }
        }

        return 'unknown';
    }
}
