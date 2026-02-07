<?php

namespace App\Http\Controllers;

use App\Models\CreditPurchase;
use App\Models\CreditTransaction;
use App\Models\Subscription;
use App\Models\User;
use App\Services\CreditService;
use App\Services\CurrencyService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\StripeClient;
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
            'pack' => 'required|string',
            'currency' => 'nullable|string|in:usd,gbp,eur',
        ]);

        $pack = PricingService::getCreditPack($request->pack);
        if (!$pack) {
            abort(404, 'Credit pack not found');
        }

        // Get currency from request or user preference
        $currency = $request->currency ?? CurrencyService::getUserCurrency();

        // Get price for the selected currency
        $price = PricingService::getPackPrice($request->pack, $currency);

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
                    'currency' => $currency,
                    'product_data' => [
                        'name' => $pack['label'] ?? ($pack['credits'] . ' Credits'),
                        'description' => "Stylely Credit Pack - {$pack['credits']} credits for virtual try-on",
                        'images' => [config('app.url') . '/images/logo.png'],
                    ],
                    'unit_amount' => $price,
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
                'currency' => $currency,
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
            'plan' => 'required|string',
            'currency' => 'nullable|string|in:usd,gbp,eur',
        ]);

        $plan = PricingService::getPlan($request->plan);

        if (!$plan) {
            abort(404, 'Plan not found');
        }

        // Get currency from request or user preference
        $currency = $request->currency ?? CurrencyService::getUserCurrency();

        // Get the correct Stripe Price ID for this currency
        $stripePriceId = PricingService::getPlanStripePriceId($request->plan, $currency);

        if (!$stripePriceId) {
            // Fallback to USD if currency price not set
            $stripePriceId = PricingService::getPlanStripePriceId($request->plan, 'usd');
        }

        if (!$stripePriceId) {
            abort(400, 'Stripe price not configured for this plan');
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
                'price' => $stripePriceId,
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
                'currency' => $currency,
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

                    // Get period dates with null safety
                    $periodStart = $session->subscription->current_period_start
                        ? \Carbon\Carbon::createFromTimestamp($session->subscription->current_period_start)
                        : now();
                    $periodEnd = $session->subscription->current_period_end
                        ? \Carbon\Carbon::createFromTimestamp($session->subscription->current_period_end)
                        : now()->addMonth();

                    // Create subscription in our database
                    $subscription = Subscription::create([
                        'user_id' => auth()->id(),
                        'plan' => $plan,
                        'status' => $session->subscription->status,
                        'stripe_subscription_id' => $session->subscription->id,
                        'stripe_customer_id' => $session->customer,
                        'current_period_start' => $periodStart,
                        'current_period_end' => $periodEnd,
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

                    // Send subscription confirmation email to user
                    try {
                        \Log::info('Sending subscription confirmation email to user', ['email' => auth()->user()->email]);
                        $subscriptionEmail = new \App\Mail\SubscriptionConfirmationEmail(auth()->user(), $subscription);
                        \Mail::to(auth()->user()->email)->send($subscriptionEmail);
                        \Log::info('Subscription confirmation email sent to user successfully');
                    } catch (\Exception $e) {
                        \Log::error('Failed to send subscription confirmation email to user', [
                            'email' => auth()->user()->email,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }

                    // Send admin copy if enabled (separate try-catch)
                    try {
                        $planDetails = \App\Services\PricingService::getPlan($plan) ?? [];
                        $subject = 'Welcome to Stylely ' . ucfirst($plan) . ' - Subscription Confirmed';
                        \App\Mail\AdminNotificationEmail::sendIfEnabled(
                            'subscription-confirmation',
                            $subject,
                            [
                                'user_name' => auth()->user()->name,
                                'user_email' => auth()->user()->email,
                                'plan_name' => ucfirst($plan),
                                'credits' => $planDetails['credits_per_month'] ?? 0,
                            ]
                        );
                    } catch (\Exception $e) {
                        \Log::error('Failed to send subscription admin copy email', ['error' => $e->getMessage()]);
                    }
                } else {
                    // Subscription already exists (created by webhook)
                    // Check if credits were added, if not add them now
                    $user = auth()->user();
                    if ($existingSub->status === 'active' && $user->getSubscriptionCredits() === 0) {
                        $oldPlan = $session->metadata->old_plan ?? 'free';
                        $this->addProratedSubscriptionCredits($existingSub, $oldPlan);

                        \Log::info('Credits added to existing subscription via success callback', [
                            'user_id' => $user->id,
                            'plan' => $existingSub->plan,
                            'old_plan' => $oldPlan,
                        ]);
                    }

                    // Ensure user subscription tier is updated
                    if ($user->subscription_tier !== $existingSub->plan) {
                        $user->update([
                            'subscription_tier' => $existingSub->plan,
                            'subscription_ends_at' => $existingSub->current_period_end,
                        ]);
                    }
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
            'return_url' => route('billing.return'), // Return via sync route
        ]);

        return redirect($session->url);
    }

    /**
     * Handle return from Stripe Billing Portal
     * Syncs subscription status with Stripe before redirecting to pricing
     */
    public function billingReturn(Request $request)
    {
        $user = auth()->user();

        if ($user) {
            $this->syncSubscriptionWithStripe($user);
        }

        return redirect()->route('pricing');
    }

    /**
     * Sync user's subscription status with Stripe
     * Called after returning from Stripe portal or manually
     */
    public function syncSubscriptionWithStripe(User $user): void
    {
        if (!$user->stripe_customer_id) {
            return;
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            // Get all subscriptions for this customer
            $stripeSubscriptions = $stripe->subscriptions->all([
                'customer' => $user->stripe_customer_id,
                'limit' => 10,
            ]);

            foreach ($stripeSubscriptions->data as $stripeSub) {
                $localSub = Subscription::where('stripe_subscription_id', $stripeSub->id)->first();

                if (!$localSub) {
                    continue;
                }

                // Determine correct local status
                $localStatus = $stripeSub->status;
                if ($stripeSub->cancel_at_period_end && $stripeSub->status === 'active') {
                    $localStatus = 'canceling';
                }

                // Update local subscription
                // If canceling, clear any scheduled downgrade - full cancellation takes precedence
                $updateData = [
                    'status' => $localStatus,
                    'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSub->current_period_start),
                    'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSub->current_period_end),
                    'canceled_at' => $stripeSub->canceled_at
                        ? \Carbon\Carbon::createFromTimestamp($stripeSub->canceled_at)
                        : ($stripeSub->cancel_at_period_end ? now() : null),
                ];

                if ($localStatus === 'canceling') {
                    $updateData['scheduled_plan'] = null;
                    $updateData['scheduled_change_at'] = null;
                }

                $localSub->update($updateData);

                \Log::info('Subscription synced with Stripe', [
                    'subscription_id' => $stripeSub->id,
                    'stripe_status' => $stripeSub->status,
                    'local_status' => $localStatus,
                    'cancel_at_period_end' => $stripeSub->cancel_at_period_end,
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to sync subscription with Stripe', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update an existing subscription
     * Routes to upgrade checkout or scheduled downgrade based on plan change direction
     */
    public function updateSubscription(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
            'currency' => 'nullable|string|in:usd,gbp,eur',
        ]);

        $user = auth()->user();
        $newPlan = $request->plan;

        // Get active subscription
        $activeSubscription = $user->activeSubscription();

        if (!$activeSubscription) {
            // No existing subscription - redirect to create new one
            return $this->createSubscription($request);
        }

        // Don't allow switching to same plan
        if ($activeSubscription->plan === $newPlan) {
            return redirect()->route('pricing')->with('info', __('pricing.already_on_plan'));
        }

        // Determine if upgrade or downgrade
        $planTiers = ['free' => 0, 'pro' => 1, 'premium' => 2];
        $currentTier = $planTiers[$activeSubscription->plan] ?? 0;
        $newTier = $planTiers[$newPlan] ?? 0;

        if ($newTier > $currentTier) {
            // UPGRADE: Go through Stripe checkout for payment
            return $this->createUpgradeCheckout($request, $activeSubscription);
        } else {
            // DOWNGRADE: Schedule for period end
            return $this->scheduleDowngrade($request, $activeSubscription, $newPlan);
        }
    }

    /**
     * Create Stripe Checkout for subscription upgrade with prorated amount
     * Shows checkout page with the price difference
     */
    protected function createUpgradeCheckout(Request $request, Subscription $activeSubscription)
    {
        $user = auth()->user();
        $newPlan = $request->plan;
        $currency = $request->currency ?? CurrencyService::getUserCurrency();

        // Get plan configs
        $oldPlanConfig = PricingService::getPlan($activeSubscription->plan);
        $newPlanConfig = PricingService::getPlan($newPlan);

        if (!$newPlanConfig) {
            return redirect()->route('pricing')->with('error', 'Plan not found');
        }

        // Get prices in the selected currency
        $oldPrice = PricingService::getPlanPrice($activeSubscription->plan, $currency);
        $newPrice = PricingService::getPlanPrice($newPlan, $currency);

        // Calculate prorated amount based on remaining days in billing period
        $now = now();
        $periodEnd = $activeSubscription->current_period_end;
        $periodStart = $activeSubscription->current_period_start;

        $totalDays = $periodStart->diffInDays($periodEnd);
        $remainingDays = max(0, $now->diffInDays($periodEnd, false));

        // Calculate unused credit from current plan
        $unusedCredit = $totalDays > 0 ? (int) round(($remainingDays / $totalDays) * $oldPrice) : 0;

        // Calculate prorated amount for new plan for remaining days
        $proratedNewPlanCost = $totalDays > 0 ? (int) round(($remainingDays / $totalDays) * $newPrice) : $newPrice;

        // Amount to charge = prorated new plan cost - unused credit from old plan
        $upgradeAmount = max(0, $proratedNewPlanCost - $unusedCredit);

        // Minimum charge of 50 cents (Stripe minimum)
        if ($upgradeAmount > 0 && $upgradeAmount < 50) {
            $upgradeAmount = 50;
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            // If upgrade amount is 0 or very small, do instant upgrade without checkout
            if ($upgradeAmount < 50) {
                return $this->processInstantUpgrade($user, $activeSubscription, $newPlan, $currency);
            }

            // Create checkout session for the prorated upgrade amount
            $session = Session::create([
                'customer' => $user->stripe_customer_id,
                'payment_method_types' => null,
                'line_items' => [[
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => "Upgrade to {$newPlanConfig['name']}",
                            'description' => "Prorated upgrade from " . ucfirst($activeSubscription->plan) . " to " . ucfirst($newPlan) . " ({$remainingDays} days remaining)",
                        ],
                        'unit_amount' => $upgradeAmount,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('upgrade.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('pricing'),
                'metadata' => [
                    'user_id' => $user->id,
                    'type' => 'upgrade',
                    'old_plan' => $activeSubscription->plan,
                    'new_plan' => $newPlan,
                    'subscription_id' => $activeSubscription->id,
                    'currency' => $currency,
                ],
            ]);

            \Log::info('Upgrade checkout created', [
                'user_id' => $user->id,
                'old_plan' => $activeSubscription->plan,
                'new_plan' => $newPlan,
                'upgrade_amount' => $upgradeAmount,
                'remaining_days' => $remainingDays,
            ]);

            return redirect($session->url);

        } catch (\Exception $e) {
            \Log::error('Failed to create upgrade checkout', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('pricing')->with('error', 'Failed to start upgrade: ' . $e->getMessage());
        }
    }

    /**
     * Process instant upgrade when prorated amount is too small for checkout
     */
    protected function processInstantUpgrade($user, $activeSubscription, $newPlan, $currency)
    {
        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $newPriceId = PricingService::getPlanStripePriceId($newPlan, $currency);

            if (!$newPriceId) {
                $newPriceId = PricingService::getPlanStripePriceId($newPlan, 'usd');
            }

            // Retrieve and update subscription
            $stripeSubscription = $stripe->subscriptions->retrieve($activeSubscription->stripe_subscription_id);
            $stripe->subscriptions->update($activeSubscription->stripe_subscription_id, [
                'items' => [[
                    'id' => $stripeSubscription->items->data[0]->id,
                    'price' => $newPriceId,
                ]],
                'proration_behavior' => 'none', // No proration since amount is negligible
            ]);

            $oldPlan = $activeSubscription->plan;

            // Update local database
            $activeSubscription->update([
                'plan' => $newPlan,
                'scheduled_plan' => null,
                'scheduled_change_at' => null,
            ]);

            $user->update(['subscription_tier' => $newPlan]);
            $this->handlePlanChangeCredits($user, $oldPlan, $newPlan);

            // Send upgrade confirmation email to user
            try {
                \Log::info('Sending instant upgrade confirmation email to user', ['email' => $user->email]);
                $subscriptionEmail = new \App\Mail\SubscriptionConfirmationEmail($user, $activeSubscription);
                \Mail::to($user->email)->send($subscriptionEmail);
                \Log::info('Instant upgrade confirmation email sent to user successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to send instant upgrade confirmation email to user', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }

            // Send admin notification for instant upgrade
            try {
                $planDetails = \App\Services\PricingService::getPlan($newPlan) ?? [];
                $subject = 'Stylely Subscription Upgrade - ' . ucfirst($oldPlan) . ' to ' . ucfirst($newPlan);
                \App\Mail\AdminNotificationEmail::sendIfEnabled(
                    'subscription-confirmation',
                    $subject,
                    [
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'plan_name' => ucfirst($newPlan),
                        'old_plan' => ucfirst($oldPlan),
                        'credits' => $planDetails['credits_per_month'] ?? 0,
                        'type' => 'instant_upgrade',
                    ]
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send instant upgrade admin notification', ['error' => $e->getMessage()]);
            }

            return redirect()->route('pricing')->with('success', __('pricing.subscription_updated'));

        } catch (\Exception $e) {
            \Log::error('Failed to process instant upgrade', ['error' => $e->getMessage()]);
            return redirect()->route('pricing')->with('error', 'Failed to upgrade: ' . $e->getMessage());
        }
    }

    /**
     * Handle successful upgrade payment from checkout
     */
    public function upgradeSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('pricing')->with('error', __('pricing.payment_failed'));
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $session = Session::retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return redirect()->route('pricing')->with('error', __('pricing.payment_processing'));
            }

            // Get metadata
            $userId = $session->metadata->user_id;
            $newPlan = $session->metadata->new_plan;
            $oldPlan = $session->metadata->old_plan;
            $subscriptionId = $session->metadata->subscription_id;
            $currency = $session->metadata->currency ?? 'usd';

            $user = User::find($userId);
            $subscription = Subscription::find($subscriptionId);

            if (!$user || !$subscription) {
                return redirect()->route('pricing')->with('error', 'Subscription not found');
            }

            // Update subscription in Stripe
            $stripe = new StripeClient(config('services.stripe.secret'));
            $newPriceId = PricingService::getPlanStripePriceId($newPlan, $currency);

            if (!$newPriceId) {
                $newPriceId = PricingService::getPlanStripePriceId($newPlan, 'usd');
            }

            $stripeSubscription = $stripe->subscriptions->retrieve($subscription->stripe_subscription_id);
            $stripe->subscriptions->update($subscription->stripe_subscription_id, [
                'items' => [[
                    'id' => $stripeSubscription->items->data[0]->id,
                    'price' => $newPriceId,
                ]],
                'proration_behavior' => 'none', // Already paid proration via checkout
            ]);

            // Update local database
            $subscription->update([
                'plan' => $newPlan,
                'scheduled_plan' => null,
                'scheduled_change_at' => null,
            ]);

            $user->update(['subscription_tier' => $newPlan]);

            // Add credits for the upgrade
            $this->handlePlanChangeCredits($user, $oldPlan, $newPlan);

            \Log::info('Upgrade completed via checkout', [
                'user_id' => $userId,
                'old_plan' => $oldPlan,
                'new_plan' => $newPlan,
            ]);

            // Send upgrade confirmation email to user
            try {
                \Log::info('Sending upgrade confirmation email to user', ['email' => $user->email]);
                $subscriptionEmail = new \App\Mail\SubscriptionConfirmationEmail($user, $subscription);
                \Mail::to($user->email)->send($subscriptionEmail);
                \Log::info('Upgrade confirmation email sent to user successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to send upgrade confirmation email to user', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Send admin notification for upgrade
            try {
                $planDetails = \App\Services\PricingService::getPlan($newPlan) ?? [];
                $subject = 'Stylely Subscription Upgrade - ' . ucfirst($oldPlan) . ' to ' . ucfirst($newPlan);
                \App\Mail\AdminNotificationEmail::sendIfEnabled(
                    'subscription-confirmation',
                    $subject,
                    [
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'plan_name' => ucfirst($newPlan),
                        'old_plan' => ucfirst($oldPlan),
                        'credits' => $planDetails['credits_per_month'] ?? 0,
                        'type' => 'upgrade',
                    ]
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send upgrade admin notification email', ['error' => $e->getMessage()]);
            }

            return redirect()->route('pricing')->with('success', __('pricing.subscription_updated'));

        } catch (\Exception $e) {
            \Log::error('Upgrade success callback failed', ['error' => $e->getMessage()]);
            return redirect()->route('pricing')->with('error', 'Upgrade failed: ' . $e->getMessage());
        }
    }

    /**
     * Schedule a downgrade to take effect at period end
     * User keeps current plan benefits until billing cycle ends
     */
    protected function scheduleDowngrade(Request $request, Subscription $activeSubscription, string $newPlan)
    {
        $user = auth()->user();

        try {
            // Update local database with scheduled downgrade
            $activeSubscription->update([
                'scheduled_plan' => $newPlan,
                'scheduled_change_at' => $activeSubscription->current_period_end,
            ]);

            $endDate = $activeSubscription->current_period_end->format('F j, Y');

            \Log::info('Downgrade scheduled', [
                'user_id' => $user->id,
                'current_plan' => $activeSubscription->plan,
                'scheduled_plan' => $newPlan,
                'scheduled_at' => $endDate,
            ]);

            return redirect()->route('pricing')->with('success',
                __('pricing.downgrade_scheduled', [
                    'plan' => ucfirst($newPlan),
                    'date' => $endDate,
                    'current_plan' => ucfirst($activeSubscription->plan),
                ])
            );

        } catch (\Exception $e) {
            \Log::error('Failed to schedule downgrade', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('pricing')->with('error', 'Failed to schedule downgrade: ' . $e->getMessage());
        }
    }

    /**
     * Cancel subscription at period end (downgrade to free)
     * User keeps access until billing cycle ends
     */
    public function cancelSubscription(Request $request)
    {
        \Log::info('Cancel subscription called');

        $user = auth()->user();

        if (!$user) {
            \Log::error('Cancel subscription: No authenticated user');
            return redirect()->route('pricing')->with('error', 'Please login first');
        }

        $activeSubscription = $user->activeSubscription();

        \Log::info('Cancel subscription: checking subscription', [
            'user_id' => $user->id,
            'has_subscription' => $activeSubscription ? 'yes' : 'no',
            'stripe_subscription_id' => $activeSubscription?->stripe_subscription_id,
        ]);

        if (!$activeSubscription) {
            return redirect()->route('pricing')->with('error', __('pricing.no_subscription'));
        }

        if (!$activeSubscription->stripe_subscription_id) {
            \Log::error('Cancel subscription: No stripe_subscription_id');
            return redirect()->route('pricing')->with('error', 'Subscription not linked to Stripe');
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            \Log::info('Calling Stripe to cancel subscription', [
                'stripe_subscription_id' => $activeSubscription->stripe_subscription_id,
            ]);

            // Cancel at period end - user keeps access until billing cycle ends
            $stripe->subscriptions->update($activeSubscription->stripe_subscription_id, [
                'cancel_at_period_end' => true,
            ]);

            \Log::info('Stripe cancel successful, updating local DB');

            // Update local database with canceling status
            // Clear any scheduled downgrade - full cancellation takes precedence
            $activeSubscription->update([
                'status' => 'canceling',
                'canceled_at' => now(),
                'scheduled_plan' => null,
                'scheduled_change_at' => null,
            ]);

            $endDate = $activeSubscription->current_period_end->format('F j, Y');

            \Log::info('Subscription scheduled for cancellation', [
                'user_id' => $user->id,
                'plan' => $activeSubscription->plan,
                'cancels_on' => $endDate,
            ]);

            return redirect()->route('pricing')->with('success', __('pricing.subscription_canceled', ['date' => $endDate]));

        } catch (\Exception $e) {
            \Log::error('Failed to cancel subscription', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('pricing')->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }

    /**
     * Handle credit changes when switching between plans
     * Only adds difference when upgrading, no credits removed on downgrade
     */
    protected function handlePlanChangeCredits(User $user, string $oldPlan, string $newPlan): void
    {
        $oldPlanConfig = PricingService::getPlan($oldPlan);
        $newPlanConfig = PricingService::getPlan($newPlan);

        $oldCredits = $oldPlanConfig['credits_per_month'] ?? 0;
        $newCredits = $newPlanConfig['credits_per_month'] ?? 0;

        // Only add credits if upgrading (new plan has more credits)
        if ($newCredits > $oldCredits) {
            $creditsToAdd = $newCredits - $oldCredits;

            // Use subscription credits (monthly, reset on renewal)
            $this->creditService->addSubscriptionCredits($user, $user->getSubscriptionCredits() + $creditsToAdd);

            CreditTransaction::record(
                $user,
                $creditsToAdd,
                CreditTransaction::TYPE_SUBSCRIPTION,
                "Upgrade credits: {$oldPlan} → {$newPlan} (+{$creditsToAdd})"
            );

            \Log::info('Upgrade credits added', [
                'user_id' => $user->id,
                'old_plan' => $oldPlan,
                'new_plan' => $newPlan,
                'credits_added' => $creditsToAdd,
            ]);
        } else {
            \Log::info('No credits added (downgrade or same tier)', [
                'user_id' => $user->id,
                'old_plan' => $oldPlan,
                'new_plan' => $newPlan,
            ]);
        }
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
     * Handles case where customer ID exists but customer was deleted or belongs to different Stripe account
     */
    protected function getOrCreateStripeCustomer(User $user): string
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // If user has a customer ID, verify it exists in Stripe
        if ($user->stripe_customer_id) {
            try {
                $customer = Customer::retrieve($user->stripe_customer_id);
                if ($customer && !$customer->deleted) {
                    return $user->stripe_customer_id;
                }
            } catch (\Exception $e) {
                // Customer doesn't exist in Stripe (deleted or different account)
                \Log::info('Stripe customer not found, creating new one', [
                    'user_id' => $user->id,
                    'old_customer_id' => $user->stripe_customer_id,
                ]);
            }
        }

        // Create new customer
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

        // Send purchase confirmation email
        $purchase = CreditPurchase::where('stripe_session_id', $session->id)->first();
        if ($purchase) {
            // Send user email
            try {
                \Log::info('Sending purchase confirmation email to user', ['email' => $user->email]);
                $purchaseEmail = new \App\Mail\PurchaseConfirmationEmail($user, $purchase);
                \Mail::to($user->email)->send($purchaseEmail);
                \Log::info('Purchase confirmation email sent to user successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to send purchase confirmation email to user', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }

            // Send admin copy if enabled (separate try-catch)
            try {
                $subject = 'Your Stylely Credit Purchase Confirmation';
                \App\Mail\AdminNotificationEmail::sendIfEnabled(
                    'purchase-confirmation',
                    $subject,
                    [
                        'user_name' => $user->name,
                        'user_email' => $user->email,
                        'credits' => $purchase->credits,
                        'amount' => $purchase->amount,
                        'currency' => strtoupper($purchase->currency),
                    ]
                );
            } catch (\Exception $e) {
                \Log::error('Failed to send admin copy email', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Handle subscription creation/update
     * Also handles cancellation from Stripe Billing Portal (cancel_at_period_end)
     */
    protected function handleSubscriptionUpdate($stripeSubscription)
    {
        $userId = $stripeSubscription->metadata->user_id ?? null;
        $plan = $stripeSubscription->metadata->plan ?? null;
        $oldPlan = $stripeSubscription->metadata->old_plan ?? 'free';

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

        // Determine local status based on Stripe subscription state
        // If cancel_at_period_end is true, user canceled via Stripe portal - mark as "canceling"
        $localStatus = $stripeSubscription->status;
        if ($stripeSubscription->cancel_at_period_end && $stripeSubscription->status === 'active') {
            $localStatus = 'canceling';
        }

        $subscription = Subscription::updateOrCreate(
            ['stripe_subscription_id' => $stripeSubscription->id],
            [
                'user_id' => $userId,
                'plan' => $plan ?? $this->getPlanFromPriceId($stripeSubscription->items->data[0]->price->id ?? null),
                'status' => $localStatus,
                'stripe_customer_id' => $stripeSubscription->customer,
                'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                'canceled_at' => $stripeSubscription->canceled_at
                    ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->canceled_at)
                    : ($stripeSubscription->cancel_at_period_end ? now() : null),
            ]
        );

        // Update user's subscription tier
        $user = User::find($userId);
        if ($user && in_array($subscription->status, ['active', 'canceling'])) {
            $user->update([
                'subscription_tier' => $subscription->plan,
                'subscription_ends_at' => $subscription->current_period_end,
            ]);
        }

        // If subscription is active and newly created, add prorated credits
        if ($stripeSubscription->status === 'active' && $subscription->wasRecentlyCreated && !$stripeSubscription->cancel_at_period_end) {
            $this->addProratedSubscriptionCredits($subscription, $oldPlan);
        }

        \Log::info('Subscription updated via webhook', [
            'subscription_id' => $stripeSubscription->id,
            'user_id' => $userId,
            'stripe_status' => $stripeSubscription->status,
            'local_status' => $localStatus,
            'cancel_at_period_end' => $stripeSubscription->cancel_at_period_end ?? false,
            'plan' => $subscription->plan,
            'old_plan' => $oldPlan,
            'was_recently_created' => $subscription->wasRecentlyCreated,
        ]);
    }

    /**
     * Handle subscription cancellation (when period ends)
     * Resets subscription credits to 0 (purchased credits remain)
     */
    protected function handleSubscriptionCancelled($stripeSubscription)
    {
        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $subscription->update([
                'status' => 'canceled',
                'canceled_at' => now(),
            ]);

            // Reset subscription credits to 0 (keep purchased credits)
            $user = User::find($subscription->user_id);
            if ($user) {
                $user->update([
                    'subscription_credits' => 0,
                    'subscription_tier' => 'free',
                    'subscription_ends_at' => null,
                ]);

                \Log::info('Subscription credits reset after cancellation', [
                    'user_id' => $user->id,
                    'subscription_id' => $stripeSubscription->id,
                ]);
            }
        }

        \Log::info('Subscription cancelled', ['subscription_id' => $stripeSubscription->id]);
    }

    /**
     * Handle subscription renewal (monthly credits)
     * Also processes any scheduled plan changes (downgrades)
     */
    protected function handleSubscriptionRenewal($invoice)
    {
        $subscription = Subscription::where('stripe_subscription_id', $invoice->subscription)->first();

        if (!$subscription || $subscription->status !== 'active') {
            return;
        }

        // Check for scheduled plan change (downgrade)
        if ($subscription->scheduled_plan) {
            $oldPlan = $subscription->plan;
            $newPlan = $subscription->scheduled_plan;

            // Apply the scheduled downgrade
            $subscription->update([
                'plan' => $newPlan,
                'scheduled_plan' => null,
                'scheduled_change_at' => null,
            ]);

            // Update user tier
            $user = User::find($subscription->user_id);
            if ($user) {
                $user->update([
                    'subscription_tier' => $newPlan,
                ]);

                // Set credits for new (lower) plan
                $planConfig = PricingService::getPlan($newPlan);
                $credits = $planConfig['credits_per_month'] ?? 0;
                $this->creditService->addSubscriptionCredits($user, $credits);

                CreditTransaction::record(
                    $user,
                    $credits,
                    CreditTransaction::TYPE_SUBSCRIPTION,
                    "Plan changed: {$oldPlan} → {$newPlan} (monthly credits)",
                    $subscription->stripe_subscription_id
                );
            }

            \Log::info('Scheduled plan change applied on renewal', [
                'subscription_id' => $subscription->id,
                'user_id' => $subscription->user_id,
                'old_plan' => $oldPlan,
                'new_plan' => $newPlan,
            ]);
        } else {
            // Normal renewal - reset credits to plan amount
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
     * Uses subscription credits (reset monthly, not additive)
     */
    protected function addSubscriptionCredits(Subscription $subscription)
    {
        $plan = PricingService::getPlan($subscription->plan);

        if (!$plan || !isset($plan['credits_per_month'])) {
            return;
        }

        $user = User::find($subscription->user_id);
        if (!$user) {
            return;
        }

        $credits = $plan['credits_per_month'];

        // Use addSubscriptionCredits (resets to new amount, not additive)
        $this->creditService->addSubscriptionCredits($user, $credits);

        \Log::info('Subscription credits reset for renewal', [
            'user_id' => $subscription->user_id,
            'credits' => $credits,
            'plan' => $subscription->plan,
        ]);
    }

    /**
     * Add prorated credits for subscription upgrade/new subscription
     * Only adds the difference when upgrading (e.g., Pro→Premium = +400 not +500)
     * Uses subscription credits (monthly, not additive with purchased credits)
     */
    protected function addProratedSubscriptionCredits(Subscription $subscription, string $oldPlan)
    {
        $newPlanConfig = PricingService::getPlan($subscription->plan);

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
        if ($oldPlan !== 'free') {
            $oldPlanConfig = PricingService::getPlan($oldPlan);
            $oldCredits = $oldPlanConfig['credits_per_month'] ?? 0;
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

        // For new subscription from free: set subscription credits to new plan amount
        // For upgrade: add difference to existing subscription credits
        if ($oldPlan === 'free') {
            $this->creditService->addSubscriptionCredits($user, $newCredits);
        } else {
            // Add difference to existing subscription credits
            $currentSubCredits = $user->getSubscriptionCredits();
            $this->creditService->addSubscriptionCredits($user, $currentSubCredits + $creditsToAdd);
        }

        CreditTransaction::record(
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
     * Checks all currency price IDs
     */
    protected function getPlanFromPriceId(?string $priceId): string
    {
        if (!$priceId) {
            return 'unknown';
        }

        $plans = PricingService::getPlans();
        foreach ($plans as $name => $plan) {
            // Check new multi-currency format
            if (isset($plan['stripe_price_ids']) && is_array($plan['stripe_price_ids'])) {
                foreach ($plan['stripe_price_ids'] as $currencyPriceId) {
                    if ($currencyPriceId === $priceId) {
                        return $name;
                    }
                }
            }

            // Check old single-price format (fallback)
            if (isset($plan['stripe_price_id']) && $plan['stripe_price_id'] === $priceId) {
                return $name;
            }
        }

        return 'unknown';
    }

    // ==========================================
    // IN-APP SUBSCRIPTION METHODS
    // ==========================================

    /**
     * Create SetupIntent for trial activation
     * Returns client_secret for Stripe Elements
     */
    public function createTrialSetupIntent(Request $request)
    {
        $user = auth()->user();

        // Check if user already used trial
        if ($user->trial_activated_at) {
            return response()->json(['error' => __('pricing.trial_already_used')], 400);
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            // Get or create Stripe customer
            $customerId = $this->getOrCreateStripeCustomer($user);

            // Create SetupIntent - card will be validated when user confirms
            $setupIntent = $stripe->setupIntents->create([
                'customer' => $customerId,
                'payment_method_types' => ['card'],
                'usage' => 'off_session',
                'metadata' => [
                    'user_id' => $user->id,
                    'purpose' => 'trial_activation',
                ],
            ]);

            return response()->json([
                'client_secret' => $setupIntent->client_secret,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to create trial setup intent', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to initialize. Please try again.'], 500);
        }
    }

    /**
     * Activate free trial after SetupIntent succeeds
     * Card is validated but NOT charged
     */
    public function activateFreeTrial(Request $request)
    {
        $user = auth()->user();
        $setupIntentId = $request->setup_intent_id;

        // Check if user already used trial
        if ($user->trial_activated_at) {
            return response()->json(['error' => __('pricing.trial_already_used')], 400);
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            // Retrieve SetupIntent to verify it succeeded
            $setupIntent = $stripe->setupIntents->retrieve($setupIntentId);

            // Verify it belongs to this user
            if (($setupIntent->metadata->user_id ?? null) != $user->id) {
                return response()->json(['error' => 'Invalid setup intent'], 400);
            }

            // Check SetupIntent status
            if ($setupIntent->status !== 'succeeded') {
                return response()->json([
                    'error' => 'Card validation failed',
                    'status' => $setupIntent->status,
                ], 400);
            }

            // SetupIntent succeeded - card is VALID!
            // Set payment method as default
            $stripe->customers->update($user->stripe_customer_id, [
                'invoice_settings' => [
                    'default_payment_method' => $setupIntent->payment_method,
                ],
            ]);

            // Give 5 free credits (no charge)
            $user->update([
                'credits' => $user->credits + 5,
                'trial_activated_at' => now(),
            ]);

            // Log the trial activation
            CreditTransaction::record(
                $user,
                5,
                CreditTransaction::TYPE_PURCHASE,
                'Free trial credits (card validated)'
            );

            \Log::info('Trial activated', [
                'user_id' => $user->id,
                'credits_added' => 5,
            ]);

            // Send trial activation email
            try {
                $trialEmail = new \App\Mail\TrialActivationEmail($user);
                \Mail::to($user->email)->send($trialEmail);
                \Log::info('Trial activation email sent', ['email' => $user->email]);
            } catch (\Exception $e) {
                \Log::error('Failed to send trial activation email', ['error' => $e->getMessage()]);
            }

            return response()->json([
                'success' => true,
                'message' => __('pricing.trial_activated'),
                'credits' => $user->fresh()->totalCredits(),
                'redirect_url' => route('pricing') . '?trial_success=1',
            ]);

        } catch (\Exception $e) {
            \Log::error('Trial activation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Card validation failed: ' . $e->getMessage()], 400);
        }
    }

    /**
     * Create in-app subscription (Free -> Pro/Premium)
     * Uses Stripe PaymentIntent for immediate charge
     */
    public function createInAppSubscription(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
            'currency' => 'nullable|string|in:usd,gbp,eur',
            'payment_method_id' => 'required|string',
        ]);

        $user = auth()->user();
        $plan = $request->plan;
        $currency = $request->currency ?? CurrencyService::getUserCurrency();
        $paymentMethodId = $request->payment_method_id;

        $planConfig = PricingService::getPlan($plan);
        if (!$planConfig) {
            return response()->json(['error' => 'Invalid plan'], 400);
        }

        $stripePriceId = PricingService::getPlanStripePriceId($plan, $currency);
        if (!$stripePriceId) {
            $stripePriceId = PricingService::getPlanStripePriceId($plan, 'usd');
        }

        if (!$stripePriceId) {
            return response()->json(['error' => 'Plan not configured'], 400);
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $customerId = $this->getOrCreateStripeCustomer($user);

            // Attach payment method to customer
            $stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $customerId,
            ]);

            // Set as default
            $stripe->customers->update($customerId, [
                'invoice_settings' => ['default_payment_method' => $paymentMethodId],
            ]);

            // Cancel any existing subscriptions
            $this->cancelExistingStripeSubscriptions($customerId);

            // Create subscription with automatic payment
            $stripeSubscription = $stripe->subscriptions->create([
                'customer' => $customerId,
                'items' => [['price' => $stripePriceId]],
                'default_payment_method' => $paymentMethodId,
                'payment_behavior' => 'error_if_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                    'payment_method_types' => ['card'],
                ],
                'expand' => ['latest_invoice.payment_intent'],
                'metadata' => [
                    'user_id' => $user->id,
                    'plan' => $plan,
                    'old_plan' => 'free',
                ],
            ]);

            // Check subscription and payment status
            $paymentIntent = $stripeSubscription->latest_invoice->payment_intent ?? null;

            \Log::info('In-app subscription created', [
                'subscription_id' => $stripeSubscription->id,
                'subscription_status' => $stripeSubscription->status,
                'payment_intent_status' => $paymentIntent ? $paymentIntent->status : 'none',
            ]);

            // Handle different payment states
            if ($paymentIntent) {
                if ($paymentIntent->status === 'requires_action') {
                    return response()->json([
                        'requires_action' => true,
                        'client_secret' => $paymentIntent->client_secret,
                        'subscription_id' => $stripeSubscription->id,
                    ]);
                }

                if ($paymentIntent->status === 'requires_payment_method') {
                    return response()->json(['error' => 'Card was declined. Please try a different card.'], 400);
                }

                if ($paymentIntent->status === 'requires_confirmation') {
                    // Confirm the payment intent server-side
                    $confirmedIntent = $stripe->paymentIntents->confirm($paymentIntent->id);
                    if ($confirmedIntent->status !== 'succeeded') {
                        return response()->json(['error' => 'Payment confirmation failed.'], 400);
                    }
                }
            }

            // If subscription is active or incomplete (payment in progress), create local record
            $validStatuses = ['active', 'trialing', 'incomplete'];
            if (in_array($stripeSubscription->status, $validStatuses)) {
                // Calculate period dates with fallbacks
                $periodStart = $stripeSubscription->current_period_start
                    ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start)
                    : now();
                $periodEnd = $stripeSubscription->current_period_end
                    ? \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                    : now()->addMonth();

                // Create local subscription record
                $subscription = Subscription::create([
                    'user_id' => $user->id,
                    'plan' => $plan,
                    'status' => 'active',
                    'stripe_subscription_id' => $stripeSubscription->id,
                    'stripe_customer_id' => $customerId,
                    'current_period_start' => $periodStart,
                    'current_period_end' => $periodEnd,
                ]);

                // Update user
                $user->update([
                    'subscription_tier' => $plan,
                    'subscription_ends_at' => $subscription->current_period_end,
                ]);

                // Add subscription credits
                $this->addProratedSubscriptionCredits($subscription, 'free');

                return response()->json([
                    'success' => true,
                    'message' => __('pricing.subscription_activated'),
                ]);
            }

            return response()->json(['error' => 'Payment failed'], 400);

        } catch (\Exception $e) {
            \Log::error('In-app subscription failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Process in-app upgrade (Pro -> Premium)
     * Charges prorated amount using saved payment method or new card
     */
    public function processInAppUpgrade(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
            'currency' => 'nullable|string|in:usd,gbp,eur',
            'payment_method_id' => 'nullable|string',
        ]);

        $user = auth()->user();
        $newPlan = $request->plan;
        $currency = $request->currency ?? CurrencyService::getUserCurrency();
        $newPaymentMethodId = $request->payment_method_id;

        $activeSubscription = $user->activeSubscription();

        // Also check for canceling subscriptions
        if (!$activeSubscription) {
            $activeSubscription = $user->subscriptions()
                ->where('status', 'canceling')
                ->where('current_period_end', '>', now())
                ->orderBy('current_period_end', 'desc')
                ->first();
        }

        if (!$activeSubscription) {
            return response()->json(['error' => 'No active subscription'], 400);
        }

        $planTiers = ['free' => 0, 'pro' => 1, 'premium' => 2];
        $currentTier = $planTiers[$activeSubscription->plan] ?? 0;
        $targetTier = $planTiers[$newPlan] ?? 0;

        if ($targetTier <= $currentTier) {
            return response()->json(['error' => 'Can only upgrade to a higher plan'], 400);
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            // If subscription is canceling, reactivate it first
            if ($activeSubscription->status === 'canceling' && $activeSubscription->stripe_subscription_id) {
                $stripe->subscriptions->update($activeSubscription->stripe_subscription_id, [
                    'cancel_at_period_end' => false,
                ]);

                $activeSubscription->update([
                    'status' => 'active',
                    'canceled_at' => null,
                ]);

                \Log::info('Reactivated canceling subscription for upgrade', [
                    'user_id' => $user->id,
                    'subscription_id' => $activeSubscription->id,
                ]);
            }

            $newPriceId = PricingService::getPlanStripePriceId($newPlan, $currency);
            if (!$newPriceId) {
                $newPriceId = PricingService::getPlanStripePriceId($newPlan, 'usd');
            }

            // If user provided a new payment method, attach it and set as default
            $paymentMethodToUse = null;
            if ($newPaymentMethodId) {
                // Attach new payment method to customer
                $stripe->paymentMethods->attach($newPaymentMethodId, [
                    'customer' => $user->stripe_customer_id,
                ]);

                // Set as default for customer
                $stripe->customers->update($user->stripe_customer_id, [
                    'invoice_settings' => ['default_payment_method' => $newPaymentMethodId],
                ]);

                // Update subscription's default payment method
                $stripe->subscriptions->update($activeSubscription->stripe_subscription_id, [
                    'default_payment_method' => $newPaymentMethodId,
                ]);

                $paymentMethodToUse = $newPaymentMethodId;

                \Log::info('Updated payment method for upgrade', [
                    'user_id' => $user->id,
                    'payment_method_id' => $newPaymentMethodId,
                ]);
            }

            // Calculate proration
            $oldPrice = PricingService::getPlanPrice($activeSubscription->plan, $currency);
            $newPrice = PricingService::getPlanPrice($newPlan, $currency);

            $now = now();
            $periodEnd = $activeSubscription->current_period_end;
            $periodStart = $activeSubscription->current_period_start;
            $totalDays = $periodStart->diffInDays($periodEnd);
            $remainingDays = max(0, $now->diffInDays($periodEnd, false));

            $unusedCredit = $totalDays > 0 ? (int) round(($remainingDays / $totalDays) * $oldPrice) : 0;
            $proratedNewCost = $totalDays > 0 ? (int) round(($remainingDays / $totalDays) * $newPrice) : $newPrice;
            $upgradeAmount = max(0, $proratedNewCost - $unusedCredit);

            // Get Stripe subscription
            $stripeSubscription = $stripe->subscriptions->retrieve($activeSubscription->stripe_subscription_id);

            // Determine which payment method to use
            if (!$paymentMethodToUse) {
                $paymentMethodToUse = $stripeSubscription->default_payment_method;
            }

            // If upgrade amount is significant, charge it first
            if ($upgradeAmount >= 50) {
                // Create payment intent for the prorated amount
                // Use payment_method_types to avoid redirect-based methods
                $paymentIntent = $stripe->paymentIntents->create([
                    'amount' => $upgradeAmount,
                    'currency' => $currency,
                    'customer' => $user->stripe_customer_id,
                    'payment_method_types' => ['card'],
                    'description' => "Upgrade to {$newPlan} - prorated charge",
                    'metadata' => [
                        'user_id' => $user->id,
                        'type' => 'upgrade',
                        'old_plan' => $activeSubscription->plan,
                        'new_plan' => $newPlan,
                    ],
                ]);

                // Confirm with the payment method (new or existing)
                $paymentIntent = $stripe->paymentIntents->confirm($paymentIntent->id, [
                    'payment_method' => $paymentMethodToUse,
                ]);

                if ($paymentIntent->status === 'requires_action') {
                    return response()->json([
                        'requires_action' => true,
                        'client_secret' => $paymentIntent->client_secret,
                    ]);
                }

                if ($paymentIntent->status !== 'succeeded') {
                    return response()->json(['error' => 'Payment failed'], 400);
                }
            }

            // Update subscription to new plan
            $stripe->subscriptions->update($activeSubscription->stripe_subscription_id, [
                'items' => [[
                    'id' => $stripeSubscription->items->data[0]->id,
                    'price' => $newPriceId,
                ]],
                'proration_behavior' => 'none', // We handled proration ourselves
            ]);

            $oldPlan = $activeSubscription->plan;

            // Update local database
            $activeSubscription->update([
                'plan' => $newPlan,
                'scheduled_plan' => null,
                'scheduled_change_at' => null,
            ]);

            $user->update(['subscription_tier' => $newPlan]);

            // Add upgrade credits
            $this->handlePlanChangeCredits($user, $oldPlan, $newPlan);

            \Log::info('In-app upgrade completed', [
                'user_id' => $user->id,
                'old_plan' => $oldPlan,
                'new_plan' => $newPlan,
                'amount_charged' => $upgradeAmount,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('pricing.subscription_updated'),
            ]);

        } catch (\Exception $e) {
            \Log::error('In-app upgrade failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Process in-app downgrade (Premium -> Pro)
     * Schedules plan change for period end
     */
    public function processInAppDowngrade(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
        ]);

        $user = auth()->user();
        $newPlan = $request->plan;

        $activeSubscription = $user->activeSubscription();
        if (!$activeSubscription) {
            return response()->json(['error' => 'No active subscription'], 400);
        }

        try {
            // Update local database with scheduled downgrade
            $activeSubscription->update([
                'scheduled_plan' => $newPlan,
                'scheduled_change_at' => $activeSubscription->current_period_end,
            ]);

            $endDate = $activeSubscription->current_period_end->format('F j, Y');

            \Log::info('Downgrade scheduled via modal', [
                'user_id' => $user->id,
                'current_plan' => $activeSubscription->plan,
                'scheduled_plan' => $newPlan,
                'scheduled_at' => $endDate,
            ]);

            return response()->json([
                'success' => true,
                'message' => __('pricing.downgrade_scheduled', [
                    'plan' => ucfirst($newPlan),
                    'date' => $endDate,
                    'current_plan' => ucfirst($activeSubscription->plan),
                ]),
            ]);

        } catch (\Exception $e) {
            \Log::error('Downgrade scheduling failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Confirm subscription after 3D Secure authentication
     */
    public function confirmInAppSubscription(Request $request)
    {
        $request->validate([
            'subscription_id' => 'required|string',
        ]);

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $stripeSubscription = $stripe->subscriptions->retrieve($request->subscription_id);

            if ($stripeSubscription->status === 'active') {
                // Create local subscription if not exists
                $subscription = Subscription::firstOrCreate(
                    ['stripe_subscription_id' => $stripeSubscription->id],
                    [
                        'user_id' => auth()->id(),
                        'plan' => $stripeSubscription->metadata->plan ?? 'pro',
                        'status' => 'active',
                        'stripe_customer_id' => $stripeSubscription->customer,
                        'current_period_start' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_start),
                        'current_period_end' => \Carbon\Carbon::createFromTimestamp($stripeSubscription->current_period_end),
                    ]
                );

                // Update user
                auth()->user()->update([
                    'subscription_tier' => $subscription->plan,
                    'subscription_ends_at' => $subscription->current_period_end,
                ]);

                return response()->json(['success' => true]);
            }

            return response()->json(['error' => 'Subscription not active'], 400);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Get user's saved payment method
     */
    public function getPaymentMethod(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->stripe_customer_id) {
            return response()->json(['has_payment_method' => false]);
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $customer = $stripe->customers->retrieve($user->stripe_customer_id, [
                'expand' => ['invoice_settings.default_payment_method'],
            ]);

            $pm = $customer->invoice_settings->default_payment_method ?? null;

            if ($pm && isset($pm->card)) {
                return response()->json([
                    'has_payment_method' => true,
                    'payment_method' => [
                        'brand' => ucfirst($pm->card->brand),
                        'last4' => $pm->card->last4,
                        'exp_month' => str_pad($pm->card->exp_month, 2, '0', STR_PAD_LEFT),
                        'exp_year' => $pm->card->exp_year,
                    ],
                ]);
            }

            return response()->json(['has_payment_method' => false]);

        } catch (\Exception $e) {
            return response()->json(['has_payment_method' => false]);
        }
    }

    /**
     * Calculate proration for upgrade preview
     */
    public function calculateProration(Request $request)
    {
        $request->validate([
            'plan' => 'required|string',
            'currency' => 'nullable|string|in:usd,gbp,eur',
        ]);

        $user = auth()->user();
        $newPlan = $request->plan;
        $currency = $request->currency ?? CurrencyService::getUserCurrency();

        $activeSubscription = $user->activeSubscription();
        if (!$activeSubscription) {
            return response()->json(['error' => 'No active subscription'], 400);
        }

        $oldPrice = PricingService::getPlanPrice($activeSubscription->plan, $currency);
        $newPrice = PricingService::getPlanPrice($newPlan, $currency);

        $now = now();
        $periodEnd = $activeSubscription->current_period_end;
        $periodStart = $activeSubscription->current_period_start;
        $totalDays = $periodStart->diffInDays($periodEnd);
        $remainingDays = max(0, $now->diffInDays($periodEnd, false));

        $unusedCredit = $totalDays > 0 ? (int) round(($remainingDays / $totalDays) * $oldPrice) : 0;
        $proratedNewCost = $totalDays > 0 ? (int) round(($remainingDays / $totalDays) * $newPrice) : $newPrice;
        $upgradeAmount = max(0, $proratedNewCost - $unusedCredit);

        $currencySymbol = CurrencyService::getSymbol($currency);

        return response()->json([
            'new_plan_price' => $currencySymbol . number_format($newPrice / 100, 2),
            'prorated_credit' => $currencySymbol . number_format($unusedCredit / 100, 2),
            'total_due' => $currencySymbol . number_format($upgradeAmount / 100, 2),
            'raw_amount' => $upgradeAmount,
        ]);
    }

    /**
     * Update user's payment method
     */
    public function updatePaymentMethod(Request $request)
    {
        $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        $user = auth()->user();
        $paymentMethodId = $request->payment_method_id;

        if (!$user || !$user->stripe_customer_id) {
            return response()->json(['error' => 'No customer account found'], 400);
        }

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));

            // Attach new payment method to customer
            $stripe->paymentMethods->attach($paymentMethodId, [
                'customer' => $user->stripe_customer_id,
            ]);

            // Set as default for customer
            $stripe->customers->update($user->stripe_customer_id, [
                'invoice_settings' => ['default_payment_method' => $paymentMethodId],
            ]);

            // Update subscription's default payment method if exists
            $activeSubscription = $user->activeSubscription();
            if ($activeSubscription && $activeSubscription->stripe_subscription_id) {
                $stripe->subscriptions->update($activeSubscription->stripe_subscription_id, [
                    'default_payment_method' => $paymentMethodId,
                ]);
            }

            \Log::info('Payment method updated', [
                'user_id' => $user->id,
                'payment_method_id' => $paymentMethodId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully',
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update payment method', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Purchase credit pack in-app (no redirect)
     * Uses PaymentIntent for one-time payment
     */
    public function purchaseCreditPackInApp(Request $request)
    {
        $request->validate([
            'pack' => 'required|string',
            'currency' => 'nullable|string|in:usd,gbp,eur',
            'payment_method_id' => 'nullable|string',
            'use_saved_card' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $packKey = $request->pack;
        $currency = $request->currency ?? CurrencyService::getUserCurrency();
        $paymentMethodId = $request->payment_method_id;
        $useSavedCard = $request->use_saved_card ?? false;

        // Get pack details
        $pack = PricingService::getCreditPack($packKey);
        if (!$pack) {
            return response()->json(['error' => 'Invalid credit pack'], 400);
        }

        // Get price for the selected currency
        $price = PricingService::getPackPrice($packKey, $currency);

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $customerId = $this->getOrCreateStripeCustomer($user);

            // If using saved card, get the default payment method
            if ($useSavedCard && !$paymentMethodId) {
                $customer = $stripe->customers->retrieve($customerId, [
                    'expand' => ['invoice_settings.default_payment_method'],
                ]);

                $defaultPm = $customer->invoice_settings->default_payment_method ?? null;
                if (!$defaultPm) {
                    return response()->json(['error' => 'No saved payment method found. Please add a card.'], 400);
                }

                $paymentMethodId = is_string($defaultPm) ? $defaultPm : $defaultPm->id;
                \Log::info('Using saved payment method', ['payment_method_id' => $paymentMethodId]);
            }

            if (!$paymentMethodId) {
                return response()->json(['error' => 'Payment method required'], 400);
            }

            // Attach payment method to customer (only if it's a new one)
            if (!$useSavedCard) {
                try {
                    $stripe->paymentMethods->attach($paymentMethodId, [
                        'customer' => $customerId,
                    ]);
                } catch (\Exception $e) {
                    // Payment method might already be attached
                    \Log::info('Payment method attach info', ['message' => $e->getMessage()]);
                }

                // Set as default
                $stripe->customers->update($customerId, [
                    'invoice_settings' => ['default_payment_method' => $paymentMethodId],
                ]);
            }

            // Create PaymentIntent
            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $price,
                'currency' => $currency,
                'customer' => $customerId,
                'payment_method' => $paymentMethodId,
                'payment_method_types' => ['card'],
                'description' => "Stylely Credit Pack - {$pack['credits']} credits",
                'metadata' => [
                    'user_id' => $user->id,
                    'type' => 'credit_pack',
                    'pack' => $packKey,
                    'credits' => $pack['credits'],
                ],
                'confirm' => true,
                'return_url' => route('pricing'), // Required for 3DS redirect fallback
            ]);

            \Log::info('Credit pack PaymentIntent created', [
                'payment_intent_id' => $paymentIntent->id,
                'status' => $paymentIntent->status,
                'user_id' => $user->id,
                'pack' => $packKey,
            ]);

            // Handle different payment states
            if ($paymentIntent->status === 'requires_action') {
                return response()->json([
                    'requires_action' => true,
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id,
                ]);
            }

            if ($paymentIntent->status === 'requires_payment_method') {
                return response()->json(['error' => 'Card was declined. Please try a different card.'], 400);
            }

            if ($paymentIntent->status === 'succeeded') {
                // Fulfill the order
                $this->fulfillCreditPackOrder($user, $pack, $packKey, $paymentIntent, $currency);

                return response()->json([
                    'success' => true,
                    'message' => __('pricing.credits_added', ['credits' => $pack['credits']]),
                    'credits' => $user->fresh()->totalCredits(),
                ]);
            }

            return response()->json(['error' => 'Payment failed. Please try again.'], 400);

        } catch (\Exception $e) {
            \Log::error('Credit pack in-app purchase failed', [
                'user_id' => $user->id,
                'pack' => $packKey,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Confirm credit pack payment after 3D Secure
     */
    public function confirmCreditPackPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        $user = auth()->user();
        $paymentIntentId = $request->payment_intent_id;

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId);

            // Verify this belongs to the user
            if (($paymentIntent->metadata->user_id ?? null) != $user->id) {
                return response()->json(['error' => 'Invalid payment'], 400);
            }

            if ($paymentIntent->status === 'succeeded') {
                // Check if already fulfilled
                $existingPurchase = CreditPurchase::where('stripe_payment_intent', $paymentIntentId)->first();
                if ($existingPurchase) {
                    return response()->json([
                        'success' => true,
                        'message' => __('pricing.credits_added', ['credits' => $existingPurchase->credits]),
                        'credits' => $user->fresh()->totalCredits(),
                    ]);
                }

                // Fulfill the order
                $packKey = $paymentIntent->metadata->pack;
                $pack = PricingService::getCreditPack($packKey);
                $currency = $paymentIntent->currency;

                $this->fulfillCreditPackOrder($user, $pack, $packKey, $paymentIntent, $currency);

                return response()->json([
                    'success' => true,
                    'message' => __('pricing.credits_added', ['credits' => $pack['credits']]),
                    'credits' => $user->fresh()->totalCredits(),
                ]);
            }

            return response()->json(['error' => 'Payment not completed'], 400);

        } catch (\Exception $e) {
            \Log::error('Credit pack confirmation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Fulfill credit pack order after successful payment
     */
    protected function fulfillCreditPackOrder($user, $pack, $packKey, $paymentIntent, $currency)
    {
        // Check if already fulfilled
        if (CreditPurchase::where('stripe_payment_intent', $paymentIntent->id)->exists()) {
            \Log::info('Credit pack order already fulfilled', ['payment_intent_id' => $paymentIntent->id]);
            return;
        }

        // Record purchase
        $purchase = CreditPurchase::create([
            'user_id' => $user->id,
            'pack' => $packKey,
            'credits' => $pack['credits'],
            'amount' => $paymentIntent->amount,
            'currency' => $currency,
            'stripe_session_id' => 'pi_' . $paymentIntent->id,
            'stripe_payment_intent' => $paymentIntent->id,
        ]);

        // Add credits to user account
        $this->creditService->addCredits(
            $user,
            $pack['credits'],
            CreditTransaction::TYPE_PURCHASE,
            "Credit pack purchase: {$packKey}",
            $paymentIntent->id
        );

        \Log::info('Credit pack fulfilled via in-app purchase', [
            'user_id' => $user->id,
            'credits' => $pack['credits'],
            'payment_intent_id' => $paymentIntent->id,
        ]);

        // Send purchase confirmation email
        try {
            $purchaseEmail = new \App\Mail\PurchaseConfirmationEmail($user, $purchase);
            \Mail::to($user->email)->send($purchaseEmail);
        } catch (\Exception $e) {
            \Log::error('Failed to send credit pack purchase email', ['error' => $e->getMessage()]);
        }

        // Send admin notification
        try {
            \App\Mail\AdminNotificationEmail::sendIfEnabled(
                'purchase-confirmation',
                'Stylely Credit Pack Purchase',
                [
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                    'credits' => $purchase->credits,
                    'amount' => $purchase->amount,
                    'currency' => strtoupper($purchase->currency),
                ]
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send admin notification for credit pack', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Quick single try-on purchase (pay-per-use)
     * Charges a small amount and adds 1 credit
     */
    public function purchaseSingleTryOn(Request $request)
    {
        $request->validate([
            'currency' => 'nullable|string|in:usd,gbp,eur',
            'payment_method_id' => 'nullable|string',
            'use_saved_card' => 'nullable|boolean',
        ]);

        $user = auth()->user();
        $currency = $request->currency ?? CurrencyService::getUserCurrency();
        $paymentMethodId = $request->payment_method_id;
        $useSavedCard = $request->use_saved_card ?? false;

        $singleTryOn = config('credits.single_tryon');
        if (!$singleTryOn) {
            return response()->json(['error' => 'Single try-on purchase not available'], 400);
        }

        $price = $singleTryOn['prices'][$currency] ?? $singleTryOn['prices']['usd'];

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $customerId = $this->getOrCreateStripeCustomer($user);

            if ($useSavedCard && !$paymentMethodId) {
                $customer = $stripe->customers->retrieve($customerId, [
                    'expand' => ['invoice_settings.default_payment_method'],
                ]);
                $defaultPm = $customer->invoice_settings->default_payment_method ?? null;
                if (!$defaultPm) {
                    return response()->json(['error' => 'No saved payment method found.'], 400);
                }
                $paymentMethodId = is_string($defaultPm) ? $defaultPm : $defaultPm->id;
            }

            if (!$paymentMethodId) {
                return response()->json(['error' => 'Payment method required'], 400);
            }

            if (!$useSavedCard) {
                try {
                    $stripe->paymentMethods->attach($paymentMethodId, [
                        'customer' => $customerId,
                    ]);
                } catch (\Exception $e) {
                    \Log::info('Payment method attach info', ['message' => $e->getMessage()]);
                }

                $stripe->customers->update($customerId, [
                    'invoice_settings' => ['default_payment_method' => $paymentMethodId],
                ]);
            }

            $paymentIntent = $stripe->paymentIntents->create([
                'amount' => $price,
                'currency' => $currency,
                'customer' => $customerId,
                'payment_method' => $paymentMethodId,
                'payment_method_types' => ['card'],
                'description' => 'Stylely - Single Try-On',
                'metadata' => [
                    'user_id' => $user->id,
                    'type' => 'single_tryon',
                    'credits' => 1,
                ],
                'confirm' => true,
                'return_url' => route('studio'),
            ]);

            if ($paymentIntent->status === 'requires_action') {
                return response()->json([
                    'requires_action' => true,
                    'client_secret' => $paymentIntent->client_secret,
                    'payment_intent_id' => $paymentIntent->id,
                ]);
            }

            if ($paymentIntent->status === 'requires_payment_method') {
                return response()->json(['error' => 'Card was declined. Please try a different card.'], 400);
            }

            if ($paymentIntent->status === 'succeeded') {
                $this->fulfillSingleTryOn($user, $paymentIntent, $currency);

                return response()->json([
                    'success' => true,
                    'credits' => $user->fresh()->totalCredits(),
                ]);
            }

            return response()->json(['error' => 'Payment failed. Please try again.'], 400);

        } catch (\Exception $e) {
            \Log::error('Single try-on purchase failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Confirm single try-on payment after 3D Secure
     */
    public function confirmSingleTryOn(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string',
        ]);

        $user = auth()->user();

        try {
            $stripe = new StripeClient(config('services.stripe.secret'));
            $paymentIntent = $stripe->paymentIntents->retrieve($request->payment_intent_id);

            if (($paymentIntent->metadata->user_id ?? null) != $user->id) {
                return response()->json(['error' => 'Invalid payment'], 400);
            }

            if ($paymentIntent->status === 'succeeded') {
                $existing = CreditPurchase::where('stripe_payment_intent', $paymentIntent->id)->first();
                if ($existing) {
                    return response()->json([
                        'success' => true,
                        'credits' => $user->fresh()->totalCredits(),
                    ]);
                }

                $this->fulfillSingleTryOn($user, $paymentIntent, $paymentIntent->currency);

                return response()->json([
                    'success' => true,
                    'credits' => $user->fresh()->totalCredits(),
                ]);
            }

            return response()->json(['error' => 'Payment not completed'], 400);

        } catch (\Exception $e) {
            \Log::error('Single try-on confirmation failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    protected function fulfillSingleTryOn($user, $paymentIntent, $currency)
    {
        if (CreditPurchase::where('stripe_payment_intent', $paymentIntent->id)->exists()) {
            return;
        }

        CreditPurchase::create([
            'user_id' => $user->id,
            'pack' => 'single_tryon',
            'credits' => 1,
            'amount' => $paymentIntent->amount,
            'currency' => $currency,
            'stripe_session_id' => 'pi_' . $paymentIntent->id,
            'stripe_payment_intent' => $paymentIntent->id,
        ]);

        $this->creditService->addCredits(
            $user,
            1,
            CreditTransaction::TYPE_PURCHASE,
            'Single try-on purchase',
            $paymentIntent->id
        );

        \Log::info('Single try-on fulfilled', [
            'user_id' => $user->id,
            'payment_intent_id' => $paymentIntent->id,
        ]);
    }
}
