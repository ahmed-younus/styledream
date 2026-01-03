<div class="space-y-6">
    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Total Users --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_users']) }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">+{{ $stats['users_today'] }} today</p>
                </div>
            </div>
        </div>

        {{-- Try-Ons --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Try-Ons</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['total_tryons']) }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">+{{ $stats['tryons_today'] }} today</p>
                </div>
            </div>
        </div>

        {{-- Subscriptions --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Subs</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($stats['active_subscriptions']) }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ $stats['pro_subscriptions'] }} Pro, {{ $stats['premium_subscriptions'] }} Premium</p>
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 bg-amber-100 dark:bg-amber-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">This Month</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">${{ number_format($stats['revenue_this_month'], 2) }}</p>
                    <p class="text-xs text-green-600 dark:text-green-400 mt-1">+${{ number_format($stats['revenue_today'], 2) }} today</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- User Growth --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">User Growth (Last 7 Days)</h3>
            <div class="h-48 flex items-end justify-between gap-2">
                @php $maxUsers = max(array_column($userGrowth, 'count')) ?: 1; @endphp
                @foreach($userGrowth as $day)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full max-w-[40px] mx-auto bg-purple-500 rounded-t hover:bg-purple-600 transition-colors"
                             style="height: {{ max(4, ($day['count'] / $maxUsers) * 140) }}px"></div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">{{ $day['date'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Revenue Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Revenue (Last 7 Days)</h3>
            <div class="h-48 flex items-end justify-between gap-2">
                @php $maxRevenue = max(array_column($revenueChart, 'amount')) ?: 1; @endphp
                @foreach($revenueChart as $day)
                    <div class="flex-1 flex flex-col items-center">
                        <div class="w-full max-w-[40px] mx-auto bg-green-500 rounded-t hover:bg-green-600 transition-colors"
                             style="height: {{ max(4, ($day['amount'] / $maxRevenue) * 140) }}px"></div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">{{ $day['date'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Bottom Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Quick Stats --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Quick Stats</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Users This Month</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($stats['users_this_month']) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Credits Used</span>
                    <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format(abs($stats['total_credits_used'])) }}</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Pro Subscribers</span>
                    <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">{{ $stats['pro_subscriptions'] }}</span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Premium Subscribers</span>
                    <span class="text-sm font-semibold text-purple-600 dark:text-purple-400">{{ $stats['premium_subscriptions'] }}</span>
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Recent Admin Activity</h3>
                <a href="{{ route('admin.logs') }}" class="text-sm text-purple-600 hover:text-purple-700 dark:text-purple-400 dark:hover:text-purple-300 font-medium">View All</a>
            </div>

            @if(count($recentActivity) > 0)
                <div class="space-y-3">
                    @foreach($recentActivity as $activity)
                        <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 dark:bg-gray-700/50">
                            <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                                <span class="text-xs font-semibold text-purple-600 dark:text-purple-400">{{ strtoupper(substr($activity['admin'], 0, 1)) }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900 dark:text-white">
                                    <span class="font-medium">{{ $activity['admin'] }}</span>
                                    <span class="text-gray-500 dark:text-gray-400 ml-1">{{ $activity['action'] }}</span>
                                </p>
                                @if($activity['description'])
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $activity['description'] }}</p>
                                @endif
                            </div>
                            <span class="text-xs text-gray-400 dark:text-gray-500 flex-shrink-0 whitespace-nowrap">{{ $activity['time'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400 text-center py-8">No recent activity</p>
            @endif
        </div>
    </div>
</div>
