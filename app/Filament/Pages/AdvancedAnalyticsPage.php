<?php

namespace App\Filament\Pages;

use App\Services\AnalyticsService;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use App\Models\Trip;
use App\Models\User;
use App\Models\Transaction;
use App\Models\Rating;
use Illuminate\Support\Facades\DB;

class AdvancedAnalyticsPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?string $navigationLabel = 'إحصائيات متقدمة';
    protected static ?string $title = 'الإحصائيات المتقدمة';
    protected static \UnitEnum|string|null $navigationGroup = 'التقارير';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.advanced-analytics';

    public function getViewData(): array
    {
        $analytics = new AnalyticsService();

        return [
            // Retention Data
            'retention_data' => $this->getRetentionData(),

            // Conversion Funnel
            'conversion_funnel' => $this->getConversionFunnel(),

            // Revenue Trends (30 days)
            'revenue_trend' => $this->getRevenueTrend(),

            // Category Performance
            'category_performance' => $this->getCategoryPerformance(),

            // Driver Performance Distribution
            'driver_performance' => $this->getDriverPerformanceDistribution(),

            // Peak Hours Heatmap Data
            'peak_hours' => $analytics->getPeakHoursAnalysis(),

            // Customer Lifetime Value
            'customer_ltv' => $this->getCustomerLTV(),

            // Churn Rate
            'churn_rate' => $this->getChurnRate(),
        ];
    }

    private function getRetentionData(): array
    {
        $weeks = [];
        for ($i = 7; $i >= 0; $i--) {
            $weekStart = now()->subWeeks($i)->startOfWeek();
            $weekEnd = now()->subWeeks($i)->endOfWeek();

            $newUsers = User::where('role', 'customer')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();

            $activeUsers = Trip::whereBetween('created_at', [$weekStart, $weekEnd])
                ->distinct('customer_id')
                ->count('customer_id');

            $weeks[] = [
                'week' => "الأسبوع " . (8 - $i),
                'label' => $weekStart->format('m/d'),
                'new_users' => $newUsers,
                'active_users' => $activeUsers,
                'retention_rate' => $newUsers > 0 ? round(($activeUsers / max($newUsers, 1)) * 100, 1) : 0,
            ];
        }
        return $weeks;
    }

    private function getConversionFunnel(): array
    {
        $totalCustomers = User::where('role', 'customer')->count();
        $customersWhoRequested = Trip::distinct('customer_id')->count('customer_id');
        $customersWhoCompleted = Trip::where('status', 'completed')
            ->distinct('customer_id')
            ->count('customer_id');
        $customersWhoRated = Rating::distinct('customer_id')->count('customer_id');
        $customersWhoSubscribed = \App\Models\UserSubscription::where('status', 'active')
            ->distinct('user_id')
            ->count('user_id');

        return [
            ['stage' => 'مسجلين', 'count' => $totalCustomers, 'color' => '#6b7280'],
            ['stage' => 'طلبوا رحلة', 'count' => $customersWhoRequested, 'color' => '#3b82f6'],
            ['stage' => 'أكملوا رحلة', 'count' => $customersWhoCompleted, 'color' => '#22c55e'],
            ['stage' => 'قيّموا', 'count' => $customersWhoRated, 'color' => '#f59e0b'],
            ['stage' => 'مشتركين', 'count' => $customersWhoSubscribed, 'color' => '#8b5cf6'],
        ];
    }

    private function getRevenueTrend(): array
    {
        $data = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $revenue = Transaction::where('payment_status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('app_commission');
            $data[] = [
                'date' => $date,
                'revenue' => round($revenue, 2),
            ];
        }
        return $data;
    }

    private function getCategoryPerformance(): array
    {
        return Trip::where('status', 'completed')
            ->select('category', DB::raw('COUNT(*) as trips'), DB::raw('AVG(final_price) as avg_price'), DB::raw('AVG(distance_km) as avg_distance'))
            ->groupBy('category')
            ->get()
            ->map(fn($item) => [
                'category' => match ($item->category) {
                    'economy' => 'توفير',
                    'vip' => 'VIP',
                    'bus' => 'باص',
                    default => $item->category,
                },
                'trips' => $item->trips,
                'avg_price' => round($item->avg_price, 2),
                'avg_distance' => round($item->avg_distance, 2),
            ])
            ->toArray();
    }

    private function getDriverPerformanceDistribution(): array
    {
        $ranges = [
            '0-10 رحلات' => [0, 10],
            '10-30 رحلة' => [10, 30],
            '30-50 رحلة' => [30, 50],
            '50+ رحلة' => [50, 999999],
        ];

        $result = [];
        foreach ($ranges as $label => [$min, $max]) {
            $count = User::where('role', 'driver')
                ->whereHas('tripsAsDriver', fn($q) => $q->where('status', 'completed'))
                ->withCount(['tripsAsDriver as completed_trips' => fn($q) => $q->where('status', 'completed')])
                ->having('completed_trips', '>=', $min)
                ->having('completed_trips', '<', $max)
                ->count();
            $result[] = ['range' => $label, 'count' => $count];
        }
        return $result;
    }

    private function getCustomerLTV(): array
    {
        return User::where('role', 'customer')
            ->whereHas('tripsAsCustomer', fn($q) => $q->where('status', 'completed'))
            ->withSum(['tripsAsCustomer as total_spent' => fn($q) => $q->where('status', 'completed')], 'final_price')
            ->withCount(['tripsAsCustomer as total_trips' => fn($q) => $q->where('status', 'completed')])
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get()
            ->map(fn($user) => [
                'name' => $user->name,
                'total_trips' => $user->total_trips,
                'total_spent' => round($user->total_spent ?? 0, 2),
                'avg_per_trip' => $user->total_trips > 0 ? round(($user->total_spent ?? 0) / $user->total_trips, 2) : 0,
            ])
            ->toArray();
    }

    private function getChurnRate(): array
    {
        $totalCustomers = User::where('role', 'customer')->count() ?: 1;
        $activeLast30 = Trip::where('created_at', '>=', now()->subDays(30))
            ->distinct('customer_id')
            ->count('customer_id');
        $activeLast60 = Trip::whereBetween('created_at', [now()->subDays(60), now()->subDays(30)])
            ->distinct('customer_id')
            ->count('customer_id');

        $churned = max(0, $activeLast60 - $activeLast30);
        $churnRate = $activeLast60 > 0 ? round(($churned / $activeLast60) * 100, 1) : 0;

        return [
            'total_customers' => $totalCustomers,
            'active_last_30' => $activeLast30,
            'active_30_60' => $activeLast60,
            'churned' => $churned,
            'churn_rate' => $churnRate,
        ];
    }
}
