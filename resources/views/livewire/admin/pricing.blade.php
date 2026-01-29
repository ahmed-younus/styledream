<div>
    @if(session('success'))
        <div class="mb-6 p-4 bg-[#30b566]/10 border border-[#30b566]/20 rounded-lg">
            <p class="text-[#30b566] text-sm">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 p-4 bg-[#f5a623]/10 border border-[#f5a623]/20 rounded-lg">
            <p class="text-[#f5a623] text-sm">{{ session('warning') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-[#df1b41]/10 border border-[#df1b41]/20 rounded-lg">
            <p class="text-[#df1b41] text-sm">{{ session('error') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Subscription Plans --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <h3 class="text-base font-semibold text-[#1a1f36]">Subscription Plans</h3>
                <div class="flex gap-2">
                    <button wire:click="syncAllWithStripe"
                            wire:loading.attr="disabled"
                            wire:target="syncAllWithStripe"
                            class="flex-1 sm:flex-initial px-3 py-1.5 bg-[#697386] text-white rounded-lg hover:bg-[#5a6172] transition-colors text-sm font-medium flex items-center justify-center gap-1 disabled:opacity-50">
                        <svg wire:loading wire:target="syncAllWithStripe" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <svg wire:loading.remove wire:target="syncAllWithStripe" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99"/>
                        </svg>
                        <span class="hidden xs:inline">Sync</span> Stripe
                    </button>
                    <button wire:click="openPlanModal()"
                            class="flex-1 sm:flex-initial px-3 py-1.5 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                        + Add Plan
                    </button>
                </div>
            </div>

            <div class="space-y-4">
                @forelse($plans as $key => $plan)
                    <div class="p-4 border border-[#e3e8ee] rounded-lg hover:border-[#d0d5dd] transition-colors relative">
                        @if(!empty($plan['badge']))
                            <span class="absolute -top-2 left-4 px-2 py-0.5 bg-[#635bff] text-white text-xs font-medium rounded">
                                {{ strtoupper($plan['badge']) }}
                            </span>
                        @endif

                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 mb-2">
                            <div>
                                <h4 class="font-medium text-[#1a1f36] text-sm">{{ $plan['name'] ?? ucfirst($key) }}</h4>
                                @if(!empty($plan['description']))
                                    <p class="text-xs text-[#697386] mt-0.5">{{ $plan['description'] }}</p>
                                @endif
                            </div>
                            <div class="text-left sm:text-right">
                                @php
                                    $usdPrice = $this->getPlanPrice($plan, 'usd');
                                    $gbpPrice = $this->getPlanPrice($plan, 'gbp');
                                    $eurPrice = $this->getPlanPrice($plan, 'eur');
                                @endphp
                                @if($usdPrice > 0 || $gbpPrice > 0 || $eurPrice > 0)
                                    <div class="text-xs sm:text-sm font-semibold flex flex-wrap gap-1 sm:gap-0">
                                        <span class="text-[#1a1f36]">${{ number_format($usdPrice / 100, 2) }}</span>
                                        <span class="text-[#e3e8ee] hidden sm:inline">|</span>
                                        <span class="text-[#697386]">£{{ number_format($gbpPrice / 100, 2) }}</span>
                                        <span class="text-[#e3e8ee] hidden sm:inline">|</span>
                                        <span class="text-[#697386]">€{{ number_format($eurPrice / 100, 2) }}</span>
                                        <span class="text-xs text-[#697386] ml-1">/mo</span>
                                    </div>
                                @else
                                    <span class="text-base font-semibold text-[#1a1f36]">Free</span>
                                @endif
                            </div>
                        </div>

                        <div class="text-sm text-[#697386] space-y-1 mb-3">
                            <p>Credits: {{ $plan['credits_per_month'] ?? 0 }}/month</p>
                            @if(!empty($plan['features']))
                                <div class="mt-2 space-y-1">
                                    @foreach(array_slice($plan['features'], 0, 3) as $feature)
                                        <p class="text-xs flex items-center gap-1">
                                            <svg class="w-3 h-3 text-[#30b566]" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $feature }}
                                        </p>
                                    @endforeach
                                    @if(count($plan['features'] ?? []) > 3)
                                        <p class="text-xs text-[#697386]">+{{ count($plan['features']) - 3 }} more...</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <button wire:click="openPlanModal('{{ $key }}')"
                                    class="flex-1 px-3 py-2 bg-[#f6f8fa] text-[#1a1f36] rounded-lg hover:bg-[#e3e8ee] transition-colors text-sm font-medium">
                                Edit
                            </button>
                            <button wire:click="deletePlan('{{ $key }}')"
                                    wire:confirm="Delete '{{ $plan['name'] ?? $key }}' plan?"
                                    class="px-3 py-2 bg-[#df1b41]/10 text-[#df1b41] rounded-lg hover:bg-[#df1b41]/20 transition-colors text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-[#697386] text-center py-8">No plans yet. Click "Add Plan" to create one.</p>
                @endforelse
            </div>
        </div>

        {{-- Credit Packs --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-4 sm:p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-base font-semibold text-[#1a1f36]">Credit Packs</h3>
                <button wire:click="openPackModal()"
                        class="px-3 py-1.5 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                    + Add Pack
                </button>
            </div>

            <div class="space-y-4">
                @forelse($creditPacks as $key => $pack)
                    <div class="p-4 border border-[#e3e8ee] rounded-lg hover:border-[#d0d5dd] transition-colors relative">
                        @if($pack['popular'] ?? false)
                            <span class="absolute -top-2 left-4 px-2 py-0.5 bg-[#635bff] text-white text-xs font-medium rounded">POPULAR</span>
                        @elseif($pack['best_value'] ?? false)
                            <span class="absolute -top-2 left-4 px-2 py-0.5 bg-[#30b566] text-white text-xs font-medium rounded">BEST VALUE</span>
                        @endif

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                            <h4 class="font-semibold text-[#1a1f36]">{{ $pack['credits'] ?? 0 }} Credits</h4>
                            <div class="text-left sm:text-right">
                                @php
                                    $usdPrice = $this->getPackPrice($pack, 'usd');
                                    $gbpPrice = $this->getPackPrice($pack, 'gbp');
                                    $eurPrice = $this->getPackPrice($pack, 'eur');
                                @endphp
                                <div class="text-xs sm:text-sm font-bold flex flex-wrap gap-1 sm:gap-0">
                                    <span class="text-[#1a1f36]">${{ number_format($usdPrice / 100, 2) }}</span>
                                    <span class="text-[#e3e8ee] hidden sm:inline">|</span>
                                    <span class="text-[#697386]">£{{ number_format($gbpPrice / 100, 2) }}</span>
                                    <span class="text-[#e3e8ee] hidden sm:inline">|</span>
                                    <span class="text-[#697386]">€{{ number_format($eurPrice / 100, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button wire:click="openPackModal('{{ $key }}')"
                                    class="flex-1 px-3 py-2 bg-[#f6f8fa] text-[#1a1f36] rounded-lg hover:bg-[#e3e8ee] transition-colors text-sm font-medium">
                                Edit
                            </button>
                            <button wire:click="deletePack('{{ $key }}')"
                                    wire:confirm="Delete {{ $pack['credits'] ?? 0 }} credits pack?"
                                    class="px-3 py-2 bg-[#df1b41]/10 text-[#df1b41] rounded-lg hover:bg-[#df1b41]/20 transition-colors text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-[#697386] text-center py-8">No credit packs yet. Click "Add Pack" to create one.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Reset Button --}}
    <div class="mt-6 flex justify-end">
        <button wire:click="resetToDefaults"
                wire:confirm="Are you sure? This will reset all pricing to default config values."
                class="px-4 py-2 bg-[#f6f8fa] text-[#1a1f36] rounded-lg hover:bg-[#e3e8ee] transition-colors text-sm">
            Reset to Defaults
        </button>
    </div>

    {{-- Info Box --}}
    <div class="mt-4 p-4 bg-[#f6f8fa] border border-[#e3e8ee] rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-[#697386] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-[#697386]">
                <p class="font-semibold mb-1 text-[#1a1f36]">Auto Stripe Sync</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    <li><strong>Subscription Plans:</strong> Stripe Prices are automatically created when you save. Change price = new Stripe Price created.</li>
                    <li><strong>Credit Packs:</strong> Dynamic pricing - changes apply instantly (no Stripe Price IDs needed).</li>
                    <li>Click <strong>"Sync Stripe"</strong> to recreate all Stripe prices at once.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Plan Modal --}}
    @if($showPlanModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col border border-[#e3e8ee]">
                <div class="p-5 border-b border-[#e3e8ee] flex-shrink-0">
                    <h3 class="text-base font-semibold text-[#1a1f36]">
                        {{ $isNewPlan ? 'Create New Plan' : 'Edit Plan' }}
                    </h3>
                </div>

                <form wire:submit="savePlan" class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-5 space-y-4 overflow-y-auto flex-1">
                        @if($isNewPlan)
                            <div>
                                <label class="block text-sm font-medium text-[#1a1f36] mb-1">Plan Key (unique identifier)</label>
                                <input wire:model="planKey" type="text"
                                       class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                       placeholder="e.g., starter, pro, enterprise">
                                @error('planKey') <span class="text-sm text-[#df1b41]">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-[#1a1f36] mb-1">Plan Name</label>
                                <input wire:model="planName" type="text"
                                       class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                       placeholder="Pro">
                                @error('planName') <span class="text-sm text-[#df1b41]">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-[#1a1f36] mb-1">Badge (optional)</label>
                                <input wire:model="planBadge" type="text"
                                       class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                       placeholder="e.g., POPULAR">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Description</label>
                            <input wire:model="planDescription" type="text"
                                   class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                   placeholder="For fashion enthusiasts">
                        </div>

                        {{-- Multi-Currency Prices --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-2">Prices (in cents, 0 = free)</label>
                            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-bold text-[#1a1f36]">$ USD</span>
                                    </div>
                                    <input wire:model.live="planPrices.usd" type="number" min="0"
                                           class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                           placeholder="999">
                                    <p class="text-xs text-[#697386] mt-1">= ${{ number_format(($planPrices['usd'] ?? 0) / 100, 2) }}</p>
                                    @error('planPrices.usd') <span class="text-xs text-[#df1b41]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-bold text-[#697386]">£ GBP</span>
                                    </div>
                                    <input wire:model.live="planPrices.gbp" type="number" min="0"
                                           class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                           placeholder="799">
                                    <p class="text-xs text-[#697386] mt-1">= £{{ number_format(($planPrices['gbp'] ?? 0) / 100, 2) }}</p>
                                    @error('planPrices.gbp') <span class="text-xs text-[#df1b41]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-bold text-[#697386]">€ EUR</span>
                                    </div>
                                    <input wire:model.live="planPrices.eur" type="number" min="0"
                                           class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                           placeholder="899">
                                    <p class="text-xs text-[#697386] mt-1">= €{{ number_format(($planPrices['eur'] ?? 0) / 100, 2) }}</p>
                                    @error('planPrices.eur') <span class="text-xs text-[#df1b41]">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Credits/Month</label>
                            <input wire:model="planCredits" type="number" min="0"
                                   class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                   placeholder="100">
                            @error('planCredits') <span class="text-sm text-[#df1b41]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Stripe Price IDs (Auto-generated) --}}
                        @if(($planPrices['usd'] ?? 0) > 0 || ($planPrices['gbp'] ?? 0) > 0 || ($planPrices['eur'] ?? 0) > 0)
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-[#1a1f36]">Stripe Price IDs</label>
                                    <span class="text-xs text-[#30b566] flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Auto-synced with Stripe
                                    </span>
                                </div>
                                <div class="space-y-2">
                                    @if(($planPrices['usd'] ?? 0) > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-[#1a1f36] w-12">USD</span>
                                            <input wire:model="planStripePriceIds.usd" type="text" readonly
                                                   class="flex-1 px-3 py-2 bg-[#f6f8fa] border border-[#e3e8ee] rounded-lg text-[#697386] font-mono text-xs cursor-not-allowed"
                                                   placeholder="Will be auto-generated">
                                        </div>
                                    @endif
                                    @if(($planPrices['gbp'] ?? 0) > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-[#697386] w-12">GBP</span>
                                            <input wire:model="planStripePriceIds.gbp" type="text" readonly
                                                   class="flex-1 px-3 py-2 bg-[#f6f8fa] border border-[#e3e8ee] rounded-lg text-[#697386] font-mono text-xs cursor-not-allowed"
                                                   placeholder="Will be auto-generated">
                                        </div>
                                    @endif
                                    @if(($planPrices['eur'] ?? 0) > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-[#697386] w-12">EUR</span>
                                            <input wire:model="planStripePriceIds.eur" type="text" readonly
                                                   class="flex-1 px-3 py-2 bg-[#f6f8fa] border border-[#e3e8ee] rounded-lg text-[#697386] font-mono text-xs cursor-not-allowed"
                                                   placeholder="Will be auto-generated">
                                        </div>
                                    @endif
                                </div>
                                <p class="text-xs text-[#697386] mt-2">Stripe prices are automatically created when you save the plan.</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Button Text</label>
                            <input wire:model="planButtonText" type="text"
                                   class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                   placeholder="e.g., Upgrade to Pro, Get Started">
                        </div>

                        {{-- Features --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-[#1a1f36]">Features</label>
                                <button type="button" wire:click="addFeature"
                                        class="text-xs text-[#635bff] hover:text-[#5248f0] font-medium">+ Add Feature</button>
                            </div>
                            <div class="space-y-2">
                                @foreach($planFeatures as $index => $feature)
                                    <div class="flex gap-2">
                                        <input wire:model="planFeatures.{{ $index }}" type="text"
                                               class="flex-1 px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] text-sm focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                               placeholder="Feature description">
                                        @if(count($planFeatures) > 1)
                                            <button type="button" wire:click="removeFeature({{ $index }})"
                                                    class="px-2 text-[#df1b41] hover:text-[#c91839]">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="p-5 border-t border-[#e3e8ee] flex justify-end gap-3 flex-shrink-0">
                        <button type="button" wire:click="closePlanModal"
                                class="px-4 py-2 text-[#697386] hover:text-[#1a1f36] hover:bg-[#f6f8fa] rounded-lg transition-colors text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                            {{ $isNewPlan ? 'Create Plan' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Pack Modal --}}
    @if($showPackModal)
        <div class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg max-w-lg w-full border border-[#e3e8ee]">
                <div class="p-5 border-b border-[#e3e8ee]">
                    <h3 class="text-base font-semibold text-[#1a1f36]">
                        {{ $isNewPack ? 'Create Credit Pack' : 'Edit Credit Pack' }}
                    </h3>
                </div>

                <form wire:submit="savePack">
                    <div class="p-5 space-y-4">
                        @if($isNewPack)
                            <div>
                                <label class="block text-sm font-medium text-[#1a1f36] mb-1">Pack Key (unique)</label>
                                <input wire:model="packKey" type="text"
                                       class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                       placeholder="e.g., small, medium, large">
                                @error('packKey') <span class="text-sm text-[#df1b41]">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Credits</label>
                            <input wire:model.live="packCredits" type="number" min="1"
                                   class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                   placeholder="50">
                            @error('packCredits') <span class="text-sm text-[#df1b41]">{{ $message }}</span> @enderror
                        </div>

                        {{-- Multi-Currency Prices --}}
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-2">Prices (in cents)</label>
                            <div class="grid grid-cols-3 gap-2 sm:gap-3">
                                <div>
                                    <div class="flex items-center gap-1 mb-1">
                                        <span class="text-xs font-bold text-[#1a1f36]">$ USD</span>
                                    </div>
                                    <input wire:model.live="packPrices.usd" type="number" min="1"
                                           class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                           placeholder="999">
                                    <p class="text-xs text-[#697386] mt-1">${{ number_format(($packPrices['usd'] ?? 0) / 100, 2) }}</p>
                                    @error('packPrices.usd') <span class="text-xs text-[#df1b41]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <div class="flex items-center gap-1 mb-1">
                                        <span class="text-xs font-bold text-[#697386]">£ GBP</span>
                                    </div>
                                    <input wire:model.live="packPrices.gbp" type="number" min="1"
                                           class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                           placeholder="799">
                                    <p class="text-xs text-[#697386] mt-1">£{{ number_format(($packPrices['gbp'] ?? 0) / 100, 2) }}</p>
                                    @error('packPrices.gbp') <span class="text-xs text-[#df1b41]">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <div class="flex items-center gap-1 mb-1">
                                        <span class="text-xs font-bold text-[#697386]">€ EUR</span>
                                    </div>
                                    <input wire:model.live="packPrices.eur" type="number" min="1"
                                           class="w-full px-3 py-2 bg-white border border-[#e3e8ee] rounded-lg text-[#1a1f36] focus:outline-none focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] transition-colors"
                                           placeholder="899">
                                    <p class="text-xs text-[#697386] mt-1">€{{ number_format(($packPrices['eur'] ?? 0) / 100, 2) }}</p>
                                    @error('packPrices.eur') <span class="text-xs text-[#df1b41]">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        @if($packCredits > 0 && ($packPrices['usd'] ?? 0) > 0)
                            <div class="p-3 bg-[#f6f8fa] rounded-lg text-center">
                                <p class="text-sm text-[#697386]">
                                    <span class="font-bold text-[#1a1f36]">${{ number_format(($packPrices['usd'] ?? 0) / 100 / $packCredits, 3) }}/credit</span>
                                    <span class="text-[#e3e8ee] mx-1">|</span>
                                    <span class="font-bold text-[#697386]">£{{ number_format(($packPrices['gbp'] ?? 0) / 100 / $packCredits, 3) }}/credit</span>
                                    <span class="text-[#e3e8ee] mx-1">|</span>
                                    <span class="font-bold text-[#697386]">€{{ number_format(($packPrices['eur'] ?? 0) / 100 / $packCredits, 3) }}/credit</span>
                                </p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-2">Badge</label>
                            <div class="flex gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model="packBadge" value="" class="text-[#635bff] focus:ring-[#635bff]">
                                    <span class="text-sm text-[#1a1f36]">None</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model="packBadge" value="popular" class="text-[#635bff] focus:ring-[#635bff]">
                                    <span class="text-sm text-[#1a1f36] font-medium">Popular</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model="packBadge" value="best_value" class="text-[#30b566] focus:ring-[#30b566]">
                                    <span class="text-sm text-[#30b566] font-medium">Best Value</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="p-5 border-t border-[#e3e8ee] flex justify-end gap-3">
                        <button type="button" wire:click="closePackModal"
                                class="px-4 py-2 text-[#697386] hover:text-[#1a1f36] hover:bg-[#f6f8fa] rounded-lg transition-colors text-sm font-medium">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                            {{ $isNewPack ? 'Create Pack' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
