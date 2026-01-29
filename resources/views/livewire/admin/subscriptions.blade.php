<div>
    <div class="flex flex-col gap-4 mb-6">
        <h2 class="text-sm font-medium text-[#697386] hidden sm:block">Manage subscriptions</h2>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by email..." class="px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] placeholder-[#697386] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] flex-1 sm:w-64 sm:flex-initial outline-none transition-colors text-sm">
            <select wire:model.live="filter" class="px-3 py-2 border border-[#e3e8ee] rounded-lg bg-white text-[#1a1f36] focus:ring-2 focus:ring-[#635bff]/20 focus:border-[#635bff] outline-none transition-colors text-sm">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="canceled">Canceled</option>
                <option value="past_due">Past Due</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-[#e3e8ee] overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-[#f6f8fa]">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">User</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Plan</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Status</th>
                        <th class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium text-[#697386] uppercase">Period End</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e3e8ee]">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-[#f6f8fa] transition-colors">
                            <td class="px-3 sm:px-6 py-3 sm:py-4">
                                <p class="font-medium text-[#1a1f36] text-sm truncate max-w-[120px] sm:max-w-none">{{ $sub->user?->name ?? 'Deleted' }}</p>
                                <p class="text-xs text-[#697386] truncate max-w-[120px] sm:max-w-none">{{ $sub->user?->email }}</p>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-[#635bff] text-white">
                                    {{ ucfirst($sub->plan) }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $sub->status === 'active' ? 'bg-[#30b566]/10 text-[#30b566]' : ($sub->status === 'canceled' ? 'bg-[#f0f3f7] text-[#697386]' : 'bg-[#df1b41]/10 text-[#df1b41]') }}">
                                    {{ ucfirst($sub->status) }}
                                </span>
                            </td>
                            <td class="hidden sm:table-cell px-6 py-4 text-sm text-[#697386]">
                                {{ $sub->current_period_end?->format('M d, Y') ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-[#697386] text-sm">No subscriptions found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-[#e3e8ee]">
            {{ $subscriptions->links() }}
        </div>
    </div>
</div>
