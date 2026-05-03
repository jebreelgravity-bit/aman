<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── جدولة توزيع مكافآت السائقين الأسبوعية ───
// يتم التشغيل كل اثنين الساعة 8:00 صباحاً
Schedule::command('rewards:distribute')
    ->weekly()
    ->mondays()
    ->at('08:00')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/rewards-scheduler.log'));
