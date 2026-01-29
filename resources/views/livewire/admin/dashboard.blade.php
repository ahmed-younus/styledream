<div class="max-w-7xl">
    {{-- Today Header --}}
    <div class="mb-8">
        <h1 class="text-[22px] font-semibold text-[#1a1f36] tracking-tight">Today</h1>
        <p class="text-sm text-[#697386] mt-0.5">{{ now()->format('F j, Y') }}</p>
    </div>

    {{-- Top Stats Row --}}
    <div class="flex flex-wrap gap-x-12 gap-y-6 mb-8">
        <div>
            <div class="flex items-center gap-1 mb-0.5">
                <span class="text-sm text-[#697386]">Total Users</span>
            </div>
            <p class="text-[28px] font-semibold text-[#1a1f36] tabular-nums tracking-tight">{{ number_format($stats['total_users']) }}</p>
            <p class="text-[13px] text-[#697386] mt-0.5"><span class="text-[#00a67d] font-medium">+{{ $stats['users_this_month'] }}</span> this month</p>
        </div>
        <div>
            <div class="flex items-center gap-1 mb-0.5">
                <span class="text-sm text-[#697386]">Revenue</span>
            </div>
            <p class="text-[28px] font-semibold text-[#1a1f36] tabular-nums tracking-tight">£{{ number_format($stats['revenue_this_month'], 2) }}</p>
            <p class="text-[13px] text-[#697386] mt-0.5"><span class="text-[#00a67d] font-medium">£{{ number_format($stats['revenue_today'], 2) }}</span> today</p>
        </div>
        <div>
            <div class="flex items-center gap-1 mb-0.5">
                <span class="text-sm text-[#697386]">API Costs</span>
            </div>
            <p class="text-[28px] font-semibold text-[#df1b41] tabular-nums tracking-tight">£{{ number_format($stats['api_cost_this_month'] ?? 0, 2) }}</p>
            <p class="text-[13px] text-[#697386] mt-0.5">{{ $stats['tryons_this_month'] ?? 0 }} try-ons @ £0.12</p>
        </div>
        <div>
            <div class="flex items-center gap-1 mb-0.5">
                <span class="text-sm text-[#697386]">Net Profit</span>
            </div>
            @php $profit = $stats['profit_this_month'] ?? 0; @endphp
            <p class="text-[28px] font-semibold {{ $profit >= 0 ? 'text-[#00a67d]' : 'text-[#df1b41]' }} tabular-nums tracking-tight">{{ $profit >= 0 ? '' : '-' }}£{{ number_format(abs($profit), 2) }}</p>
            <p class="text-[13px] text-[#697386] mt-0.5">after fees this month</p>
        </div>
    </div>

    {{-- Line Chart --}}
    <div class="mb-8 bg-white rounded-lg border border-[#e3e8ee] p-5">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-medium text-[#1a1f36]">User Growth</h3>
            <span class="text-xs text-[#697386]">Last 7 days</span>
        </div>
    <div class="mb-8 bg-white rounded-lg border border-[#e3e8ee] p-5">
        <div class="h-32">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="chartGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" style="stop-color:#635bff;stop-opacity:0.12"/>
                        <stop offset="100%" style="stop-color:#635bff;stop-opacity:0"/>
                    </linearGradient>
                </defs>
                @php
                    $maxUsers = max(array_column($userGrowth, 'count')) ?: 1;
                    $points = [];
                    $totalPoints = count($userGrowth);
                    $width = $totalPoints > 1 ? 100 / ($totalPoints - 1) : 100;
                    foreach($userGrowth as $i => $day) {
                        $x = $i * $width;
                        $y = 100 - (($day['count'] / $maxUsers) * 70 + 15);
                        $points[] = "$x,$y";
                    }
                    $polylinePoints = implode(' ', $points);
                    $areaPoints = "0,100 " . $polylinePoints . " 100,100";
                @endphp
                <polygon points="{{ $areaPoints }}" fill="url(#chartGradient)"/>
                <polyline points="{{ $polylinePoints }}" fill="none" stroke="#635bff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="flex justify-between pt-2 border-t border-[#e3e8ee]">
            <span class="text-xs text-[#697386]">{{ $userGrowth[0]['date'] ?? '' }}</span>
            <span class="text-xs text-[#697386]">{{ $userGrowth[count($userGrowth)-1]['date'] ?? '' }}</span>
        </div>
    </div>

    {{-- Balance Row --}}
    <div class="flex flex-wrap gap-8 mb-8 pb-8 border-b border-[#e3e8ee]">
        <div>
            <div class="flex items-center gap-3 mb-0.5">
                <span class="text-sm font-medium text-[#1a1f36]">Total Try-Ons</span>
                <a href="{{ route('admin.analytics') }}" class="text-sm text-[#635bff] hover:text-[#5248f0] font-medium">View</a>
            </div>
            <p class="text-xl font-semibold text-[#1a1f36] tabular-nums">{{ number_format($stats['total_tryons']) }}</p>
        </div>
        <div>
            <div class="flex items-center gap-3 mb-0.5">
                <span class="text-sm font-medium text-[#1a1f36]">Credits Used</span>
                <a href="{{ route('admin.analytics') }}" class="text-sm text-[#635bff] hover:text-[#5248f0] font-medium">View</a>
            </div>
            <p class="text-xl font-semibold text-[#1a1f36] tabular-nums">{{ number_format(abs($stats['total_credits_used'])) }}</p>
        </div>
    </div>

    {{-- Your Overview --}}
    <h2 class="text-base font-semibold text-[#1a1f36]">Your overview</h2>

    {{-- Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
        {{-- Subscriptions --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <span class="text-sm font-medium text-[#1a1f36]">Subscriptions</span>
            <div class="h-1.5 bg-[#e3e8ee] rounded-full mt-3 mb-3 overflow-hidden">
                @php
                    $total = $stats['active_subscriptions'] ?: 1;
                    $proPercent = ($stats['pro_subscriptions'] / $total) * 100;
                    $premiumPercent = ($stats['premium_subscriptions'] / $total) * 100;
                @endphp
                <div class="h-full flex">
                    <div class="bg-[#635bff]" style="width: {{ $proPercent }}%"></div>
                    <div class="bg-[#00d4ff]" style="width: {{ $premiumPercent }}%"></div>
                </div>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#635bff]"></span>
                        <span class="text-[#1a1f36]">Pro</span>
                    </div>
                    <span class="font-medium text-[#1a1f36]">{{ $stats['pro_subscriptions'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#00d4ff]"></span>
                        <span class="text-[#1a1f36]">Premium</span>
                    </div>
                    <span class="font-medium text-[#1a1f36]">{{ $stats['premium_subscriptions'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-[#e3e8ee]"></span>
                        <span class="text-[#697386]">Free</span>
                    </div>
                    <span class="text-[#697386]">{{ $stats['total_users'] - $stats['active_subscriptions'] }}</span>
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <span class="text-sm font-medium text-[#1a1f36]">Revenue</span>
            <div class="flex items-baseline gap-2 mt-2">
                <span class="text-2xl font-semibold text-[#1a1f36] tabular-nums">£{{ number_format($stats['revenue_this_month'], 2) }}</span>
                @if($stats['revenue_this_month'] > 0)
                    <span class="text-sm font-medium text-[#00a67d]">+100%</span>
                @endif
            </div>
            <p class="text-xs text-[#697386] mb-3">This month</p>
            <div class="h-12">
                <svg class="w-full h-full" viewBox="0 0 100 40" preserveAspectRatio="none">
                    @php
                        $maxRev = max(array_column($revenueChart, 'amount')) ?: 1;
                        $revPoints = [];
                        $revTotal = count($revenueChart);
                        $revWidth = $revTotal > 1 ? 100 / ($revTotal - 1) : 100;
                        foreach($revenueChart as $i => $day) {
                            $x = $i * $revWidth;
                            $y = 40 - (($day['amount'] / $maxRev) * 32 + 4);
                            $revPoints[] = "$x,$y";
                        }
                        $revPolyline = implode(' ', $revPoints);
                    @endphp
                    <polyline points="{{ $revPolyline }}" fill="none" stroke="#635bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>

        {{-- Try-Ons --}}
        <div class="bg-white rounded-lg border border-[#e3e8ee] p-5">
            <span class="text-sm font-medium text-[#1a1f36]">Try-Ons</span>
            <div class="mt-2">
                <span class="text-2xl font-semibold text-[#1a1f36] tabular-nums">{{ number_format($stats['total_tryons']) }}</span>
            </div>
            <p class="text-xs text-[#697386] mb-3">Total generations</p>
            <div class="h-12">
                <svg class="w-full h-full" viewBox="0 0 100 40" preserveAspectRatio="none">
                    @php
                        $maxU = max(array_column($userGrowth, 'count')) ?: 1;
                        $uPoints = [];
                        $uTotal = count($userGrowth);
                        $uWidth = $uTotal > 1 ? 100 / ($uTotal - 1) : 100;
                        foreach($userGrowth as $i => $day) {
                            $x = $i * $uWidth;
                            $y = 40 - (($day['count'] / $maxU) * 32 + 4);
                            $uPoints[] = "$x,$y";
                        }
                        $uPolyline = implode(' ', $uPoints);
                    @endphp
                    <polyline points="{{ $uPolyline }}" fill="none" stroke="#635bff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Recent Activity --}}
    <div class="flex items-center justify-between mb-3">
        <h2 class="text-base font-semibold text-[#1a1f36]">Recent activity</h2>
        <a href="{{ route('admin.logs') }}" class="text-sm text-[#635bff] hover:text-[#5248f0] font-medium">View all</a>
    </div>

    <div class="bg-white rounded-lg border border-[#e3e8ee]">
        @forelse($recentActivity as $activity)
            <div class="px-5 py-3.5 flex items-center gap-3 hover:bg-[#f9fafb] transition-colors {{ !$loop->last ? 'border-b border-[#e3e8ee]' : '' }}">
                <div class="w-8 h-8 rounded-full bg-[#f0f3f7] flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-medium text-[#697386]">{{ strtoupper(substr($activity['admin'], 0, 1)) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-[#1a1f36]">
                        <span class="font-medium">{{ $activity['admin'] }}</span>
                        <span class="text-[#697386]">{{ $activity['action'] }}</span>
                    </p>
                    @if($activity['description'])
                        <p class="text-xs text-[#697386] truncate">{{ $activity['description'] }}</p>
                    @endif
                </div>
                <span class="text-xs text-[#697386] flex-shrink-0 tabular-nums">{{ $activity['time'] }}</span>
            </div>
        @empty
            <div class="px-5 py-10 text-center">
                <svg class="w-10 h-10 text-[#d1d5db] mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-[#697386]">No recent activity</p></div>
        @endforelse
    </div>
</div>
