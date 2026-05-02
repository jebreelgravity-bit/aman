<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'monthly_price',
        'discount_percentage',
        'free_trips_per_month',
        'features',
        'priority_booking',
        'is_active',
    ];

    protected $casts = [
        'monthly_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'features' => 'array',
        'priority_booking' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscriptions()
    {
        return $this->hasMany(UserSubscription::class)->where('status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
