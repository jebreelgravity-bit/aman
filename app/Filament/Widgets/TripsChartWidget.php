<?php

namespace App\Filament\Widgets;

use App\Services\AnalyticsService;
use Filament\Widgets\ChartWidget;

class TripsChartWidget extends ChartWidget
{
    protected ?string $heading = 'توزيع الرحلات خلال 24 ساعة';
    
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $analytics = new AnalyticsService();
        $distribution = $analytics->getTripsDistributionByHour();

        return [
            'datasets' => [
                [
                    'label' => 'عدد الرحلات',
                    'data' => $distribution['data'],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $distribution['labels'],
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
