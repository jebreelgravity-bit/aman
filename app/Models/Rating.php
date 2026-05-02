<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'customer_id',
        'driver_id',
        'driver_rating',
        'service_rating',
        'cleanliness_rating',
        'comment',
        'tags',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function getAverageRatingAttribute(): float
    {
        $ratings = array_filter([
            $this->driver_rating,
            $this->service_rating,
            $this->cleanliness_rating,
        ]);
        
        return count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 2) : 0;
    }
}
