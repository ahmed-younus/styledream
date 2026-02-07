{{-- Downgrade Modal (Premium -> Pro) --}}
<div>
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900">{{ __('pricing.downgrade_to', ['plan' => $planDetails['name'] ?? ucfirst($targetPlan)]) }}</h2>
    </div>

    {{-- Plan Comparison --}}
    <div class="grid grid-cols-2 gap-3 mb-6">
        {{-- Current Plan --}}
        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
            <span class="text-xs text-gray-500 uppercase tracking-wider">{{ __('pricing.current') }}</span>
            <p class="font-semibold text-gray-900 mt-1">{{ ucfirst($currentPlan) }}</p>
            <p class="text-sm text-gray-600">{{ $currentPlanPrice }}{{ __('pricing.per_month') }}</p>
        </div>
        {{-- New Plan --}}
        <div class="p-4 bg-primary/10 rounded-lg border-2 border-primary">
            <span class="text-xs text-primary uppercase tracking-wider font-medium">{{ __('pricing.new') }}</span>
            <p class="font-semibold text-gray-900 mt-1">{{ $planDetails['name'] ?? ucfirst($targetPlan) }}</p>
            <p class="text-sm text-gray-600">{{ $targetPlanPrice }}{{ __('pricing.per_month') }}</p>
        </div>
    </div>

    {{-- Info Notice --}}
    <div class="p-4 bg-gray-50 border border-gray-200 rounded-lg mb-6">
        <p class="text-sm text-gray-900">
            <span class="font-semibold">{{ __('pricing.plan_changes_on', ['plan' => ucfirst($targetPlan), 'date' => $effectiveDate]) }}</span>
        </p>
        <p class="text-sm text-gray-600 mt-2">
            {{ __('pricing.keep_benefits_until', ['plan' => ucfirst($currentPlan)]) }}
        </p>
        <p class="text-sm text-gray-600 mt-2">
            {{ __('pricing.charged_after', ['amount' => $targetPlanPrice, 'plan' => ucfirst($targetPlan)]) }}
        </p>
    </div>

    {{-- Error Display --}}
    @if($error)
        <div class="text-red-500 text-sm mb-4">{{ $error }}</div>
    @endif

    {{-- Buttons --}}
    <div class="flex gap-3">
        <button wire:click="closeModal"
            class="flex-1 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-gray-700 font-medium">
            {{ __('pricing.keep_plan', ['plan' => ucfirst($currentPlan)]) }}
        </button>
        <button wire:click="processDowngrade"
            wire:loading.attr="disabled"
            class="flex-1 py-3 bg-gray-900 text-white rounded-lg hover:bg-gray-800 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
            <span wire:loading.remove wire:target="processDowngrade">{{ __('pricing.downgrade_to', ['plan' => $planDetails['name'] ?? ucfirst($targetPlan)]) }}</span>
            <span wire:loading wire:target="processDowngrade" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('pricing.processing') }}
            </span>
        </button>
    </div>
</div>
