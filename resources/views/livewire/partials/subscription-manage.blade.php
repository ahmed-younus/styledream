{{-- Manage Subscription Modal --}}
<div>
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">{{ __('pricing.manage_subscription') }}</h2>
        <p class="text-gray-500 text-sm mt-1">{{ __('pricing.manage_subtitle') }}</p>
    </div>

    {{-- Current Plan Card --}}
    <div class="p-4 {{ $isCanceling ? 'bg-orange-50 border-orange-300' : 'bg-primary/10 border-primary' }} rounded-lg border-2 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <span class="text-xs {{ $isCanceling ? 'text-orange-600' : 'text-primary' }} uppercase tracking-wider font-medium">{{ __('pricing.current_plan') }}</span>
                <p class="font-bold text-gray-900 text-lg mt-1">{{ $planDetails['name'] ?? ucfirst($currentPlan) }}</p>
                <p class="text-gray-600">{{ $currentPlanPrice }}{{ __('pricing.per_month') }}</p>
            </div>
            <div class="text-right">
                @if($isCanceling)
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ __('pricing.canceling') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ __('pricing.active') }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Billing Cycle --}}
    <div class="p-4 bg-gray-50 rounded-lg mb-6 border border-gray-200">
        <h3 class="text-sm font-medium text-gray-700 mb-3">{{ __('pricing.billing_cycle') }}</h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">{{ __('pricing.current_period') }}</span>
                <span class="text-gray-900">{{ $billingCycleStart }} - {{ $billingCycleEnd }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">{{ $isCanceling ? __('pricing.ends_on') : __('pricing.next_billing') }}</span>
                <span class="{{ $isCanceling ? 'text-orange-600' : 'text-gray-900' }} font-medium">{{ $effectiveDate }}</span>
            </div>
            @if($subscriptionCredits !== null)
            <div class="flex justify-between pt-2 border-t border-gray-200 mt-2">
                <span class="text-gray-500">{{ __('pricing.credits_remaining_label') }}</span>
                <span class="text-gray-900 font-medium">{{ $subscriptionCredits }} {{ __('pricing.credits') }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- Payment Method --}}
    @if($hasPaymentMethod && $paymentMethod)
    <div x-data="{ editingCard: false }">
        {{-- Show saved card when not editing --}}
        <div x-show="!editingCard" class="p-4 bg-gray-50 rounded-lg mb-6 border border-gray-200">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-700">{{ __('pricing.payment_method') }}</h3>
            </div>
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
                <button @click="editingCard = true; $nextTick(() => window.initManageCardElement && window.initManageCardElement())"
                        class="px-3 py-1.5 text-sm text-primary hover:text-primary/80 hover:bg-primary/5 rounded transition-colors">
                    {{ __('pricing.edit') }}
                </button>
            </div>
        </div>

        {{-- Show card input when editing --}}
        <div x-show="editingCard" x-cloak class="p-4 bg-gray-50 rounded-lg mb-6 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm font-medium text-gray-700">{{ __('pricing.update_card') }}</span>
                <button @click="editingCard = false" class="text-xs text-gray-500 hover:text-gray-700">
                    {{ __('pricing.cancel') }}
                </button>
            </div>
            <div wire:ignore id="manage-card-element" class="p-3 bg-white border border-gray-300 rounded-lg min-h-[44px]"></div>
            <div class="mt-3 flex justify-end">
                <button @click="updatePaymentMethod()"
                        :disabled="processing"
                        class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50">
                    <span x-show="!processing">{{ __('pricing.save_card') }}</span>
                    <span x-show="processing" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('pricing.saving') }}
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Error Display --}}
    @if($error)
        <div class="text-red-500 text-sm mb-4">{{ $error }}</div>
    @endif
    <div x-show="error" x-text="error" class="text-red-500 text-sm mb-4"></div>

    {{-- Quick Actions --}}
    <div class="space-y-3 mb-6">
        <p class="text-sm font-medium text-gray-700">{{ __('pricing.quick_actions') }}</p>

        @if($isCanceling)
            {{-- Reactivate Subscription --}}
            <button wire:click="processReactivate"
                    wire:loading.attr="disabled"
                    class="w-full py-3 text-white rounded-lg hover:opacity-90 transition-colors font-semibold disabled:opacity-50"
                    style="background-color: #18181b;">
                <span wire:loading.remove wire:target="processReactivate">{{ __('pricing.reactivate_subscription') }}</span>
                <span wire:loading wire:target="processReactivate" class="flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('pricing.processing') }}
                </span>
            </button>
        @else
            @if($currentPlan === 'pro')
            {{-- Upgrade to Premium --}}
            <button wire:click="$dispatch('openSubscriptionModal', { plan: 'premium' })"
                    class="w-full py-3 text-white rounded-lg hover:opacity-90 transition-colors font-semibold"
                    style="background-color: #18181b;">
                {{ __('pricing.upgrade_to', ['plan' => 'Premium']) }}
            </button>
            @endif

            {{-- Cancel Subscription --}}
            <button wire:click="$dispatch('openSubscriptionModal', { plan: 'free' })"
                    class="w-full py-3 text-white rounded-lg hover:opacity-90 transition-colors font-semibold"
                    style="background-color: #18181b;">
                {{ __('pricing.cancel_subscription') }}
            </button>
        @endif
    </div>

    {{-- Close Button --}}
    <button wire:click="closeModal"
            class="w-full py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-gray-700 font-medium">
        {{ __('pricing.close') }}
    </button>
</div>
