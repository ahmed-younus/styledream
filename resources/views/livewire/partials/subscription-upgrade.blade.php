{{-- Upgrade Modal (Pro -> Premium) --}}
<div>
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-foreground">{{ __('pricing.upgrade_to', ['plan' => $planDetails['name'] ?? ucfirst($targetPlan)]) }}</h2>
    </div>

    {{-- Plan Comparison --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        {{-- Current Plan --}}
        <div class="p-4 bg-secondary rounded-lg border border-border">
            <span class="text-xs text-muted-foreground uppercase tracking-wider">{{ __('pricing.current') }}</span>
            <p class="font-semibold text-foreground mt-1">{{ ucfirst($currentPlan) }}</p>
            <p class="text-sm text-muted-foreground">{{ $currentPlanPrice }}{{ __('pricing.per_month') }}</p>
        </div>
        {{-- New Plan --}}
        <div class="p-4 bg-primary/10 rounded-lg border-2 border-primary">
            <span class="text-xs text-primary uppercase tracking-wider">{{ __('pricing.new') }}</span>
            <p class="font-semibold text-foreground mt-1">{{ $planDetails['name'] ?? ucfirst($targetPlan) }}</p>
            <p class="text-sm text-muted-foreground">{{ $targetPlanPrice }}{{ __('pricing.per_month') }}</p>
        </div>
    </div>

    {{-- Proration Details --}}
    @if($proration)
    <div class="p-4 bg-muted/50 rounded-lg mb-6">
        <h3 class="text-sm font-medium text-foreground mb-3">{{ __('pricing.order_details') }}</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-muted-foreground">{{ $planDetails['name'] ?? ucfirst($targetPlan) }} plan</span>
                <span class="text-foreground">{{ $proration['new_plan_price'] }}</span>
            </div>
            <div class="flex justify-between text-green-500">
                <span>{{ __('pricing.prorated_credit') }}</span>
                <span>-{{ $proration['prorated_credit'] }}</span>
            </div>
            <div class="border-t border-border pt-2 mt-2 flex justify-between font-semibold">
                <span class="text-foreground">{{ __('pricing.total_due_today') }}</span>
                <span class="text-foreground">{{ $proration['total_due'] }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Saved Payment Method --}}
    @if($hasPaymentMethod && $paymentMethod)
    <div x-data="{ editingCard: false }">
        {{-- Show saved card when not editing --}}
        <div x-show="!editingCard" class="p-4 bg-gray-50 rounded-lg mb-6 border border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-8 bg-white rounded border border-gray-200 flex items-center justify-center">
                        @if(strtolower($paymentMethod['brand']) === 'visa')
                            <span class="text-blue-600 font-bold text-xs italic">VISA</span>
                        @elseif(strtolower($paymentMethod['brand']) === 'mastercard')
                            <div class="flex" style="margin-left: -2px;">
                                <div class="w-3 h-3 rounded-full" style="background-color: #EB001B;"></div>
                                <div class="w-3 h-3 rounded-full" style="background-color: #F79E1B; margin-left: -4px;"></div>
                            </div>
                        @else
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-900 font-medium">{{ $paymentMethod['brand'] }} **** {{ $paymentMethod['last4'] }}</span>
                        <span class="text-xs text-gray-500">{{ $paymentMethod['exp_month'] }}/{{ $paymentMethod['exp_year'] }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <button @click="editingCard = true; $nextTick(() => window.initUpgradeCardElement && window.initUpgradeCardElement())"
                            class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                            title="Edit payment method">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Show card input when editing --}}
        <div x-show="editingCard" x-cloak class="p-4 bg-gray-50 rounded-lg mb-6 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-700">{{ __('pricing.payment_details') }}</span>
                <button @click="editingCard = false" class="text-xs text-gray-500 hover:text-gray-700">
                    {{ __('pricing.cancel') }}
                </button>
            </div>
            <div wire:ignore id="upgrade-card-element" class="p-3 bg-white border border-gray-300 rounded-lg min-h-[44px]"></div>
            <p class="text-xs text-gray-500 mt-2">{{ __('pricing.new_card_charged') }}</p>
        </div>
    </div>
    @endif

    {{-- Error Display --}}
    @if($error)
        <div class="text-destructive text-sm mb-4">{{ $error }}</div>
    @endif
    <div x-show="error" x-text="error" class="text-destructive text-sm mb-4"></div>

    {{-- Buttons --}}
    <div class="flex gap-3">
        <button wire:click="closeModal"
            class="flex-1 py-3 border border-border rounded-lg hover:bg-muted transition-colors text-foreground font-medium">
            {{ __('pricing.cancel') }}
        </button>
        <button @click="processUpgrade('{{ $targetPlan }}', '{{ $currency }}')"
            :disabled="processing"
            class="flex-1 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!processing">{{ __('pricing.upgrade_to', ['plan' => $planDetails['name'] ?? ucfirst($targetPlan)]) }}</span>
            <span x-show="processing" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('pricing.processing') }}
            </span>
        </button>
    </div>
</div>
