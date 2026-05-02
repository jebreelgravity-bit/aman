<?php

namespace App\Filament\Widgets;

use App\Models\Rating;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class DriverRatingsWidget extends Widget
{
    protected string $view = 'filament.widgets.driver-ratings-widget';

    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        // ─── المتوسط الكلي ───
        $overallAvg = Rating::avg('driver_rating');
        $overallAvg = $overallAvg ? round($overallAvg, 1) : 0;
        $totalRatings = Rating::count();
        $todayRatings = Rating::whereDate('created_at', today())->count();

        // ─── أفضل 5 سائقين ───
        $topDrivers = DB::table('ratings')
            ->join('users', 'ratings.driver_id', '=', 'users.id')
            ->select(
                'users.name',
                'users.id as driver_id',
                DB::raw('ROUND(AVG(ratings.driver_rating), 1) as avg_rating'),
                DB::raw('COUNT(*) as total_ratings')
            )
            ->groupBy('users.id', 'users.name')
            ->having('total_ratings', '>=', 1)
            ->orderByDesc('avg_rating')
            ->limit(5)
            ->get();

        // ─── تقييمات تحتاج مراجعة (≤ 2) ───
        $lowRatings = Rating::where('driver_rating', '<=', 2)
            ->with(['driver:id,name', 'customer:id,name'])
            ->latest()
            ->limit(5)
            ->get();

        $lowCount = Rating::where('driver_rating', '<=', 2)->count();

        // ─── الوسوم الأكثر تكراراً ───
        $allTags = Rating::whereNotNull('tags')
            ->where('tags', '!=', '[]')
            ->pluck('tags')
            ->flatMap(function ($tags) {
                if (is_string($tags)) {
                    $tags = json_decode($tags, true);
                }
                return is_array($tags) ? $tags : [];
            })
            ->countBy()
            ->sortDesc()
            ->take(8);

        // ─── توزيع التقييمات (1-5) ───
        $distribution = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = Rating::where('driver_rating', $i)->count();
            $distribution[$i] = [
                'count' => $count,
                'percentage' => $totalRatings > 0 ? round(($count / $totalRatings) * 100, 1) : 0,
            ];
        }

        return [
            'overallAvg' => $overallAvg,
            'totalRatings' => $totalRatings,
            'todayRatings' => $todayRatings,
            'topDrivers' => $topDrivers,
            'lowRatings' => $lowRatings,
            'lowCount' => $lowCount,
            'topTags' => $allTags,
            'distribution' => $distribution,
        ];
    }
}
