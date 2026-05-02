<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DynamicPricingRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'cities',
        'areas',
        'start_time',
        'end_time',
        'days_of_week',
        'categories',
        'adjustment_type',
        'adjustment_value',
        'priority',
        'is_active',
    ];

    protected $casts = [
        'cities' => 'array',
        'areas' => 'array',
        'days_of_week' => 'array',
        'categories' => 'array',
        'adjustment_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function applies(array $conditions): bool
    {
        if (!$this->is_active) return false;

        // Check time
        if ($this->start_time && $this->end_time) {
            $currentTime = now()->format('H:i:s');
            if ($currentTime < $this->start_time || $currentTime > $this->end_time) {
                return false;
            }
        }

        // Check day of week
        if ($this->days_of_week && !in_array(now()->dayOfWeek, $this->days_of_week)) {
            return false;
        }

        // Check category
        if ($this->categories && isset($conditions['category'])) {
            if (!in_array($conditions['category'], $this->categories)) {
                return false;
            }
        }

        // Check city/area
        if ($this->cities && isset($conditions['city'])) {
            if (!in_array($conditions['city'], $this->cities)) {
                return false;
            }
        }

        return true;
    }

    public function adjustPrice(float $basePrice): float
    {
        switch ($this->adjustment_type) {
            case 'multiplier':
                return $basePrice * $this->adjustment_value;
            case 'percentage':
                return $basePrice * (1 + ($this->adjustment_value / 100));
            case 'fixed_increase':
                return $basePrice + $this->adjustment_value;
            default:
                return $basePrice;
        }
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('priority', 'desc');
    }
}
