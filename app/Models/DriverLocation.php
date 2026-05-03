<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DriverLocation extends Model
{
    protected $fillable = [
        'driver_id', 'latitude', 'longitude',
        'speed', 'heading', 'accuracy', 'status', 'trip_id', 'recorded_at',
    ];

    protected $casts = [
        'latitude'    => 'decimal:7',
        'longitude'   => 'decimal:7',
        'recorded_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /** آخر موقع لسائق معين */
    public static function latestForDriver(int $driverId): ?self
    {
        return static::where('driver_id', $driverId)
            ->latest('recorded_at')
            ->first();
    }
}
