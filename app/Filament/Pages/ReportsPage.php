<?php

namespace App\Filament\Pages;

use App\Models\Trip;
use App\Models\Transaction;
use App\Models\Rating;
use App\Models\User;
use App\Models\Complaint;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ReportsPage extends Page
{
    protected string $view = 'filament.pages.reports-page';

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationLabel = '📊 التقارير';

    protected static ?string $title = 'مركز التقارير';

    protected static \UnitEnum|string|null $navigationGroup = 'التقارير والتحليلات';

    protected static ?int $navigationSort = 1;

    protected function getViewData(): array
    {
        // ─── ملخص عام ───
        $totalTrips = Trip::count();
        $completedTrips = Trip::where('status', 'completed')->count();
        $cancelledTrips = Trip::where('status', 'cancelled')->count();
        $completionRate = $totalTrips > 0 ? round(($completedTrips / $totalTrips) * 100, 1) : 0;
        $cancellationRate = $totalTrips > 0 ? round(($cancelledTrips / $totalTrips) * 100, 1) : 0;

        // ─── مالي ───
        $totalRevenue = Transaction::where('payment_status', 'completed')->sum('trip_price');
        $totalCommission = Transaction::where('payment_status', 'completed')->sum('app_commission');
        $totalPayouts = Transaction::where('payment_status', 'completed')->sum('driver_earnings');
        $pendingPayments = Transaction::where('payment_status', 'pending')->sum('trip_price');

        // ─── مستخدمين ───
        $totalDrivers = User::where('role', 'driver')->count();
        $activeDrivers = User::where('role', 'driver')->where('is_active', true)->count();
        $totalCustomers = User::where('role', 'customer')->count();

        // ─── تقييمات ───
        $avgRating = Rating::avg('driver_rating');
        $avgRating = $avgRating ? round($avgRating, 1) : 0;
        $totalRatings = Rating::count();

        // ─── شكاوى ───
        $openComplaints = Complaint::where('status', 'open')->count();
        $totalComplaints = Complaint::count();
        $resolvedComplaints = Complaint::where('status', 'resolved')->count();
        $resolutionRate = $totalComplaints > 0 ? round(($resolvedComplaints / $totalComplaints) * 100, 1) : 0;

        // ─── أفضل 10 سائقين بالأرباح ───
        $topEarningDrivers = DB::table('transactions')
            ->join('users', 'transactions.driver_id', '=', 'users.id')
            ->where('transactions.payment_status', 'completed')
            ->select(
                'users.name',
                DB::raw('SUM(transactions.driver_earnings) as total_earnings'),
                DB::raw('COUNT(transactions.id) as total_trips')
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_earnings')
            ->limit(10)
            ->get();

        // ─── إيرادات آخر 30 يوم ───
        $dailyRevenue = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $dailyRevenue[] = [
                'date' => now()->subDays($i)->format('m/d'),
                'revenue' => (int) Transaction::where('payment_status', 'completed')
                    ->whereDate('created_at', $date)
                    ->sum('app_commission'),
            ];
        }

        // ─── توزيع طرق الدفع ───
        $paymentMethods = Transaction::where('payment_status', 'completed')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(trip_price) as total')
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn($item) => [
                match ($item->payment_method) {
                    'cash' => '💵 نقدي',
                    'e_wallet' => '📱 محفظة',
                    'bank_transfer' => '🏦 تحويل',
                    'mobile_money' => '📲 موبايل',
                    default => $item->payment_method,
                } => [
                    'count' => $item->count,
                    'total' => number_format($item->total),
                ]
            ]);

        // ─── توزيع الفئات ───
        $categoryDistribution = Trip::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(fn($item) => [
                match ($item->category) {
                    'economy' => 'توفير', 'vip' => 'VIP', 'bus' => 'باص', default => $item->category,
                } => $item->count
            ]);

        return compact(
            'totalTrips', 'completedTrips', 'cancelledTrips', 'completionRate', 'cancellationRate',
            'totalRevenue', 'totalCommission', 'totalPayouts', 'pendingPayments',
            'totalDrivers', 'activeDrivers', 'totalCustomers',
            'avgRating', 'totalRatings',
            'openComplaints', 'totalComplaints', 'resolutionRate',
            'topEarningDrivers', 'dailyRevenue', 'paymentMethods', 'categoryDistribution'
        );
    }
}
