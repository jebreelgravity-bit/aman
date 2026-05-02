<?php

namespace App\Services;

use App\Models\DriverReward;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DriverRewardService
{
    const MAX_REWARD_PERCENTAGE = 10; // 10% من أرباح التطبيق الأسبوعية
    
    /**
     * Calculate and distribute weekly rewards for all drivers
     * 
     * @param int|null $weekNumber Week number (defaults to current week)
     * @param int|null $year Year (defaults to current year)
     * @return array Distribution summary
     */
    public function distributeWeeklyRewards(?int $weekNumber = null, ?int $year = null): array
    {
        $weekNumber = $weekNumber ?? Carbon::now()->weekOfYear;
        $year = $year ?? Carbon::now()->year;
        
        // Get week date range
        $weekDates = $this->getWeekDateRange($weekNumber, $year);
        
        // Calculate total app profits for the week
        $totalAppProfits = $this->calculateWeeklyAppProfits($weekDates['start'], $weekDates['end']);
        
        // Maximum reward pool (10% of app profits)
        $maxRewardPool = ($totalAppProfits * self::MAX_REWARD_PERCENTAGE) / 100;
        
        // Get all driver rewards for this week
        $driverRewards = $this->calculateDriverRewards($weekDates['start'], $weekDates['end'], $weekNumber, $year);
        
        // Calculate total calculated rewards
        $totalCalculatedRewards = $driverRewards->sum('calculated_reward');
        
        // Distribute rewards proportionally if exceeds limit
        $distributionRatio = 1.0;
        if ($totalCalculatedRewards > $maxRewardPool) {
            $distributionRatio = $maxRewardPool / $totalCalculatedRewards;
        }
        
        // Update actual rewards
        $distributedRewards = [];
        foreach ($driverRewards as $reward) {
            $actualReward = $reward->calculated_reward * $distributionRatio;
            
            $reward->update([
                'actual_reward' => round($actualReward, 2),
                'is_distributed' => true,
                'distributed_at' => now(),
            ]);
            
            $distributedRewards[] = [
                'driver_id' => $reward->driver_id,
                'calculated_reward' => $reward->calculated_reward,
                'actual_reward' => round($actualReward, 2),
                'total_trips' => $reward->total_trips,
            ];
        }
        
        return [
            'week_number' => $weekNumber,
            'year' => $year,
            'week_start' => $weekDates['start']->format('Y-m-d'),
            'week_end' => $weekDates['end']->format('Y-m-d'),
            'total_app_profits' => round($totalAppProfits, 2),
            'max_reward_pool' => round($maxRewardPool, 2),
            'total_calculated_rewards' => round($totalCalculatedRewards, 2),
            'distribution_ratio' => round($distributionRatio, 4),
            'total_distributed' => round($totalCalculatedRewards * $distributionRatio, 2),
            'drivers_count' => count($distributedRewards),
            'rewards' => $distributedRewards,
        ];
    }
    
    /**
     * Calculate driver rewards based on performance
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $weekNumber
     * @param int $year
     * @return \Illuminate\Support\Collection
     */
    private function calculateDriverRewards(Carbon $startDate, Carbon $endDate, int $weekNumber, int $year)
    {
        // Get driver statistics for the week
        $driverStats = Transaction::select(
                'driver_id',
                DB::raw('COUNT(*) as total_trips'),
                DB::raw('SUM(driver_earnings) as total_earnings')
            )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'completed')
            ->groupBy('driver_id')
            ->get();
        
        $rewards = collect();
        
        foreach ($driverStats as $stats) {
            // Calculate reward based on performance tiers
            $calculatedReward = $this->calculateRewardAmount($stats->total_trips, $stats->total_earnings);
            
            // Create or update driver reward record
            $reward = DriverReward::updateOrCreate(
                [
                    'driver_id' => $stats->driver_id,
                    'week_number' => $weekNumber,
                    'year' => $year,
                ],
                [
                    'week_start_date' => $startDate->format('Y-m-d'),
                    'week_end_date' => $endDate->format('Y-m-d'),
                    'total_trips' => $stats->total_trips,
                    'total_earnings' => $stats->total_earnings,
                    'calculated_reward' => $calculatedReward,
                ]
            );
            
            $rewards->push($reward);
        }
        
        return $rewards;
    }
    
    /**
     * Calculate reward amount based on performance tiers
     * 
     * @param int $totalTrips
     * @param float $totalEarnings
     * @return float
     */
    private function calculateRewardAmount(int $totalTrips, float $totalEarnings): float
    {
        // Reward tiers based on number of trips
        $reward = 0;
        
        if ($totalTrips >= 50) {
            $reward = 50000; // 50,000 YER for 50+ trips
        } elseif ($totalTrips >= 30) {
            $reward = 30000; // 30,000 YER for 30-49 trips
        } elseif ($totalTrips >= 20) {
            $reward = 20000; // 20,000 YER for 20-29 trips
        } elseif ($totalTrips >= 10) {
            $reward = 10000; // 10,000 YER for 10-19 trips
        }
        
        // Bonus: Add 5% of earnings for high performers (30+ trips)
        if ($totalTrips >= 30) {
            $reward += ($totalEarnings * 0.05);
        }
        
        return round($reward, 2);
    }
    
    /**
     * Calculate total app profits for a week
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return float
     */
    private function calculateWeeklyAppProfits(Carbon $startDate, Carbon $endDate): float
    {
        return Transaction::whereBetween('created_at', [$startDate, $endDate])
            ->where('payment_status', 'completed')
            ->sum('app_commission');
    }
    
    /**
     * Get week date range
     * 
     * @param int $weekNumber
     * @param int $year
     * @return array
     */
    private function getWeekDateRange(int $weekNumber, int $year): array
    {
        $date = Carbon::now();
        $date->setISODate($year, $weekNumber);
        
        return [
            'start' => $date->copy()->startOfWeek(),
            'end' => $date->copy()->endOfWeek(),
        ];
    }
    
    /**
     * Get driver reward summary
     * 
     * @param int $driverId
     * @param int|null $weekNumber
     * @param int|null $year
     * @return DriverReward|null
     */
    public function getDriverReward(int $driverId, ?int $weekNumber = null, ?int $year = null): ?DriverReward
    {
        $weekNumber = $weekNumber ?? Carbon::now()->weekOfYear;
        $year = $year ?? Carbon::now()->year;
        
        return DriverReward::where('driver_id', $driverId)
            ->where('week_number', $weekNumber)
            ->where('year', $year)
            ->first();
    }
}
