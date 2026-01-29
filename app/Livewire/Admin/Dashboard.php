<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\TryOn;
use App\Models\Subscription;
use App\Models\CreditPurchase;
use App\Models\AdminActivityLog;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public array $stats = [];
    public array $recentActivity = [];
    public array $revenueChart = [];
    public array $userGrowth = [];

    // Cost per API call (slightly inflated from £0.10)
    protected const API_COST_PER_CALL = 0.12;
    // Stripe UK fees: ~2.5% + 20p average
    protected const STRIPE_PERCENT_FEE = 0.025;
    protected const STRIPE_FIXED_FEE = 0.20;

    public function mount()
    {
        $this->loadStats();
        $this->loadRecentActivity();
        $this->loadChartData();
    }

    protected function loadStats()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $totalTryons = TryOn::count();
        $tryonsThisMonth = TryOn::where('created_at', '>=', $thisMonth)->count();
        $revenueThisMonth = $this->calculateMonthlyRevenue();
        $revenueToday = $this->calculateTodayRevenue();

        // Calculate costs
        $apiCostThisMonth = $tryonsThisMonth * self::API_COST_PER_CALL;
        $apiCostTotal = $totalTryons * self::API_COST_PER_CALL;

        // Stripe fees (estimate based on transaction count)
        $transactionsThisMonth = CreditPurchase::where('created_at', '>=', $thisMonth)
            ->where('status', 'completed')
            ->count();
        $stripeFees = ($revenueThisMonth * self::STRIPE_PERCENT_FEE) + ($transactionsThisMonth * self::STRIPE_FIXED_FEE);

        // Profit calculation
        $profitThisMonth = $revenueThisMonth - $apiCostThisMonth - $stripeFees;

        $this->stats = [
            'total_users' => User::count(),
            'users_today' => User::where('created_at', '>=', $today)->count(),
            'users_this_month' => User::where('created_at', '>=', $thisMonth)->count(),

            'total_tryons' => $totalTryons,
            'tryons_today' => TryOn::where('created_at', '>=', $today)->count(),
            'tryons_this_month' => $tryonsThisMonth,

            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'pro_subscriptions' => Subscription::where('status', 'active')->where('plan', 'pro')->count(),
            'premium_subscriptions' => Subscription::where('status', 'active')->where('plan', 'premium')->count(),

            'revenue_this_month' => $revenueThisMonth,
            'revenue_today' => $revenueToday,

            'total_credits_used' => DB::table('credit_transactions')
                ->where('type', 'usage')
                ->sum('amount') ?? 0,

            // Cost & Profit metrics
            'api_cost_this_month' => $apiCostThisMonth,
            'api_cost_total' => $apiCostTotal,
            'stripe_fees' => $stripeFees,
            'profit_this_month' => $profitThisMonth,
        ];
    }

    protected function calculateMonthlyRevenue(): float
    {
        $thisMonth = now()->startOfMonth();

        // Credit purchases
        $creditRevenue = CreditPurchase::where('created_at', '>=', $thisMonth)
            ->where('status', 'completed')
            ->sum('amount') ?? 0;

        // Subscriptions (estimate based on active subs)
        $proSubs = Subscription::where('status', 'active')
            ->where('plan', 'pro')
            ->where('current_period_start', '>=', $thisMonth)
            ->count();

        $premiumSubs = Subscription::where('status', 'active')
            ->where('plan', 'premium')
            ->where('current_period_start', '>=', $thisMonth)
            ->count();

        $subscriptionRevenue = ($proSubs * 999) + ($premiumSubs * 2499);

        return ($creditRevenue + $subscriptionRevenue) / 100;
    }

    protected function calculateTodayRevenue(): float
    {
        $today = now()->startOfDay();

        $creditRevenue = CreditPurchase::where('created_at', '>=', $today)
            ->where('status', 'completed')
            ->sum('amount') ?? 0;

        return $creditRevenue / 100;
    }

    protected function loadRecentActivity()
    {
        $this->recentActivity = AdminActivityLog::with('adminUser')
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'admin' => $log->adminUser?->name ?? 'System',
                'action' => $log->action_label,
                'action_color' => $log->action_color,
                'model' => $log->model_label,
                'description' => $log->description,
                'time' => $log->created_at->diffForHumans(),
            ])
            ->toArray();
    }

    protected function loadChartData()
    {
        // User growth last 7 days
        $this->userGrowth = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->startOfDay();
            return [
                'date' => $date->format('M d'),
                'count' => User::whereDate('created_at', $date)->count(),
            ];
        })->toArray();

        // Revenue last 7 days
        $this->revenueChart = collect(range(6, 0))->map(function ($daysAgo) {
            $date = now()->subDays($daysAgo)->startOfDay();
            $revenue = CreditPurchase::whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('amount') ?? 0;
            return [
                'date' => $date->format('M d'),
                'amount' => $revenue / 100,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
