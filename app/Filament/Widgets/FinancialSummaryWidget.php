<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class FinancialSummaryWidget extends ChartWidget
{
    protected ?string $heading = '💰 الملخص المالي — طرق الدفع (آخر 30 يوم)';

    protected static ?int $sort = 7;

    public function getDescription(): ?string
    {
        $total = Transaction::where('payment_status', 'completed')
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('trip_price');

        $pending = Transaction::where('payment_status', 'pending')->count();
        $underReview = Transaction::where('payment_status', 'under_review')->count();

        $alerts = [];
        if ($pending > 0) $alerts[] = "⏳ {$pending} معلقة";
        if ($underReview > 0) $alerts[] = "🔍 {$underReview} قيد المراجعة";

        return 'إجمالي: ' . number_format($total) . ' ر.ي' .
            (count($alerts) > 0 ? ' | ' . implode(' | ', $alerts) : '');
    }

    protected function getData(): array
    {
        $thirtyDaysAgo = now()->subDays(30);

        $methods = [
            'cash' => ['label' => '💵 نقدي', 'color' => 'rgba(34, 197, 94, 0.8)'],
            'e_wallet' => ['label' => '📱 محفظة إلكترونية', 'color' => 'rgba(59, 130, 246, 0.8)'],
            'bank_transfer' => ['label' => '🏦 تحويل بنكي', 'color' => 'rgba(245, 158, 11, 0.8)'],
            'mobile_money' => ['label' => '📲 موبايل موني', 'color' => 'rgba(139, 92, 246, 0.8)'],
        ];

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($methods as $key => $meta) {
            $amount = Transaction::where('payment_method', $key)
                ->where('payment_status', 'completed')
                ->where('created_at', '>=', $thirtyDaysAgo)
                ->sum('trip_price');

            if ($amount > 0) {
                $labels[] = $meta['label'];
                $data[] = round($amount);
                $colors[] = $meta['color'];
            }
        }

        // إذا لا توجد بيانات، أضف placeholder
        if (empty($data)) {
            $labels = ['لا توجد بيانات بعد'];
            $data = [1];
            $colors = ['rgba(156, 163, 175, 0.5)'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'المبلغ (ر.ي)',
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 15,
                        'usePointStyle' => true,
                    ],
                ],
            ],
            'cutout' => '60%',
        ];
    }
}
