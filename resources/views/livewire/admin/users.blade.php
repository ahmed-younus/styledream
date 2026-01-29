<div>
    {{-- Header --}}
    <div class="flex flex-col gap-4 mb-6">
        <div class="hidden sm:block">
            <h2 class="text-sm font-medium text-[#697386]">Manage all registered users</h2>
        </div>

        {{-- Filters --}}
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <div class="relative flex-1 sm:flex-initial">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search users..."
                    class="pl-9 pr-4 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] w-full sm:w-64 outline-none transition-colors text-sm"
                >
                <svg class="w-4 h-4 text-[#697386] absolute left-3 top-2.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                </svg>
            </div>

            <select wire:model.live="filter" class="px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                <option value="all">All Users</option>
                <option value="subscribed">Subscribed</option>
                <option value="free">Free Users</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg border border-[#e3e8ee] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#f6f8fa]">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase tracking-wider cursor-pointer hover:bg-[#f0f3f7] transition-colors" wire:click="sortBy('name')">
                            User
                            @if($sortBy === 'name')
                                <span class="ml-1">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase tracking-wider cursor-pointer hover:bg-[#f0f3f7] transition-colors" wire:click="sortBy('credits')">
                            Credits
                            @if($sortBy === 'credits')
                                <span class="ml-1">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase tracking-wider">
                            Plan
                        </th>
                        <th class="hidden md:table-cell px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase tracking-wider cursor-pointer hover:bg-[#f0f3f7] transition-colors" wire:click="sortBy('created_at')">
                            Joined
                            @if($sortBy === 'created_at')
                                <span class="ml-1">{{ $sortDir === 'asc' ? '↑' : '↓' }}</span>
                            @endif
                        </th>
                        <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-[#697386] uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e3e8ee]">
                    @forelse($users as $user)
                        <tr class="hover:bg-[#f6f8fa] transition-colors">
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-full bg-[#f0f3f7] flex items-center justify-center text-[#1a1f36] font-medium flex-shrink-0 text-sm">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-[#1a1f36] text-sm truncate max-w-[120px] sm:max-w-none">{{ $user->name }}</p>
                                        <p class="text-xs text-[#697386] truncate max-w-[120px] sm:max-w-none">{{ $user->email }}</p>
                                        {{-- Show credits on mobile --}}
                                        <p class="text-xs text-[#1a1f36] sm:hidden">{{ number_format($user->credits) }} credits</p>
                                    </div>
                                    @if($user->is_banned ?? false)
                                        <span class="hidden sm:inline-block px-2 py-0.5 text-xs font-medium bg-[#df1b41]/10 text-[#df1b41] rounded-full">Banned</span>
                                    @endif
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                                <span class="font-medium text-[#1a1f36] text-sm tabular-nums">{{ number_format($user->credits) }}</span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                @php $subscription = $user->activeSubscription(); @endphp
                                @if($subscription)
                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-[#635bff] text-white">
                                        {{ ucfirst($subscription->plan) }}
                                    </span>
                                @else
                                    <span class="text-[#697386] text-xs">Free</span>
                                @endif
                                @if($user->is_banned ?? false)
                                    <span class="sm:hidden ml-1 px-1.5 py-0.5 text-[10px] font-medium bg-[#df1b41]/10 text-[#df1b41] rounded">Ban</span>
                                @endif
                            </td>
                            <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-[#697386]">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openCreditsModal({{ $user->id }})" class="p-1.5 sm:p-2 text-[#30b566] hover:bg-[#30b566]/10 rounded-lg transition-colors" title="Add Credits">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                        </svg>
                                    </button>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="p-1.5 sm:p-2 text-[#697386] hover:text-[#1a1f36] hover:bg-[#f6f8fa] rounded-lg transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
                                        </svg>
                                    </a>
                                    <button wire:click="toggleBan({{ $user->id }})" class="p-1.5 sm:p-2 {{ ($user->is_banned ?? false) ? 'text-[#30b566] hover:bg-[#30b566]/10' : 'text-[#df1b41] hover:bg-[#df1b41]/10' }} rounded-lg transition-colors" title="{{ ($user->is_banned ?? false) ? 'Unban' : 'Ban' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-[#697386] text-sm">
                                No users found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-[#e3e8ee]">
            {{ $users->links() }}
        </div>
    </div>

    {{-- Add Credits Modal --}}
    @if($showCreditsModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="$el.querySelector('input')?.focus()">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-black/40" wire:click="$set('showCreditsModal', false)"></div>
                <div class="relative bg-white rounded-lg shadow-lg w-full max-w-md p-6 border border-[#e3e8ee]">
                    <h3 class="text-base font-semibold text-[#1a1f36] mb-4">Add Credits</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Credits to Add</label>
                            <input type="number" wire:model="creditsToAdd" min="1" max="10000" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                            @error('creditsToAdd') <p class="text-[#df1b41] text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-[#1a1f36] mb-1">Reason</label>
                            <input type="text" wire:model="creditReason" placeholder="e.g., Compensation, Bonus" class="w-full px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                            @error('creditReason') <p class="text-[#df1b41] text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button wire:click="$set('showCreditsModal', false)" class="px-4 py-2 text-[#697386] hover:text-[#1a1f36] hover:bg-[#f6f8fa] rounded-lg transition-colors text-sm font-medium">
                            Cancel
                        </button>
                        <button wire:click="addCredits" class="px-4 py-2 bg-[#635bff] text-white rounded-lg hover:bg-[#5248f0] transition-colors text-sm font-medium">
                            Add Credits
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
