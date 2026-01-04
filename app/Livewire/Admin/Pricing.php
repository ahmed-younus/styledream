<?php

namespace App\Livewire\Admin;

use App\Models\Setting;
use App\Services\CurrencyService;
use App\Services\PricingService;
use App\Services\StripeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Pricing Management')]
class Pricing extends Component
{
    // Subscription Plans
    public array $plans = [];

    // Credit Packs
    public array $creditPacks = [];

    // Supported currencies
    public array $currencies = [];

    // Plan Modal
    public bool $showPlanModal = false;
    public ?string $editingPlanKey = null;
    public bool $isNewPlan = false;

    // Plan form fields
    public string $planKey = '';
    public string $planName = '';
    public array $planPrices = ['usd' => 0, 'gbp' => 0, 'eur' => 0];
    public int $planCredits = 0;
    public array $planStripePriceIds = ['usd' => '', 'gbp' => '', 'eur' => ''];
    public string $planDescription = '';
    public string $planButtonText = '';
    public string $planBadge = '';
    public array $planFeatures = [''];

    // Pack Modal
    public bool $showPackModal = false;
    public ?string $editingPackKey = null;
    public bool $isNewPack = false;

    // Pack form fields
    public string $packKey = '';
    public int $packCredits = 0;
    public array $packPrices = ['usd' => 0, 'gbp' => 0, 'eur' => 0];
    public string $packBadge = '';

    public function mount()
    {
        $this->currencies = CurrencyService::getSupportedCurrencies();
        $this->loadPricing();
    }

    protected function loadPricing()
    {
        $savedPlans = Setting::get('subscription_plans');
        $savedPacks = Setting::get('credit_packs');

        $this->plans = $savedPlans ?: config('subscriptions.plans', []);
        $this->creditPacks = $savedPacks ?: config('credits.packs', []);
    }

    // ==================== PLAN METHODS ====================

    public function openPlanModal(?string $planKey = null)
    {
        $this->resetPlanForm();

        if ($planKey && isset($this->plans[$planKey])) {
            $this->editingPlanKey = $planKey;
            $this->isNewPlan = false;
            $plan = $this->plans[$planKey];

            $this->planKey = $planKey;
            $this->planName = $plan['name'] ?? '';

            // Handle both old single-price and new multi-currency formats
            if (isset($plan['prices']) && is_array($plan['prices'])) {
                $this->planPrices = [
                    'usd' => $plan['prices']['usd'] ?? 0,
                    'gbp' => $plan['prices']['gbp'] ?? 0,
                    'eur' => $plan['prices']['eur'] ?? 0,
                ];
            } else {
                // Old format - convert single price to multi-currency
                $price = $plan['price'] ?? 0;
                $this->planPrices = [
                    'usd' => $price,
                    'gbp' => (int) round($price * 0.8),  // Approximate conversion
                    'eur' => (int) round($price * 0.9),
                ];
            }

            // Handle stripe price IDs
            if (isset($plan['stripe_price_ids']) && is_array($plan['stripe_price_ids'])) {
                $this->planStripePriceIds = [
                    'usd' => $plan['stripe_price_ids']['usd'] ?? '',
                    'gbp' => $plan['stripe_price_ids']['gbp'] ?? '',
                    'eur' => $plan['stripe_price_ids']['eur'] ?? '',
                ];
            } else {
                // Old format
                $this->planStripePriceIds = [
                    'usd' => $plan['stripe_price_id'] ?? '',
                    'gbp' => '',
                    'eur' => '',
                ];
            }

            $this->planCredits = $plan['credits_per_month'] ?? 0;
            $this->planDescription = $plan['description'] ?? '';
            $this->planButtonText = $plan['button_text'] ?? '';
            $this->planBadge = $plan['badge'] ?? '';
            $this->planFeatures = !empty($plan['features']) ? $plan['features'] : [''];
        } else {
            $this->isNewPlan = true;
            $this->planFeatures = [''];
            $this->planPrices = ['usd' => 0, 'gbp' => 0, 'eur' => 0];
            $this->planStripePriceIds = ['usd' => '', 'gbp' => '', 'eur' => ''];
        }

        $this->showPlanModal = true;
    }

    public function addFeature()
    {
        $this->planFeatures[] = '';
    }

    public function removeFeature(int $index)
    {
        if (count($this->planFeatures) > 1) {
            unset($this->planFeatures[$index]);
            $this->planFeatures = array_values($this->planFeatures);
        }
    }

    public function savePlan()
    {
        $rules = [
            'planName' => 'required|string|max:50',
            'planPrices.usd' => 'required|integer|min:0',
            'planPrices.gbp' => 'required|integer|min:0',
            'planPrices.eur' => 'required|integer|min:0',
            'planCredits' => 'required|integer|min:0',
            'planDescription' => 'nullable|string|max:255',
            'planButtonText' => 'nullable|string|max:50',
            'planBadge' => 'nullable|string|max:30',
            'planFeatures' => 'array|min:1',
            'planFeatures.*' => 'nullable|string|max:100',
        ];

        if ($this->isNewPlan) {
            $rules['planKey'] = 'required|string|max:30|alpha_dash';
        }

        $this->validate($rules);

        $key = $this->isNewPlan ? Str::slug($this->planKey, '_') : $this->editingPlanKey;
        $hasPrice = $this->planPrices['usd'] > 0 || $this->planPrices['gbp'] > 0 || $this->planPrices['eur'] > 0;

        // Auto-sync with Stripe if prices are set
        $stripePriceIds = $this->planStripePriceIds;
        if ($hasPrice) {
            try {
                $stripePriceIds = StripeService::syncPlanPrices(
                    $key,
                    $this->planName,
                    [
                        'usd' => (int) $this->planPrices['usd'],
                        'gbp' => (int) $this->planPrices['gbp'],
                        'eur' => (int) $this->planPrices['eur'],
                    ],
                    $this->planStripePriceIds
                );
            } catch (\Exception $e) {
                session()->flash('warning', 'Failed to sync with Stripe: ' . $e->getMessage());
            }
        }

        // Filter out empty features
        $features = array_values(array_filter($this->planFeatures, fn($f) => !empty(trim($f))));

        $this->plans[$key] = [
            'name' => $this->planName,
            'prices' => [
                'usd' => (int) $this->planPrices['usd'],
                'gbp' => (int) $this->planPrices['gbp'],
                'eur' => (int) $this->planPrices['eur'],
            ],
            'stripe_price_ids' => $stripePriceIds,
            'credits_per_month' => $this->planCredits,
            'description' => $this->planDescription,
            'button_text' => $this->planButtonText ?: ($hasPrice ? "Upgrade to {$this->planName}" : 'Current Plan'),
            'badge' => $this->planBadge,
            'features' => $features,
        ];

        $this->savePlans();
        session()->flash('success', $this->isNewPlan ? "Plan '{$this->planName}' created & synced with Stripe!" : "Plan '{$this->planName}' updated & synced with Stripe!");
        $this->closePlanModal();
    }

    public function deletePlan(string $key)
    {
        if (isset($this->plans[$key])) {
            $name = $this->plans[$key]['name'] ?? $key;
            unset($this->plans[$key]);
            $this->savePlans();
            session()->flash('success', "Plan '{$name}' deleted!");
        }
    }

    protected function savePlans()
    {
        Setting::set('subscription_plans', $this->plans, Setting::GROUP_PRICING, Setting::TYPE_JSON);

        // Clear ALL cache to ensure pricing page updates immediately
        PricingService::flushAllCache();

        auth('admin')->user()->logActivity('update', Setting::class, null, null, null, 'Updated subscription plans');
    }

    public function closePlanModal()
    {
        $this->showPlanModal = false;
        $this->resetPlanForm();
    }

    protected function resetPlanForm()
    {
        $this->editingPlanKey = null;
        $this->isNewPlan = false;
        $this->planKey = '';
        $this->planName = '';
        $this->planPrices = ['usd' => 0, 'gbp' => 0, 'eur' => 0];
        $this->planCredits = 0;
        $this->planStripePriceIds = ['usd' => '', 'gbp' => '', 'eur' => ''];
        $this->planDescription = '';
        $this->planButtonText = '';
        $this->planBadge = '';
        $this->planFeatures = [''];
    }

    // ==================== PACK METHODS ====================

    public function openPackModal(?string $packKey = null)
    {
        $this->resetPackForm();

        if ($packKey && isset($this->creditPacks[$packKey])) {
            $this->editingPackKey = $packKey;
            $this->isNewPack = false;
            $pack = $this->creditPacks[$packKey];

            $this->packKey = $packKey;
            $this->packCredits = $pack['credits'] ?? 0;

            // Handle both old single-price and new multi-currency formats
            if (isset($pack['prices']) && is_array($pack['prices'])) {
                $this->packPrices = [
                    'usd' => $pack['prices']['usd'] ?? 0,
                    'gbp' => $pack['prices']['gbp'] ?? 0,
                    'eur' => $pack['prices']['eur'] ?? 0,
                ];
            } else {
                // Old format
                $price = $pack['price'] ?? 0;
                $this->packPrices = [
                    'usd' => $price,
                    'gbp' => (int) round($price * 0.8),
                    'eur' => (int) round($price * 0.9),
                ];
            }

            $this->packBadge = ($pack['popular'] ?? false) ? 'popular' : (($pack['best_value'] ?? false) ? 'best_value' : '');
        } else {
            $this->isNewPack = true;
            $this->packPrices = ['usd' => 0, 'gbp' => 0, 'eur' => 0];
        }

        $this->showPackModal = true;
    }

    public function savePack()
    {
        $rules = [
            'packCredits' => 'required|integer|min:1',
            'packPrices.usd' => 'required|integer|min:1',
            'packPrices.gbp' => 'required|integer|min:1',
            'packPrices.eur' => 'required|integer|min:1',
            'packBadge' => 'nullable|string|in:,popular,best_value',
        ];

        if ($this->isNewPack) {
            $rules['packKey'] = 'required|string|max:30|alpha_dash';
        }

        $this->validate($rules);

        $key = $this->isNewPack ? Str::slug($this->packKey, '_') : $this->editingPackKey;

        $this->creditPacks[$key] = [
            'credits' => $this->packCredits,
            'prices' => [
                'usd' => (int) $this->packPrices['usd'],
                'gbp' => (int) $this->packPrices['gbp'],
                'eur' => (int) $this->packPrices['eur'],
            ],
            'label' => $this->packCredits . ' Credits',
            'popular' => $this->packBadge === 'popular',
            'best_value' => $this->packBadge === 'best_value',
        ];

        $this->savePacks();
        session()->flash('success', $this->isNewPack ? "Credit pack created!" : "Credit pack updated!");
        $this->closePackModal();
    }

    public function deletePack(string $key)
    {
        if (isset($this->creditPacks[$key])) {
            $credits = $this->creditPacks[$key]['credits'] ?? 0;
            unset($this->creditPacks[$key]);
            $this->savePacks();
            session()->flash('success', "{$credits} credits pack deleted!");
        }
    }

    protected function savePacks()
    {
        Setting::set('credit_packs', $this->creditPacks, Setting::GROUP_PRICING, Setting::TYPE_JSON);

        // Clear ALL cache to ensure pricing page updates immediately
        PricingService::flushAllCache();

        auth('admin')->user()->logActivity('update', Setting::class, null, null, null, 'Updated credit packs');
    }

    public function closePackModal()
    {
        $this->showPackModal = false;
        $this->resetPackForm();
    }

    protected function resetPackForm()
    {
        $this->editingPackKey = null;
        $this->isNewPack = false;
        $this->packKey = '';
        $this->packCredits = 0;
        $this->packPrices = ['usd' => 0, 'gbp' => 0, 'eur' => 0];
        $this->packBadge = '';
    }

    // ==================== UTILITY METHODS ====================

    /**
     * Get price for display (handles both old and new formats)
     */
    public function getPlanPrice(array $plan, string $currency = 'usd'): int
    {
        if (isset($plan['prices']) && is_array($plan['prices'])) {
            return $plan['prices'][$currency] ?? 0;
        }
        return $plan['price'] ?? 0;
    }

    public function getPackPrice(array $pack, string $currency = 'usd'): int
    {
        if (isset($pack['prices']) && is_array($pack['prices'])) {
            return $pack['prices'][$currency] ?? 0;
        }
        return $pack['price'] ?? 0;
    }

    public function resetToDefaults()
    {
        Setting::where('key', 'subscription_plans')->delete();
        Setting::where('key', 'credit_packs')->delete();

        // Clear ALL cache
        PricingService::flushAllCache();

        $this->plans = config('subscriptions.plans', []);
        $this->creditPacks = config('credits.packs', []);

        auth('admin')->user()->logActivity('update', Setting::class, null, null, null, 'Reset pricing to defaults');
        session()->flash('success', 'Pricing reset to default values!');
    }

    /**
     * Sync all subscription plans with Stripe
     * Creates/updates Stripe Prices for all plans
     */
    public function syncAllWithStripe()
    {
        $synced = 0;
        $errors = [];

        foreach ($this->plans as $key => $plan) {
            $prices = $plan['prices'] ?? [];
            $hasPrice = ($prices['usd'] ?? 0) > 0 || ($prices['gbp'] ?? 0) > 0 || ($prices['eur'] ?? 0) > 0;

            if (!$hasPrice) {
                continue; // Skip free plans
            }

            try {
                $newPriceIds = StripeService::syncPlanPrices(
                    $key,
                    $plan['name'] ?? $key,
                    $prices,
                    $plan['stripe_price_ids'] ?? []
                );

                $this->plans[$key]['stripe_price_ids'] = $newPriceIds;
                $synced++;
            } catch (\Exception $e) {
                $errors[] = "{$key}: {$e->getMessage()}";
            }
        }

        $this->savePlans();

        if (count($errors) > 0) {
            session()->flash('warning', "Synced {$synced} plans. Errors: " . implode(', ', $errors));
        } else {
            session()->flash('success', "Successfully synced {$synced} plans with Stripe!");
        }

        auth('admin')->user()->logActivity('update', Setting::class, null, null, null, 'Synced all plans with Stripe');
    }

    /**
     * Sync a single plan with Stripe
     */
    public function syncPlanWithStripe(string $planKey)
    {
        if (!isset($this->plans[$planKey])) {
            session()->flash('error', 'Plan not found');
            return;
        }

        $plan = $this->plans[$planKey];
        $prices = $plan['prices'] ?? [];

        try {
            $newPriceIds = StripeService::syncPlanPrices(
                $planKey,
                $plan['name'] ?? $planKey,
                $prices,
                $plan['stripe_price_ids'] ?? []
            );

            $this->plans[$planKey]['stripe_price_ids'] = $newPriceIds;
            $this->savePlans();

            session()->flash('success', "Plan '{$plan['name']}' synced with Stripe!");
        } catch (\Exception $e) {
            session()->flash('error', 'Stripe sync failed: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.admin.pricing');
    }
}
