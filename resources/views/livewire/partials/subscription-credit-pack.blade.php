{{-- Credit Pack Purchase Modal --}}
<div class="text-gray-900">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold">{{ __('pricing.buy_credit_pack', ['credits' => $creditPackDetails['credits'] ?? 0]) }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ __('pricing.credit_pack_description') }}</p>
    </div>

    {{-- Pack Details --}}
    <div class="p-4 bg-primary/5 rounded-lg mb-6 border border-primary/20">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center">
                    <span class="text-xl font-bold text-primary">{{ $creditPackDetails['credits'] ?? 0 }}</span>
                </div>
                <div>
                    <p class="font-semibold">{{ $creditPackDetails['label'] ?? ($creditPackDetails['credits'] . ' Credits') }}</p>
                    <p class="text-sm text-gray-500">{{ $creditPackDetails['per_credit'] ?? '' }}</p>
                </div>
            </div>
            <div class="text-right">
                <span class="text-2xl font-bold">{{ $creditPackPrice }}</span>
            </div>
        </div>
    </div>

    {{-- Saved Payment Method --}}
    @if($hasPaymentMethod && $paymentMethod)
    <div x-data="{ useNewCard: false }">
        <div class="p-4 bg-gray-50 rounded-lg mb-4 border border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-7 bg-white rounded border border-gray-200 flex items-center justify-center">
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
                    <div>
                        <span class="text-sm font-medium">{{ $paymentMethod['brand'] }} **** {{ $paymentMethod['last4'] }}</span>
                        <span class="text-xs text-gray-500 ml-2">{{ $paymentMethod['exp_month'] }}/{{ $paymentMethod['exp_year'] }}</span>
                    </div>
                </div>
                <button @click="useNewCard = !useNewCard; if(useNewCard) { $nextTick(() => window.initCreditPackCardElement && window.initCreditPackCardElement()) }"
                        class="text-sm text-primary hover:underline">
                    <span x-show="!useNewCard">{{ __('pricing.edit') }}</span>
                    <span x-show="useNewCard">{{ __('pricing.cancel') }}</span>
                </button>
            </div>
        </div>

        {{-- New Card Input (when editing) --}}
        <div x-show="useNewCard" x-cloak class="mb-4">
            <label class="block text-sm font-medium mb-2">{{ __('pricing.update_card') }}</label>
            <div wire:ignore id="credit-pack-card-element" class="p-4 bg-white border border-gray-300 rounded-lg min-h-[44px]"></div>
        </div>

        {{-- Error Display --}}
        <div x-show="error" x-text="error" class="text-red-500 text-sm mb-4"></div>
        @if($error)
            <div class="text-red-500 text-sm mb-4">{{ $error }}</div>
        @endif

        {{-- Buttons --}}
        <div class="flex gap-3">
            <button wire:click="closeModal"
                class="flex-1 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">
                {{ __('pricing.cancel') }}
            </button>
            <button @click="purchaseCreditPack('{{ $creditPack }}', '{{ $currency }}', useNewCard)"
                :disabled="processing"
                class="flex-1 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!processing">{{ __('pricing.purchase_credits') }}</span>
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
    @else
    {{-- No Saved Card - Show Card Input --}}
    <div class="space-y-3">
        <label class="block text-sm font-medium">{{ __('pricing.payment_details') }}</label>
        <div wire:ignore id="card-element" class="p-4 bg-white border border-gray-300 rounded-lg min-h-[44px]"></div>

        {{-- Error Display --}}
        <div x-show="error" x-text="error" class="text-red-500 text-sm"></div>
        @if($error)
            <div class="text-red-500 text-sm">{{ $error }}</div>
        @endif
    </div>

    {{-- Buttons --}}
    <div class="mt-6 flex gap-3">
        <button wire:click="closeModal"
            class="flex-1 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">
            {{ __('pricing.cancel') }}
        </button>
        <button @click="purchaseCreditPack('{{ $creditPack }}', '{{ $currency }}', false)"
            :disabled="processing"
            class="flex-1 py-3 bg-primary text-white rounded-lg hover:bg-primary/90 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!processing">{{ __('pricing.purchase_credits') }}</span>
            <span x-show="processing" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('pricing.processing') }}
            </span>
        </button>
    </div>
    @endif

    {{-- Secure Payment Notice --}}
    <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-500">
        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        {{ __('pricing.secure_payment') }}
    </div>
</div>
