<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\ChartWidget;

class CustomerSegmentationWidget extends ChartWidget
{
    protected ?string $heading = 'تحليل العملاء: جدد مقابل عائدون';

    protected static ?int $sort = 4;

    protected function getData(): array
    {
        $analytics = new AnalyticsService();
        $data = $analytics->getNewVsReturningCustomers();

        return [
            'datasets' => [
                [
                    'label' => 'العملاء',
                    'data' => $data['data'],
                    'backgroundColor' => [
                        'rgba(99, 102, 241, 0.8)',  // Indigo for new
                        'rgba(168, 85, 247, 0.8)',  // Purple for returning
                    ],
                ],
            ],
            'labels' => $data['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
        ];
    }
}
