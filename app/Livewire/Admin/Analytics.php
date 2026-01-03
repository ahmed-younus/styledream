<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\TryOn;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Carbon\Carbon;

#[Layout('layouts.admin')]
#[Title('Analytics')]
class Analytics extends Component
{
    public string $period = '30'; // days
    public string $tab = 'overview';

    public function render()
    {
        $days = (int) $this->period;
        $startDate = Carbon::now()->subDays($days);

        // User Analytics
        $userStats = $this->getUserStats($startDate, $days);

        // Revenue Analytics
        $revenueStats = $this->getRevenueStats($startDate, $days);

        // Try-On Analytics
        $tryOnStats = $this->getTryOnStats($startDate, $days);

        // Credit Analytics
        $creditStats = $this->getCreditStats($startDate, $days);

        return view('livewire.admin.analytics', [
            'userStats' => $userStats,
            'revenueStats' => $revenueStats,
            'tryOnStats' => $tryOnStats,
            'creditStats' => $creditStats,
        ]);
    }

    private function getUserStats(Carbon $startDate, int $days): array
    {
        $totalUsers = User::count();
        $newUsers = User::where('created_at', '>=', $startDate)->count();
        $previousPeriodUsers = User::whereBetween('created_at', [
            $startDate->copy()->subDays($days),
            $startDate
        ])->count();

        $growthRate = $previousPeriodUsers > 0
            ? round((($newUsers - $previousPeriodUsers) / $previousPeriodUsers) * 100, 1)
            : 100;

        // Daily breakdown
        $dailyUsers = User::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill missing dates
        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => Carbon::parse($date)->format('M d'),
                'count' => $dailyUsers[$date] ?? 0,
            ];
        }

        return [
            'total' => $totalUsers,
            'new' => $newUsers,
            'growth_rate' => $growthRate,
            'chart' => $chartData,
        ];
    }

    private function getRevenueStats(Carbon $startDate, int $days): array
    {
        $revenue = CreditTransaction::where('created_at', '>=', $startDate)
            ->where('type', 'purchase')
            ->where('amount', '>', 0)
            ->sum('amount');

        $previousRevenue = CreditTransaction::whereBetween('created_at', [
            $startDate->copy()->subDays($days),
            $startDate
        ])
            ->where('type', 'purchase')
            ->where('amount', '>', 0)
            ->sum('amount');

        $growthRate = $previousRevenue > 0
            ? round((($revenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : ($revenue > 0 ? 100 : 0);

        // Daily breakdown
        $dailyRevenue = CreditTransaction::where('created_at', '>=', $startDate)
            ->where('type', 'purchase')
            ->where('amount', '>', 0)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => Carbon::parse($date)->format('M d'),
                'amount' => (float) ($dailyRevenue[$date] ?? 0),
            ];
        }

        // Revenue by source
        $bySource = CreditTransaction::where('created_at', '>=', $startDate)
            ->where('type', 'purchase')
            ->where('amount', '>', 0)
            ->selectRaw("COALESCE(description, 'Unknown') as source, SUM(amount) as total")
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->toArray();

        return [
            'total' => $revenue,
            'previous' => $previousRevenue,
            'growth_rate' => $growthRate,
            'chart' => $chartData,
            'by_source' => $bySource,
        ];
    }

    private function getTryOnStats(Carbon $startDate, int $days): array
    {
        $totalTryOns = TryOn::where('created_at', '>=', $startDate)->count();
        $previousTryOns = TryOn::whereBetween('created_at', [
            $startDate->copy()->subDays($days),
            $startDate
        ])->count();

        $growthRate = $previousTryOns > 0
            ? round((($totalTryOns - $previousTryOns) / $previousTryOns) * 100, 1)
            : ($totalTryOns > 0 ? 100 : 0);

        // Daily breakdown
        $dailyTryOns = TryOn::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => Carbon::parse($date)->format('M d'),
                'count' => $dailyTryOns[$date] ?? 0,
            ];
        }

        // Success rate
        $successful = TryOn::where('created_at', '>=', $startDate)
            ->whereNotNull('result_image_url')
            ->count();

        $successRate = $totalTryOns > 0
            ? round(($successful / $totalTryOns) * 100, 1)
            : 0;

        return [
            'total' => $totalTryOns,
            'previous' => $previousTryOns,
            'growth_rate' => $growthRate,
            'success_rate' => $successRate,
            'chart' => $chartData,
        ];
    }

    private function getCreditStats(Carbon $startDate, int $days): array
    {
        $creditsUsed = CreditTransaction::where('created_at', '>=', $startDate)
            ->where('amount', '<', 0)
            ->sum(DB::raw('ABS(amount)'));

        $creditsPurchased = CreditTransaction::where('created_at', '>=', $startDate)
            ->where('type', 'purchase')
            ->where('amount', '>', 0)
            ->sum('amount');

        $creditsGifted = CreditTransaction::where('created_at', '>=', $startDate)
            ->whereIn('type', ['bonus', 'admin', 'daily'])
            ->where('amount', '>', 0)
            ->sum('amount');

        // Daily usage
        $dailyUsage = CreditTransaction::where('created_at', '>=', $startDate)
            ->where('amount', '<', 0)
            ->selectRaw('DATE(created_at) as date, SUM(ABS(amount)) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        $chartData = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => Carbon::parse($date)->format('M d'),
                'used' => (int) ($dailyUsage[$date] ?? 0),
            ];
        }

        return [
            'used' => $creditsUsed,
            'purchased' => $creditsPurchased,
            'gifted' => $creditsGifted,
            'chart' => $chartData,
        ];
    }
}
