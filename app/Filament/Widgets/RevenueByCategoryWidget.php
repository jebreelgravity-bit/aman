<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\ChartWidget;

class RevenueByCategoryWidget extends ChartWidget
{
    protected ?string $heading = 'توزيع الإيرادات حسب نوع الخدمة';
    
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $analytics = new AnalyticsService();
        $data = $analytics->getRevenueByCategory();

        return [
            'datasets' => [
                [
                    'label' => 'الإيرادات (ر.ي)',
                    'data' => $data['data'],
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.8)',  // Green for economy
                        'rgba(234, 179, 8, 0.8)',  // Yellow for VIP
                        'rgba(59, 130, 246, 0.8)', // Blue for bus
                    ],
                ],
            ],
            'labels' => $data['labels'],
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
                ],
            ],
        ];
    }
}
