<div>
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
            <p class="text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Subscription Plans --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Subscription Plans</h3>
                <button wire:click="openPlanModal()"
                        class="px-3 py-1.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm font-medium">
                    + Add Plan
                </button>
            </div>

            <div class="space-y-4">
                @forelse($plans as $key => $plan)
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-purple-300 dark:hover:border-purple-700 transition-colors relative">
                        @if(!empty($plan['badge']))
                            <span class="absolute -top-2 left-4 px-2 py-0.5 bg-purple-600 text-white text-xs font-medium rounded">
                                {{ strtoupper($plan['badge']) }}
                            </span>
                        @endif

                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-white">{{ $plan['name'] ?? ucfirst($key) }}</h4>
                                @if(!empty($plan['description']))
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $plan['description'] }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                @php
                                    $usdPrice = $this->getPlanPrice($plan, 'usd');
                                    $gbpPrice = $this->getPlanPrice($plan, 'gbp');
                                    $eurPrice = $this->getPlanPrice($plan, 'eur');
                                @endphp
                                @if($usdPrice > 0 || $gbpPrice > 0 || $eurPrice > 0)
                                    <div class="text-sm font-bold text-purple-600">
                                        ${{ number_format($usdPrice / 100, 2) }} |
                                        <span class="text-blue-600">£{{ number_format($gbpPrice / 100, 2) }}</span> |
                                        <span class="text-green-600">€{{ number_format($eurPrice / 100, 2) }}</span>
                                    </div>
                                    <p class="text-xs text-gray-400">/month</p>
                                @else
                                    <span class="text-lg font-bold text-purple-600">Free</span>
                                @endif
                            </div>
                        </div>

                        <div class="text-sm text-gray-500 space-y-1 mb-3">
                            <p>Credits: {{ $plan['credits_per_month'] ?? 0 }}/month</p>
                            @if(!empty($plan['features']))
                                <div class="mt-2 space-y-1">
                                    @foreach(array_slice($plan['features'], 0, 3) as $feature)
                                        <p class="text-xs flex items-center gap-1">
                                            <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            {{ $feature }}
                                        </p>
                                    @endforeach
                                    @if(count($plan['features'] ?? []) > 3)
                                        <p class="text-xs text-gray-400">+{{ count($plan['features']) - 3 }} more...</p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <button wire:click="openPlanModal('{{ $key }}')"
                                    class="flex-1 px-3 py-2 bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-colors text-sm font-medium">
                                Edit
                            </button>
                            <button wire:click="deletePlan('{{ $key }}')"
                                    wire:confirm="Delete '{{ $plan['name'] ?? $key }}' plan?"
                                    class="px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">No plans yet. Click "Add Plan" to create one.</p>
                @endforelse
            </div>
        </div>

        {{-- Credit Packs --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Credit Packs</h3>
                <button wire:click="openPackModal()"
                        class="px-3 py-1.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                    + Add Pack
                </button>
            </div>

            <div class="space-y-4">
                @forelse($creditPacks as $key => $pack)
                    <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-lg hover:border-green-300 dark:hover:border-green-700 transition-colors relative">
                        @if($pack['popular'] ?? false)
                            <span class="absolute -top-2 left-4 px-2 py-0.5 bg-blue-600 text-white text-xs font-medium rounded">POPULAR</span>
                        @elseif($pack['best_value'] ?? false)
                            <span class="absolute -top-2 left-4 px-2 py-0.5 bg-green-600 text-white text-xs font-medium rounded">BEST VALUE</span>
                        @endif

                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-900 dark:text-white">{{ $pack['credits'] ?? 0 }} Credits</h4>
                            <div class="text-right">
                                @php
                                    $usdPrice = $this->getPackPrice($pack, 'usd');
                                    $gbpPrice = $this->getPackPrice($pack, 'gbp');
                                    $eurPrice = $this->getPackPrice($pack, 'eur');
                                @endphp
                                <div class="text-sm font-bold">
                                    <span class="text-purple-600">${{ number_format($usdPrice / 100, 2) }}</span> |
                                    <span class="text-blue-600">£{{ number_format($gbpPrice / 100, 2) }}</span> |
                                    <span class="text-green-600">€{{ number_format($eurPrice / 100, 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button wire:click="openPackModal('{{ $key }}')"
                                    class="flex-1 px-3 py-2 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors text-sm font-medium">
                                Edit
                            </button>
                            <button wire:click="deletePack('{{ $key }}')"
                                    wire:confirm="Delete {{ $pack['credits'] ?? 0 }} credits pack?"
                                    class="px-3 py-2 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-8">No credit packs yet. Click "Add Pack" to create one.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Reset Button --}}
    <div class="mt-6 flex justify-end">
        <button wire:click="resetToDefaults"
                wire:confirm="Are you sure? This will reset all pricing to default config values."
                class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors text-sm">
            Reset to Defaults
        </button>
    </div>

    {{-- Info Box --}}
    <div class="mt-4 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
        <p class="text-sm text-blue-700 dark:text-blue-400">
            <strong>Multi-Currency:</strong> Set prices in USD ($), GBP (£), and EUR (€). Create separate Price IDs in Stripe for each currency.
        </p>
    </div>

    {{-- Plan Modal --}}
    @if($showPlanModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden flex flex-col">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700 flex-shrink-0">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $isNewPlan ? 'Create New Plan' : 'Edit Plan' }}
                    </h3>
                </div>

                <form wire:submit="savePlan" class="flex flex-col flex-1 overflow-hidden">
                    <div class="p-6 space-y-4 overflow-y-auto flex-1">
                        @if($isNewPlan)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan Key (unique identifier)</label>
                                <input wire:model="planKey" type="text"
                                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       placeholder="e.g., starter, pro, enterprise">
                                @error('planKey') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Plan Name</label>
                                <input wire:model="planName" type="text"
                                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       placeholder="Pro">
                                @error('planName') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Badge (optional)</label>
                                <input wire:model="planBadge" type="text"
                                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                       placeholder="e.g., POPULAR">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                            <input wire:model="planDescription" type="text"
                                   class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="For fashion enthusiasts">
                        </div>

                        {{-- Multi-Currency Prices --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prices (in cents, 0 = free)</label>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-bold text-purple-600">$ USD</span>
                                    </div>
                                    <input wire:model.live="planPrices.usd" type="number" min="0"
                                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                           placeholder="999">
                                    <p class="text-xs text-gray-500 mt-1">= ${{ number_format(($planPrices['usd'] ?? 0) / 100, 2) }}</p>
                                    @error('planPrices.usd') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-bold text-blue-600">£ GBP</span>
                                    </div>
                                    <input wire:model.live="planPrices.gbp" type="number" min="0"
                                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="799">
                                    <p class="text-xs text-gray-500 mt-1">= £{{ number_format(($planPrices['gbp'] ?? 0) / 100, 2) }}</p>
                                    @error('planPrices.gbp') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-sm font-bold text-green-600">€ EUR</span>
                                    </div>
                                    <input wire:model.live="planPrices.eur" type="number" min="0"
                                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500"
                                           placeholder="899">
                                    <p class="text-xs text-gray-500 mt-1">= €{{ number_format(($planPrices['eur'] ?? 0) / 100, 2) }}</p>
                                    @error('planPrices.eur') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Credits/Month</label>
                            <input wire:model="planCredits" type="number" min="0"
                                   class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="100">
                            @error('planCredits') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Stripe Price IDs --}}
                        @if(($planPrices['usd'] ?? 0) > 0 || ($planPrices['gbp'] ?? 0) > 0 || ($planPrices['eur'] ?? 0) > 0)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Stripe Price IDs</label>
                                <div class="space-y-2">
                                    @if(($planPrices['usd'] ?? 0) > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-purple-600 w-12">USD</span>
                                            <input wire:model="planStripePriceIds.usd" type="text"
                                                   class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                                                   placeholder="price_xxxxx_usd">
                                        </div>
                                        @error('planStripePriceIds.usd') <span class="text-xs text-red-500 ml-14">{{ $message }}</span> @enderror
                                    @endif
                                    @if(($planPrices['gbp'] ?? 0) > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-blue-600 w-12">GBP</span>
                                            <input wire:model="planStripePriceIds.gbp" type="text"
                                                   class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                                   placeholder="price_xxxxx_gbp">
                                        </div>
                                        @error('planStripePriceIds.gbp') <span class="text-xs text-red-500 ml-14">{{ $message }}</span> @enderror
                                    @endif
                                    @if(($planPrices['eur'] ?? 0) > 0)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xs font-bold text-green-600 w-12">EUR</span>
                                            <input wire:model="planStripePriceIds.eur" type="text"
                                                   class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white font-mono text-sm focus:outline-none focus:ring-2 focus:ring-green-500"
                                                   placeholder="price_xxxxx_eur">
                                        </div>
                                        @error('planStripePriceIds.eur') <span class="text-xs text-red-500 ml-14">{{ $message }}</span> @enderror
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Button Text</label>
                            <input wire:model="planButtonText" type="text"
                                   class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                   placeholder="e.g., Upgrade to Pro, Get Started">
                        </div>

                        {{-- Features --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Features</label>
                                <button type="button" wire:click="addFeature"
                                        class="text-xs text-purple-600 hover:text-purple-700 font-medium">+ Add Feature</button>
                            </div>
                            <div class="space-y-2">
                                @foreach($planFeatures as $index => $feature)
                                    <div class="flex gap-2">
                                        <input wire:model="planFeatures.{{ $index }}" type="text"
                                               class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-purple-500"
                                               placeholder="Feature description">
                                        @if(count($planFeatures) > 1)
                                            <button type="button" wire:click="removeFeature({{ $index }})"
                                                    class="px-2 text-red-500 hover:text-red-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 flex-shrink-0">
                        <button type="button" wire:click="closePlanModal"
                                class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                            {{ $isNewPlan ? 'Create Plan' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Pack Modal --}}
    @if($showPackModal)
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-lg w-full">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $isNewPack ? 'Create Credit Pack' : 'Edit Credit Pack' }}
                    </h3>
                </div>

                <form wire:submit="savePack">
                    <div class="p-6 space-y-4">
                        @if($isNewPack)
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pack Key (unique)</label>
                                <input wire:model="packKey" type="text"
                                       class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="e.g., small, medium, large">
                                @error('packKey') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Credits</label>
                            <input wire:model.live="packCredits" type="number" min="1"
                                   class="w-full px-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500"
                                   placeholder="50">
                            @error('packCredits') <span class="text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Multi-Currency Prices --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prices (in cents)</label>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <div class="flex items-center gap-1 mb-1">
                                        <span class="text-xs font-bold text-purple-600">$ USD</span>
                                    </div>
                                    <input wire:model.live="packPrices.usd" type="number" min="1"
                                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                                           placeholder="999">
                                    <p class="text-xs text-gray-500 mt-1">${{ number_format(($packPrices['usd'] ?? 0) / 100, 2) }}</p>
                                    @error('packPrices.usd') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <div class="flex items-center gap-1 mb-1">
                                        <span class="text-xs font-bold text-blue-600">£ GBP</span>
                                    </div>
                                    <input wire:model.live="packPrices.gbp" type="number" min="1"
                                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="799">
                                    <p class="text-xs text-gray-500 mt-1">£{{ number_format(($packPrices['gbp'] ?? 0) / 100, 2) }}</p>
                                    @error('packPrices.gbp') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <div class="flex items-center gap-1 mb-1">
                                        <span class="text-xs font-bold text-green-600">€ EUR</span>
                                    </div>
                                    <input wire:model.live="packPrices.eur" type="number" min="1"
                                           class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500"
                                           placeholder="899">
                                    <p class="text-xs text-gray-500 mt-1">€{{ number_format(($packPrices['eur'] ?? 0) / 100, 2) }}</p>
                                    @error('packPrices.eur') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        @if($packCredits > 0 && ($packPrices['usd'] ?? 0) > 0)
                            <div class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-center">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    <span class="font-bold text-purple-600">${{ number_format(($packPrices['usd'] ?? 0) / 100 / $packCredits, 3) }}/credit</span>
                                    <span class="text-gray-400 mx-1">|</span>
                                    <span class="font-bold text-blue-600">£{{ number_format(($packPrices['gbp'] ?? 0) / 100 / $packCredits, 3) }}/credit</span>
                                    <span class="text-gray-400 mx-1">|</span>
                                    <span class="font-bold text-green-600">€{{ number_format(($packPrices['eur'] ?? 0) / 100 / $packCredits, 3) }}/credit</span>
                                </p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Badge</label>
                            <div class="flex gap-3">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model="packBadge" value="" class="text-green-600 focus:ring-green-500">
                                    <span class="text-sm text-gray-700 dark:text-gray-300">None</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model="packBadge" value="popular" class="text-blue-600 focus:ring-blue-500">
                                    <span class="text-sm text-blue-600 font-medium">Popular</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model="packBadge" value="best_value" class="text-green-600 focus:ring-green-500">
                                    <span class="text-sm text-green-600 font-medium">Best Value</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                        <button type="button" wire:click="closePackModal"
                                class="px-4 py-2 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                            {{ $isNewPack ? 'Create Pack' : 'Save Changes' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
