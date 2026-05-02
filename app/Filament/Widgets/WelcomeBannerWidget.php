<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use App\Models\Trip;
use App\Models\User;
use App\Models\Complaint;

class WelcomeBannerWidget extends Widget
{
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = -10;

    protected string $view = 'filament.widgets.welcome-banner-widget';

    protected function getViewData(): array
    {
        $hour = now()->hour;
        if ($hour < 12) {
            $greeting = '☀️ صباح الخير';
        } elseif ($hour < 18) {
            $greeting = '🌤️ مساء الخير';
        } else {
            $greeting = '🌙 مساء النور';
        }

        $todayTrips = Trip::whereDate('created_at', today())->count();
        $activeTrips = Trip::whereIn('status', ['pending', 'accepted', 'started'])->count();
        $onlineDrivers = User::where('role', 'driver')->where('is_active', true)->count();
        $urgentComplaints = Complaint::where('status', 'open')->where('priority', 'urgent')->count();

        return compact('greeting', 'todayTrips', 'activeTrips', 'onlineDrivers', 'urgentComplaints');
    }
}
