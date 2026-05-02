<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class WeeklyRevenueComparisonWidget extends ChartWidget
{
    protected ?string $heading = '📈 مقارنة الإيرادات — هذا الأسبوع مقابل الأسبوع الماضي';

    protected static ?int $sort = 9;

    protected int|string|array $columnSpan = 'full';

    public function getDescription(): ?string
    {
        $thisWeek = Transaction::where('payment_status', 'completed')
            ->where('created_at', '>=', now()->startOfWeek())
            ->sum('app_commission');

        $lastWeek = Transaction::where('payment_status', 'completed')
            ->whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->startOfWeek()])
            ->sum('app_commission');

        $growth = $lastWeek > 0
            ? round((($thisWeek - $lastWeek) / $lastWeek) * 100, 1)
            : 0;

        $icon = $growth >= 0 ? '📈' : '📉';
        return "{$icon} العمولات: " . number_format($thisWeek) . " ر.ي هذا الأسبوع | " .
            ($growth >= 0 ? "+{$growth}%" : "{$growth}%") . " عن الأسبوع الماضي";
    }

    protected function getData(): array
    {
        $days = ['السبت', 'الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];

        $thisWeekData = [];
        $lastWeekData = [];

        $startOfThisWeek = now()->startOfWeek();
        $startOfLastWeek = now()->subWeek()->startOfWeek();

        for ($i = 0; $i < 7; $i++) {
            $thisWeekData[] = (int) Transaction::where('payment_status', 'completed')
                ->whereDate('created_at', $startOfThisWeek->copy()->addDays($i))
                ->sum('app_commission');

            $lastWeekData[] = (int) Transaction::where('payment_status', 'completed')
                ->whereDate('created_at', $startOfLastWeek->copy()->addDays($i))
                ->sum('app_commission');
        }

        return [
            'datasets' => [
                [
                    'label' => 'هذا الأسبوع',
                    'data' => $thisWeekData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 3,
                    'pointRadius' => 4,
                    'pointBackgroundColor' => 'rgb(34, 197, 94)',
                ],
                [
                    'label' => 'الأسبوع الماضي',
                    'data' => $lastWeekData,
                    'borderColor' => 'rgb(156, 163, 175)',
                    'backgroundColor' => 'rgba(156, 163, 175, 0.05)',
                    'fill' => true,
                    'tension' => 0.4,
                    'borderWidth' => 2,
                    'borderDash' => [5, 5],
                    'pointRadius' => 3,
                    'pointBackgroundColor' => 'rgb(156, 163, 175)',
                ],
            ],
            'labels' => $days,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'العمولة (ر.ي)',
                    ],
                ],
            ],
        ];
    }
}
