{{-- New Subscription Modal (Free -> Pro/Premium) --}}
<div class="text-gray-900">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold">{{ __('pricing.subscribe_to', ['plan' => $planDetails['name'] ?? ucfirst($targetPlan)]) }}</h2>
    </div>

    {{-- Plan Details --}}
    <div class="p-4 bg-gray-50 rounded-lg mb-6 border border-gray-200">
        <div class="flex items-center justify-between mb-3">
            <span class="font-semibold">{{ $planDetails['name'] ?? ucfirst($targetPlan) }} Plan</span>
            <span class="text-lg font-bold">{{ $targetPlanPrice }}<span class="text-sm text-gray-500 font-normal">{{ __('pricing.per_month') }}</span></span>
        </div>
        <div class="border-t border-gray-200 pt-3">
            <ul class="space-y-2">
                @foreach($planDetails['features'] ?? [] as $feature)
                    <li class="flex items-center gap-2 text-sm">
                        <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        {{ $feature }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    {{-- Payment Details --}}
    <div class="space-y-3">
        <label class="block text-sm font-medium">{{ __('pricing.payment_details') }}</label>
        <div wire:ignore id="card-element" class="p-4 bg-white border border-gray-300 rounded-lg min-h-[44px]"></div>

        {{-- Error Display --}}
        <div x-show="error" x-text="error" class="text-red-500 text-sm"></div>
        @if($error)
            <div class="text-red-500 text-sm">{{ $error }}</div>
        @endif
    </div>

    {{-- Total --}}
    <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <div class="flex items-center justify-between">
            <span class="font-medium">{{ __('pricing.total_due_today') }}</span>
            <span class="text-lg font-bold">{{ $targetPlanPrice }}</span>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            {{ __('pricing.renews_monthly', ['amount' => $targetPlanPrice]) }}
        </p>
    </div>

    {{-- Buttons --}}
    <div class="mt-6 flex gap-3">
        <button wire:click="closeModal"
            class="flex-1 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors font-medium">
            {{ __('pricing.cancel') }}
        </button>
        <button @click="createSubscription('{{ $targetPlan }}', '{{ $currency }}')"
            :disabled="processing"
            class="flex-1 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!processing">{{ __('pricing.subscribe_to', ['plan' => $planDetails['name'] ?? ucfirst($targetPlan)]) }}</span>
            <span x-show="processing" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('pricing.processing') }}
            </span>
        </button>
    </div>

    {{-- Secure Payment Notice --}}
    <div class="mt-4 flex items-center justify-center gap-2 text-xs text-gray-500">
        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
        {{ __('pricing.secure_payment') }}
    </div>
</div>
