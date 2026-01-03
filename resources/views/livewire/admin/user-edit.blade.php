<div>
    <div class="mb-6">
        <a href="{{ route('admin.users') }}" class="text-purple-600 hover:text-purple-700 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Users
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
            <p class="text-green-700 dark:text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- User Info Card --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">User Information</h3>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Name</label>
                    <input type="text" wire:model="form.name" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('form.name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                    <input type="email" wire:model="form.email" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    @error('form.email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4">
                    <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Stats Card --}}
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Credits</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($user->credits) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Try-Ons</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $user->tryOns->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600 dark:text-gray-400">Joined</span>
                        <span class="font-semibold text-gray-900 dark:text-white">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Credit Management --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Manage Credits</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Amount</label>
                        <input type="number" wire:model="creditAmount" min="1" max="10000"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="Enter amount">
                        @error('creditAmount') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Reason (optional)</label>
                        <input type="text" wire:model="creditReason"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                               placeholder="e.g., Bonus, Refund, Correction">
                    </div>

                    <div class="flex gap-2">
                        <button wire:click="addCredits" type="button"
                                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                            + Add
                        </button>
                        <button wire:click="deductCredits" type="button"
                                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm font-medium">
                            - Deduct
                        </button>
                    </div>
                </div>
            </div>

            @php $subscription = $user->activeSubscription(); @endphp
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Subscription</h3>
                @if($subscription)
                    <div class="space-y-2">
                        <p class="text-lg font-bold {{ $subscription->plan === 'premium' ? 'text-purple-600' : 'text-blue-600' }}">{{ ucfirst($subscription->plan) }}</p>
                        <p class="text-sm text-gray-500">Expires: {{ $subscription->current_period_end->format('M d, Y') }}</p>
                    </div>
                @else
                    <p class="text-gray-500">No active subscription</p>
                @endif
            </div>
        </div>
    </div>
</div>
