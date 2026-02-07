{{-- Trial Activation Modal --}}
<div>
    {{-- Header --}}
    <div class="text-center mb-6">
        <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-primary/20 to-purple-500/20 rounded-full flex items-center justify-center">
            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
            </svg>
        </div>
        <h2 class="text-xl font-bold text-foreground">{{ __('pricing.activate_trial_title') }}</h2>
        <p class="text-muted-foreground mt-1">{{ __('pricing.activate_trial_subtitle') }}</p>
    </div>

    {{-- Card Input --}}
    <div class="space-y-4">
        <label class="block text-sm font-medium text-foreground">{{ __('pricing.enter_card') }}</label>
        <div wire:ignore id="card-element" class="p-4 bg-background border border-border rounded-lg min-h-[44px]"></div>

        {{-- Error Display --}}
        <div x-show="error" x-text="error" class="text-destructive text-sm"></div>
        @if($error)
            <div class="text-destructive text-sm">{{ $error }}</div>
        @endif
    </div>

    {{-- Benefits List --}}
    <div class="mt-6 p-4 bg-green-500/10 border border-green-500/30 rounded-lg">
        <ul class="space-y-2 text-sm">
            <li class="flex items-center gap-2 text-foreground">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                {{ __('pricing.trial_benefit_1') }}
            </li>
            <li class="flex items-center gap-2 text-foreground">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                {{ __('pricing.trial_benefit_2') }}
            </li>
            <li class="flex items-center gap-2 text-foreground">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                </svg>
                {{ __('pricing.trial_benefit_3') }}
            </li>
        </ul>
    </div>

    {{-- Buttons --}}
    <div class="mt-6 flex gap-3">
        <button wire:click="closeModal"
            class="flex-1 py-3 border border-border rounded-lg hover:bg-muted transition-colors text-foreground font-medium">
            {{ __('pricing.cancel') }}
        </button>
        <button @click="activateTrial()"
            :disabled="processing"
            class="flex-1 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!processing">{{ __('pricing.activate_trial') }}</span>
            <span x-show="processing" class="flex items-center justify-center gap-2">
                <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ __('pricing.validating') }}
            </span>
        </button>
    </div>
</div>
