<?php

namespace App\Filament\Widgets;

use App\Models\Trip;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DemandChartWidget extends ChartWidget
{
    protected ?string $heading = '📈 خريطة الطلب — أعلى ساعات الذروة (آخر 7 أيام)';

    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        // تحليل الطلب حسب يوم الأسبوع والساعة (آخر 7 أيام)
        $data = Trip::where('created_at', '>=', now()->subDays(7))
            ->selectRaw("
                DAYOFWEEK(created_at) - 1 as day_of_week,
                HOUR(created_at) as hour,
                COUNT(*) as trips_count
            ")
            ->groupBy('day_of_week', 'hour')
            ->orderBy('trips_count', 'desc')
            ->get();

        // تجميع البيانات حسب اليوم
        $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        $tripsPerDay = [];

        foreach ($days as $index => $dayName) {
            $tripsPerDay[] = $data->where('day_of_week', $index)->sum('trips_count');
        }

        // تجميع حسب الفترة الزمنية
        $periods = [
            'الفجر (4-7)' => range(4, 6),
            'الصباح (7-10)' => range(7, 9),
            'الظهيرة (10-13)' => range(10, 12),
            'العصر (13-16)' => range(13, 15),
            'المساء (16-19)' => range(16, 18),
            'الليل (19-22)' => range(19, 21),
            'متأخر (22-4)' => [22, 23, 0, 1, 2, 3],
        ];

        $tripsPerPeriod = [];
        foreach ($periods as $periodName => $hours) {
            $tripsPerPeriod[] = $data->whereIn('hour', $hours)->sum('trips_count');
        }

        return [
            'datasets' => [
                [
                    'label' => 'رحلات حسب اليوم',
                    'data' => $tripsPerDay,
                    'backgroundColor' => [
                        'rgba(239, 68, 68, 0.7)',   // أحد
                        'rgba(59, 130, 246, 0.7)',   // إثنين
                        'rgba(16, 185, 129, 0.7)',   // ثلاثاء
                        'rgba(245, 158, 11, 0.7)',   // أربعاء
                        'rgba(139, 92, 246, 0.7)',   // خميس
                        'rgba(236, 72, 153, 0.7)',   // جمعة
                        'rgba(20, 184, 166, 0.7)',   // سبت
                    ],
                    'borderWidth' => 2,
                    'borderRadius' => 8,
                ],
            ],
            'labels' => $days,
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
                    'title' => [
                        'display' => true,
                        'text' => 'عدد الرحلات',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'أيام الأسبوع',
                    ],
                ],
            ],
        ];
    }

    public function getDescription(): ?string
    {
        $peakDay = Trip::where('created_at', '>=', now()->subDays(7))
            ->selectRaw("DAYOFWEEK(created_at) - 1 as day, COUNT(*) as cnt")
            ->groupBy('day')
            ->orderByDesc('cnt')
            ->first();

        if (!$peakDay) {
            return 'لا توجد بيانات كافية بعد';
        }

        $days = ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        return '🔥 أعلى يوم طلب: ' . ($days[$peakDay->day] ?? '—') . " ({$peakDay->cnt} رحلة)";
    }
}
