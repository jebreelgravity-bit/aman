<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LongDistanceTrip extends Model
{
    protected $fillable = [
        'name', 'origin_city', 'destination_city',
        'origin_lat', 'origin_lng', 'dest_lat', 'dest_lng',
        'distance_km', 'base_price', 'vehicle_type', 'max_passengers',
        'departure_time', 'frequency', 'departure_days',
        'is_active', 'notes', 'sort_order',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'departure_days' => 'array',
        'base_price'     => 'decimal:2',
        'distance_km'    => 'decimal:2',
    ];

    public function drivers()
    {
        return $this->belongsToMany(User::class, 'long_distance_trip_driver', 'long_distance_trip_id', 'driver_id')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function primaryDriver()
    {
        return $this->drivers()->wherePivot('is_primary', true)->first();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
