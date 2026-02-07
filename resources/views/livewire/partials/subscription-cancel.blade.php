{{-- Cancel Subscription Modal (Pro/Premium -> Free) --}}
<div>
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">{{ __('pricing.cancel_subscription') }}</h2>
    </div>

    {{-- Warning Notice --}}
    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg mb-6">
        <p class="font-semibold text-gray-900">{{ __('pricing.cancel_confirm_modal') }}</p>
        <p class="text-sm text-gray-600 mt-2">
            {{ __('pricing.subscription_ends_on', ['plan' => ucfirst($currentPlan), 'date' => $effectiveDate]) }}
        </p>

        <div class="mt-4 text-sm text-gray-600">
            <p class="font-medium text-gray-900">{{ __('pricing.after_cancel_heading') }}</p>
            <ul class="mt-2 space-y-2">
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('pricing.after_cancel_free') }}
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('pricing.after_cancel_credits') }}
                </li>
                <li class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    {{ __('pricing.after_cancel_quality') }}
                </li>
            </ul>
        </div>

        <p class="text-sm text-gray-500 mt-4">
            {{ __('pricing.resubscribe_anytime') }}
        </p>
    </div>

    {{-- Error Display --}}
    @if($error)
        <div class="text-destructive text-sm mb-4">{{ $error }}</div>
    @endif

    {{-- Buttons --}}
    <div class="flex gap-3">
        <button wire:click="closeModal"
            class="flex-1 py-3 text-white rounded-lg hover:opacity-90 transition-colors font-semibold"
            style="background-color: #18181b;">
            {{ __('pricing.keep_subscription') }}
        </button>
        <button wire:click="processCancel"
            wire:loading.attr="disabled"
            class="flex-1 py-3 text-white rounded-lg hover:opacity-90 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed"
            style="background-color: #ef4444;">
            <span wire:loading.remove wire:target="processCancel">{{ __('pricing.cancel_go_free') }}</span>
            <span wire:loading wire:target="processCancel" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('pricing.processing') }}
            </span>
        </button>
    </div>
</div>
