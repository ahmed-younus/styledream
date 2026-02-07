<div>
    <div class="mb-6">
        <a href="{{ route('admin.users') }}" class="text-[#635bff] hover:text-[#5248f0] flex items-center gap-1 text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
            </svg>
            Back to Users
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-[#30b566]/10 border border-[#30b566]/20 rounded-lg">
            <p class="text-[#30b566] text-sm">{{ session('success') }}</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- User Info Card --}}
        <div class="lg:col-span-2 bg-white rounded-lg border border-[#e3e8ee] p-6">
            <h3 class="text-sm font-semibold text-[#1a1f36] mb-6">User Information</h3>

            <form wire:submit="save" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Name</label>
                    <input type="text" wire:model="form.name" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                    @error('form.name') <p class="text-[#df1b41] text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-[#1a1f36] mb-1">Email</label>
                    <input type="email" wire:model="form.email" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                    @error('form.email') <p class="text-[#df1b41] text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-4">
                    <button type="submit" class="px-4 py-2 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        {{-- Stats Card --}}
        <div class="space-y-6">
            <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
                <h3 class="text-sm font-semibold text-[#1a1f36] mb-4">Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-[#697386]">Total Credits</span>
                        <span class="text-sm font-semibold text-[#1a1f36] tabular-nums">{{ number_format($user->totalCredits()) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-[#697386]">- Subscription</span>
                        <span class="text-sm text-[#697386] tabular-nums">{{ number_format($user->subscription_credits ?? 0) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-[#697386]">- Purchased</span>
                        <span class="text-sm text-[#697386] tabular-nums">{{ number_format($user->credits) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-[#697386]">Subscription Tier</span>
                        <span class="text-sm font-semibold text-[#1a1f36]">{{ ucfirst($user->subscription_tier ?? 'Free') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-[#697386]">Try-Ons</span>
                        <span class="text-sm font-semibold text-[#1a1f36] tabular-nums">{{ $user->tryOns->count() }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-[#697386]">Joined</span>
                        <span class="text-sm font-semibold text-[#1a1f36]">{{ $user->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Credit Management --}}
            <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
                <h3 class="text-sm font-semibold text-[#1a1f36] mb-4">Manage Credits</h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-[#1a1f36] mb-1">Amount</label>
                        <input type="number" wire:model="creditAmount" min="1" max="10000"
                               class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm"
                               placeholder="Enter amount">
                        @error('creditAmount') <p class="text-[#df1b41] text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#1a1f36] mb-1">Reason (optional)</label>
                        <input type="text" wire:model="creditReason"
                               class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm"
                               placeholder="e.g., Bonus, Refund, Correction">
                    </div>

                    <div class="flex gap-2">
                        <button wire:click="addCredits" type="button"
                                class="flex-1 px-4 py-2 bg-[#30b566] text-white rounded-lg hover:bg-[#28a058] transition-colors text-sm font-medium">
                            + Add
                        </button>
                        <button wire:click="deductCredits" type="button"
                                class="flex-1 px-4 py-2 bg-[#df1b41] text-white rounded-lg hover:bg-[#c8183a] transition-colors text-sm font-medium">
                            - Deduct
                        </button>
                    </div>
                </div>
            </div>

            @php $subscription = $user->activeSubscription(); @endphp
            <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
                <h3 class="text-sm font-semibold text-[#1a1f36] mb-4">Subscription</h3>
                @if($subscription)
                    <div class="space-y-2">
                        <p class="text-base font-semibold text-[#1a1f36]">{{ ucfirst($subscription->plan) }}</p>
                        <p class="text-sm text-[#697386]">Expires: {{ $subscription->current_period_end->format('M d, Y') }}</p>
                        @if($subscription->scheduled_plan)
                            <p class="text-sm text-orange-500">Scheduled: {{ ucfirst($subscription->scheduled_plan) }} on {{ $subscription->scheduled_change_at->format('M d, Y') }}</p>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-[#697386]">No active subscription</p>
                @endif
            </div>

            {{-- Reset User for Testing --}}
            <div class="bg-white rounded-lg border border-[#df1b41]/20 p-5">
                <h3 class="text-sm font-semibold text-[#df1b41] mb-2">Reset User (Testing)</h3>
                <p class="text-xs text-[#697386] mb-4">Reset user to initial state: 5 credits, no subscription. All subscriptions will be canceled.</p>
                <button wire:click="resetUser"
                        wire:confirm="Are you sure? This will reset credits to 5 and cancel all subscriptions."
                        type="button"
                        class="w-full px-4 py-2 bg-[#df1b41] text-white rounded-lg hover:bg-[#c8183a] transition-colors text-sm font-medium">
                    Reset User
                </button>
            </div>
        </div>
    </div>
</div>
