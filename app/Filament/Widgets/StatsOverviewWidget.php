<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Trip;
use App\Models\User;
use App\Models\Rating;
use App\Models\Transaction;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // ─── إجمالي الرحلات اليوم ───
        $todayTrips = Trip::whereDate('created_at', today())->count();
        $yesterdayTrips = Trip::whereDate('created_at', today()->subDay())->count();
        $tripsTrend = $yesterdayTrips > 0
            ? round((($todayTrips - $yesterdayTrips) / $yesterdayTrips) * 100, 1)
            : 0;

        // ─── السائقين النشطين ───
        $activeDrivers = User::where('role', 'driver')->where('is_active', true)->count();
        $totalDrivers = User::where('role', 'driver')->count();

        // ─── صافي الربح (العمولة) ───
        $totalRevenue = Trip::where('status', 'completed')->sum('final_price');
        $appCommission = Transaction::where('payment_status', 'completed')->sum('app_commission');
        $todayCommission = Transaction::where('payment_status', 'completed')
            ->whereDate('created_at', today())
            ->sum('app_commission');
        $yesterdayCommission = Transaction::where('payment_status', 'completed')
            ->whereDate('created_at', today()->subDay())
            ->sum('app_commission');
        $commissionTrend = $yesterdayCommission > 0
            ? round((($todayCommission - $yesterdayCommission) / $yesterdayCommission) * 100, 1)
            : 0;

        // ─── متوسط دخل السائق ───
        $driverCount = $activeDrivers ?: 1; // ديناميكي بدلاً من 15 الثابتة!
        $totalPayouts = Transaction::where('payment_status', 'completed')->sum('driver_earnings');
        $avgDriverIncome = round($totalPayouts / $driverCount, 2);

        // ─── معدل الإلغاء ───
        $totalTrips = Trip::count() ?: 1;
        $cancelledTrips = Trip::where('status', 'cancelled')->count();
        $cancellationRate = round(($cancelledTrips / $totalTrips) * 100, 1);

        // ─── الرحلات الجارية الآن ───
        $activeTrips = Trip::whereIn('status', ['pending', 'accepted', 'started'])->count();

        // ─── معدل رضا العملاء ───
        $avgRating = Rating::avg('driver_rating');
        $avgRating = $avgRating ? round($avgRating, 1) : 0;
        $totalRatings = Rating::count();

        // ─── مدفوعات معلقة ───
        $pendingPayments = Transaction::where('payment_status', 'pending')->count();

        return [
            Stat::make('🚗 رحلات اليوم', $todayTrips)
                ->description($tripsTrend >= 0 ? "↑ {$tripsTrend}% عن الأمس" : "↓ " . abs($tripsTrend) . "% عن الأمس")
                ->descriptionIcon($tripsTrend >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->chart($this->getLastWeekTrips())
                ->color($tripsTrend >= 0 ? 'success' : 'danger'),

            Stat::make('💰 عمولة اليوم', number_format($todayCommission) . ' ر.ي')
                ->description($commissionTrend >= 0 ? "↑ {$commissionTrend}% عن الأمس" : "↓ " . abs($commissionTrend) . "% عن الأمس")
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart($this->getLastWeekCommissions())
                ->color($commissionTrend >= 0 ? 'success' : 'warning'),

            Stat::make('🟢 رحلات جارية الآن', $activeTrips)
                ->description("{$activeDrivers} سائق نشط من {$totalDrivers}")
                ->descriptionIcon('heroicon-m-signal')
                ->color($activeTrips > 0 ? 'info' : 'gray'),

            Stat::make('📊 متوسط دخل السائق', number_format($avgDriverIncome) . ' ر.ي')
                ->description("محسوب على {$driverCount} سائق نشط")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('⭐ رضا العملاء', $avgRating . ' / 5.0')
                ->description("{$totalRatings} تقييم إجمالي")
                ->descriptionIcon('heroicon-m-star')
                ->color($avgRating >= 4 ? 'success' : ($avgRating >= 3 ? 'warning' : 'danger')),

            Stat::make('⚠️ معدل الإلغاء', $cancellationRate . '%')
                ->description("{$cancelledTrips} من {$totalTrips} رحلة" . ($pendingPayments > 0 ? " | {$pendingPayments} دفعة معلقة" : ''))
                ->descriptionIcon($cancellationRate > 10 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color($cancellationRate > 10 ? 'danger' : 'success'),
        ];
    }

    /**
     * مخطط صغير: رحلات آخر 7 أيام
     */
    private function getLastWeekTrips(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = Trip::whereDate('created_at', today()->subDays($i))->count();
        }
        return $data;
    }

    /**
     * مخطط صغير: عمولات آخر 7 أيام
     */
    private function getLastWeekCommissions(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $data[] = (int) Transaction::where('payment_status', 'completed')
                ->whereDate('created_at', today()->subDays($i))
                ->sum('app_commission');
        }
        return $data;
    }
}
