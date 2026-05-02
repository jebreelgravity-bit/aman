<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'max_discount',
        'min_trip_amount',
        'usage_limit',
        'usage_per_user',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
        'allowed_categories',
        'allowed_users',
        'user_type',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_trip_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'allowed_categories' => 'array',
        'allowed_users' => 'array',
    ];

    public function usages()
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        
        return true;
    }

    public function canBeUsedBy(User $user): bool
    {
        if (!$this->isValid()) return false;
        
        $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
        if ($userUsageCount >= $this->usage_per_user) return false;
        
        if ($this->allowed_users && !in_array($user->id, $this->allowed_users)) return false;
        
        return true;
    }

    public function calculateDiscount(float $tripAmount): float
    {
        if ($this->type === 'percentage') {
            $discount = ($tripAmount * $this->value) / 100;
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }
        } else {
            $discount = $this->value;
        }
        
        return round(min($discount, $tripAmount), 2);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            });
    }
}
