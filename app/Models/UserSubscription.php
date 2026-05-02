<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_plan_id',
        'status',
        'starts_at',
        'expires_at',
        'next_billing_date',
        'trips_used',
        'total_savings',
        'verification_document',
        'verification_status',
        'rejection_reason',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'expires_at' => 'date',
        'next_billing_date' => 'date',
        'total_savings' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' 
            && now()->between($this->starts_at, $this->expires_at);
    }

    public function hasFreeTripAvailable(): bool
    {
        if (!$this->isActive()) return false;
        
        return $this->trips_used < $this->plan->free_trips_per_month;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('starts_at', '<=', now())
            ->where('expires_at', '>=', now());
    }
}
