<div>
    <div class="flex flex-col gap-4 mb-6">
        <h2 class="text-lg font-medium text-gray-500 dark:text-gray-400 hidden sm:block">Manage subscriptions</h2>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by email..." class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white flex-1 sm:w-64 sm:flex-initial">
            <select wire:model.live="filter" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="canceled">Canceled</option>
                <option value="past_due">Past Due</option>
            </select>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">User</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Plan</th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Status</th>
                        <th class="hidden sm:table-cell px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Period End</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($subscriptions as $sub)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-3 sm:px-6 py-3 sm:py-4">
                                <p class="font-medium text-gray-900 dark:text-white text-sm sm:text-base truncate max-w-[120px] sm:max-w-none">{{ $sub->user?->name ?? 'Deleted' }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 truncate max-w-[120px] sm:max-w-none">{{ $sub->user?->email }}</p>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $sub->plan === 'premium' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ ucfirst($sub->plan) }}
                                </span>
                            </td>
                            <td class="px-3 sm:px-6 py-3 sm:py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $sub->status === 'active' ? 'bg-green-100 text-green-700' : ($sub->status === 'canceled' ? 'bg-gray-100 text-gray-700' : 'bg-red-100 text-red-700') }}">
                                    {{ ucfirst($sub->status) }}
                                </span>
                            </td>
                            <td class="hidden sm:table-cell px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $sub->current_period_end?->format('M d, Y') ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-gray-500">No subscriptions found</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $subscriptions->links() }}
        </div>
    </div>
</div>
