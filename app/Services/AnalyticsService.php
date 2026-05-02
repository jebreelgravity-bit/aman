<?php

namespace App\Services;

use App\Models\Trip;
use App\Models\User;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Calculate retention rate
     */
    public function getRetentionRate(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        // Users who made their first trip in the period
        $newUsers = User::whereHas('customerTrips', function ($q) use ($startDate) {
            $q->where('status', 'completed')
              ->where('created_at', '>=', $startDate);
        })->pluck('id');
        
        // Users who came back for a second trip
        $returningUsers = User::whereIn('id', $newUsers)
            ->whereHas('customerTrips', function ($q) use ($startDate) {
                $q->where('status', 'completed')
                  ->where('created_at', '>=', $startDate);
            }, '>=', 2)
            ->count();
        
        $retentionRate = $newUsers->count() > 0 
            ? round(($returningUsers / $newUsers->count()) * 100, 2) 
            : 0;
        
        return [
            'retention_rate' => $retentionRate,
            'new_users' => $newUsers->count(),
            'returning_users' => $returningUsers,
            'period_days' => $days,
        ];
    }

    /**
     * Calculate conversion rate
     */
    public function getConversionRate(): array
    {
        $totalTrips = Trip::count();
        $completedTrips = Trip::where('status', 'completed')->count();
        $cancelledTrips = Trip::where('status', 'cancelled')->count();
        
        $conversionRate = $totalTrips > 0 
            ? round(($completedTrips / $totalTrips) * 100, 2) 
            : 0;
        
        $cancellationRate = $totalTrips > 0 
            ? round(($cancelledTrips / $totalTrips) * 100, 2) 
            : 0;
        
        return [
            'conversion_rate' => $conversionRate,
            'cancellation_rate' => $cancellationRate,
            'total_trips' => $totalTrips,
            'completed_trips' => $completedTrips,
            'cancelled_trips' => $cancelledTrips,
        ];
    }

    /**
     * Get today's revenue
     */
    public function getTodayRevenue(): array
    {
        $today = now()->startOfDay();
        
        $revenue = Transaction::where('created_at', '>=', $today)
            ->where('payment_status', 'completed')
            ->selectRaw('
                SUM(trip_price) as total_revenue,
                SUM(app_commission) as app_revenue,
                SUM(driver_earnings) as driver_earnings,
                COUNT(*) as transactions_count
            ')
            ->first();
        
        return [
            'total_revenue' => $revenue->total_revenue ?? 0,
            'app_revenue' => $revenue->app_revenue ?? 0,
            'driver_earnings' => $revenue->driver_earnings ?? 0,
            'transactions_count' => $revenue->transactions_count ?? 0,
        ];
    }

    /**
     * Get active trips count
     */
    public function getActiveTripsCount(): int
    {
        return Trip::whereIn('status', ['pending', 'accepted', 'started'])->count();
    }

    /**
     * Get trips distribution by hour (last 24 hours)
     */
    public function getTripsDistributionByHour(): array
    {
        $data = Trip::where('created_at', '>=', now()->subHours(24))
            ->selectRaw("HOUR(created_at) as hour, COUNT(*) as count")
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();
        
        $hours = [];
        $counts = [];
        
        for ($i = 0; $i < 24; $i++) {
            $hours[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ':00';
            $counts[] = $data->firstWhere('hour', $i)?->count ?? 0;
        }
        
        return [
            'labels' => $hours,
            'data' => $counts,
        ];
    }

    /**
     * Get revenue distribution by category
     */
    public function getRevenueByCategory(): array
    {
        $data = Transaction::join('trips', 'transactions.trip_id', '=', 'trips.id')
            ->where('transactions.payment_status', 'completed')
            ->selectRaw('trips.category, SUM(transactions.trip_price) as revenue, COUNT(*) as trips_count')
            ->groupBy('trips.category')
            ->get();
        
        return [
            'labels' => $data->pluck('category')->map(function ($cat) {
                return match($cat) {
                    'economy' => 'توفير',
                    'vip' => 'VIP',
                    'bus' => 'باص',
                    default => $cat
                };
            })->toArray(),
            'data' => $data->pluck('revenue')->toArray(),
            'trips' => $data->pluck('trips_count')->toArray(),
        ];
    }

    /**
     * Get new vs returning customers
     */
    public function getNewVsReturningCustomers(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        $newCustomers = User::where('role', 'customer')
            ->where('created_at', '>=', $startDate)
            ->whereHas('customerTrips', function ($q) {
                $q->where('status', 'completed');
            }, '=', 1)
            ->count();
        
        $returningCustomers = User::where('role', 'customer')
            ->whereHas('customerTrips', function ($q) use ($startDate) {
                $q->where('status', 'completed')
                  ->where('created_at', '>=', $startDate);
            }, '>', 1)
            ->count();
        
        return [
            'labels' => ['عملاء جدد', 'عملاء عائدون'],
            'data' => [$newCustomers, $returningCustomers],
        ];
    }

    /**
     * Get driver performance metrics
     */
    public function getDriverPerformance(int $driverId): array
    {
        $driver = User::find($driverId);
        
        if (!$driver || $driver->role !== 'driver') {
            return [];
        }
        
        $trips = Trip::where('driver_id', $driverId)
            ->where('status', 'completed')
            ->count();
        
        $earnings = Transaction::where('driver_id', $driverId)
            ->where('payment_status', 'completed')
            ->sum('driver_earnings');
        
        $avgRating = DB::table('ratings')
            ->where('driver_id', $driverId)
            ->avg('driver_rating');
        
        $completionRate = Trip::where('driver_id', $driverId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed
            ')
            ->first();
        
        $rate = $completionRate->total > 0 
            ? round(($completionRate->completed / $completionRate->total) * 100, 2) 
            : 0;
        
        return [
            'total_trips' => $trips,
            'total_earnings' => round($earnings, 2),
            'average_rating' => round($avgRating ?? 0, 2),
            'completion_rate' => $rate,
        ];
    }

    /**
     * Get peak hours analysis
     */
    public function getPeakHoursAnalysis(): array
    {
        $data = Trip::where('created_at', '>=', now()->subDays(7))
            ->selectRaw("
                HOUR(created_at) as hour,
                COUNT(*) as trips_count,
                AVG(distance_km) as avg_distance,
                AVG(final_price) as avg_price
            ")
            ->groupBy('hour')
            ->orderBy('trips_count', 'desc')
            ->limit(5)
            ->get();
        
        return $data->map(function ($item) {
            return [
                'hour' => str_pad($item->hour, 2, '0', STR_PAD_LEFT) . ':00',
                'trips' => $item->trips_count,
                'avg_distance' => round($item->avg_distance, 2),
                'avg_price' => round($item->avg_price, 2),
            ];
        })->toArray();
    }

    /**
     * Get monthly comparison
     */
    public function getMonthlyComparison(): array
    {
        $currentMonth = Transaction::whereRaw('MONTH(created_at) = ?', [now()->month])
            ->whereRaw('YEAR(created_at) = ?', [now()->year])
            ->where('payment_status', 'completed')
            ->selectRaw('
                SUM(trip_price) as revenue,
                SUM(app_commission) as commission,
                COUNT(*) as trips
            ')
            ->first();
        
        $lastMonth = Transaction::whereRaw('MONTH(created_at) = ?', [now()->subMonth()->month])
            ->whereRaw('YEAR(created_at) = ?', [now()->subMonth()->year])
            ->where('payment_status', 'completed')
            ->selectRaw('
                SUM(trip_price) as revenue,
                SUM(app_commission) as commission,
                COUNT(*) as trips
            ')
            ->first();
        
        $revenueGrowth = $lastMonth->revenue > 0 
            ? round((($currentMonth->revenue - $lastMonth->revenue) / $lastMonth->revenue) * 100, 2) 
            : 0;
        
        $tripsGrowth = $lastMonth->trips > 0 
            ? round((($currentMonth->trips - $lastMonth->trips) / $lastMonth->trips) * 100, 2) 
            : 0;
        
        return [
            'current_month' => [
                'revenue' => round($currentMonth->revenue ?? 0, 2),
                'commission' => round($currentMonth->commission ?? 0, 2),
                'trips' => $currentMonth->trips ?? 0,
            ],
            'last_month' => [
                'revenue' => round($lastMonth->revenue ?? 0, 2),
                'commission' => round($lastMonth->commission ?? 0, 2),
                'trips' => $lastMonth->trips ?? 0,
            ],
            'growth' => [
                'revenue_percentage' => $revenueGrowth,
                'trips_percentage' => $tripsGrowth,
            ],
        ];
    }
}
