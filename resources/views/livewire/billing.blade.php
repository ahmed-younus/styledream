<div class="min-h-screen pt-20 md:pt-24 pb-12 px-4 bg-background">
    <div class="max-w-2xl mx-auto">

        {{-- Page Title --}}
        <h1 class="text-2xl sm:text-3xl font-bold text-foreground mb-8">{{ __('billing.title') }}</h1>

        {{-- Error Display --}}
        @if($error)
            <div class="mb-6 p-4 bg-destructive/10 border border-destructive/20 rounded-xl text-destructive text-center">
                {{ $error }}
            </div>
        @endif

        {{-- Current Plan Section --}}
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-foreground mb-3">{{ __('billing.current_plan') }}</h2>
            <div class="p-5 bg-card border border-border rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-bold text-foreground text-lg">{{ $planDetails['name'] ?? ucfirst($currentPlan) }}</p>
                        @if($currentPlan !== 'free' && $subscriptionStatus === 'active')
                            <p class="text-sm text-muted-foreground mt-1">{{ __('billing.renews_on', ['date' => $renewDate]) }}</p>
                        @elseif($currentPlan !== 'free' && $subscriptionStatus === 'canceling')
                            <p class="text-sm text-orange-500 mt-1">{{ __('billing.ends_on', ['date' => $renewDate]) }}</p>
                        @else
                            <p class="text-sm text-muted-foreground mt-1">{{ __('billing.free_plan_desc') }}</p>
                        @endif
                    </div>
                    <a href="{{ route('pricing') }}"
                       class="px-5 py-2 border border-border rounded-lg text-sm font-medium text-foreground hover:bg-secondary transition-colors">
                        @if($currentPlan === 'free')
                            {{ __('billing.view_plans') }}
                        @else
                            {{ __('billing.manage_plan') }}
                        @endif
                    </a>
                </div>
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-border mb-6"></div>

        {{-- Payment Method Section --}}
        <div class="mb-6" x-data="{
            editing: false,
            stripe: null,
            cardElement: null,
            processing: false,
            cardError: null
        }">
            <h2 class="text-lg font-semibold text-foreground mb-3">{{ __('billing.payment') }}</h2>
            <div class="p-5 bg-card border border-border rounded-xl">
                @if($hasPaymentMethod && $paymentMethod)
                    {{-- Saved card display --}}
                    <div x-show="!editing">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-8 bg-secondary rounded border border-border flex items-center justify-center">
                                    @if(strtolower($paymentMethod['brand']) === 'visa')
                                        <span class="text-blue-600 font-bold text-xs italic">VISA</span>
                                    @elseif(strtolower($paymentMethod['brand']) === 'mastercard')
                                        <div class="flex" style="margin-left: -2px;">
                                            <div class="w-3 h-3 rounded-full" style="background-color: #EB001B;"></div>
                                            <div class="w-3 h-3 rounded-full" style="background-color: #F79E1B; margin-left: -4px;"></div>
                                        </div>
                                    @else
                                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-foreground font-medium">{{ $paymentMethod['brand'] }} &bull;&bull;&bull;&bull; {{ $paymentMethod['last4'] }}</span>
                                    <span class="text-xs text-muted-foreground">{{ $paymentMethod['exp_month'] }}/{{ $paymentMethod['exp_year'] }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="editing = true; $nextTick(() => {
                                    if (!stripe) {
                                        stripe = Stripe('{{ config('services.stripe.key') }}');
                                    }
                                    const container = document.getElementById('billing-card-element');
                                    if (container) {
                                        container.innerHTML = '';
                                        const elements = stripe.elements({
                                            appearance: {
                                                theme: 'stripe',
                                                variables: {
                                                    colorPrimary: '#8B5CF6',
                                                    colorBackground: '#ffffff',
                                                    colorText: '#1a1a1a',
                                                    colorDanger: '#ef4444',
                                                    fontFamily: 'Inter, system-ui, sans-serif',
                                                    borderRadius: '8px',
                                                },
                                            },
                                        });
                                        cardElement = elements.create('card', {
                                            hidePostalCode: true,
                                            style: {
                                                base: {
                                                    fontSize: '16px',
                                                    color: '#1a1a1a',
                                                    '::placeholder': { color: '#9ca3af' },
                                                },
                                            },
                                        });
                                        cardElement.mount('#billing-card-element');
                                        cardElement.on('change', (event) => {
                                            cardError = event.error ? event.error.message : null;
                                        });
                                    }
                                })"
                                        class="px-4 py-2 border border-border rounded-lg text-sm font-medium text-foreground hover:bg-secondary transition-colors">
                                    {{ __('billing.update') }}
                                </button>
                                <button wire:click="removePaymentMethod"
                                        wire:confirm="{{ __('billing.confirm_remove_card') }}"
                                        class="px-4 py-2 border border-destructive/30 rounded-lg text-sm font-medium text-destructive hover:bg-destructive/5 transition-colors">
                                    {{ __('billing.remove') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Edit card form --}}
                    <div x-show="editing" x-cloak>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-medium text-foreground">{{ __('billing.update_card') }}</span>
                            <button @click="editing = false" class="text-xs text-muted-foreground hover:text-foreground">
                                {{ __('pricing.cancel') }}
                            </button>
                        </div>
                        <div id="billing-card-element" class="p-3 bg-background border border-border rounded-lg min-h-[44px]"></div>
                        <div x-show="cardError" x-text="cardError" class="text-destructive text-sm mt-2"></div>
                        <div class="mt-3 flex justify-end">
                            <button @click="async () => {
                                if (processing || !cardElement) return;
                                processing = true;
                                cardError = null;
                                try {
                                    const { paymentMethod: pm, error: pmError } = await stripe.createPaymentMethod({
                                        type: 'card',
                                        card: cardElement,
                                    });
                                    if (pmError) {
                                        cardError = pmError.message;
                                        processing = false;
                                        return;
                                    }
                                    const response = await fetch('/subscription/update-payment-method', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                                            'Accept': 'application/json',
                                        },
                                        body: JSON.stringify({ payment_method_id: pm.id }),
                                    });
                                    const data = await response.json();
                                    if (data.error) {
                                        cardError = data.error;
                                        processing = false;
                                        return;
                                    }
                                    if (data.success) {
                                        window.location.reload();
                                    }
                                } catch (e) {
                                    cardError = 'An unexpected error occurred.';
                                }
                                processing = false;
                            }"
                                    :disabled="processing"
                                    class="px-4 py-2 bg-primary text-primary-foreground text-sm font-medium rounded-lg hover:bg-primary/90 transition-colors disabled:opacity-50">
                                <span x-show="!processing">{{ __('billing.save_card') }}</span>
                                <span x-show="processing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    {{ __('billing.saving') }}
                                </span>
                            </button>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-muted-foreground">{{ __('billing.no_payment_method') }}</p>
                @endif
            </div>
        </div>

        {{-- Divider --}}
        <div class="border-t border-border mb-6"></div>

        {{-- Payment History Section --}}
        <div>
            <h2 class="text-lg font-semibold text-foreground mb-3">{{ __('billing.payment_history') }}</h2>
            @if($paymentHistory->count() > 0)
                <div class="bg-card border border-border rounded-xl overflow-hidden">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th class="text-left px-5 py-3 text-sm font-medium text-muted-foreground">{{ __('billing.date') }}</th>
                                <th class="text-left px-5 py-3 text-sm font-medium text-muted-foreground">{{ __('billing.description') }}</th>
                                <th class="text-left px-5 py-3 text-sm font-medium text-muted-foreground">{{ __('billing.total') }}</th>
                                <th class="text-left px-5 py-3 text-sm font-medium text-muted-foreground">{{ __('billing.status') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentHistory as $purchase)
                                <tr class="border-b border-border last:border-b-0">
                                    <td class="px-5 py-3 text-sm text-foreground">{{ $purchase->created_at->format('M j, Y') }}</td>
                                    <td class="px-5 py-3 text-sm text-foreground">{{ __('billing.credit_pack_purchase', ['credits' => $purchase->credits]) }}</td>
                                    <td class="px-5 py-3 text-sm text-foreground">{{ $purchase->formatted_price }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                            {{ __('billing.paid') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-5 bg-card border border-border rounded-xl">
                    <p class="text-sm text-muted-foreground">{{ __('billing.no_payment_history') }}</p>
                </div>
            @endif
        </div>

    </div>
</div>
