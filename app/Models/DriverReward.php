<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'week_number',
        'year',
        'week_start_date',
        'week_end_date',
        'total_trips',
        'total_earnings',
        'calculated_reward',
        'actual_reward',
        'is_distributed',
        'distributed_at',
        'notes',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'total_earnings' => 'decimal:2',
        'calculated_reward' => 'decimal:2',
        'actual_reward' => 'decimal:2',
        'is_distributed' => 'boolean',
        'distributed_at' => 'datetime',
    ];

    /**
     * Get the driver for the reward
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Scope: Filter by week
     */
    public function scopeByWeek($query, int $weekNumber, int $year)
    {
        return $query->where('week_number', $weekNumber)
                    ->where('year', $year);
    }

    /**
     * Scope: Filter by driver
     */
    public function scopeByDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    /**
     * Scope: Distributed rewards
     */
    public function scopeDistributed($query)
    {
        return $query->where('is_distributed', true);
    }

    /**
     * Scope: Pending rewards
     */
    public function scopePending($query)
    {
        return $query->where('is_distributed', false);
    }

    /**
     * Check if reward is distributed
     */
    public function isDistributed(): bool
    {
        return $this->is_distributed;
    }

    /**
     * Get reduction percentage (if reward was reduced)
     */
    public function getReductionPercentage(): float
    {
        if ($this->calculated_reward == 0) {
            return 0;
        }

        $reduction = (($this->calculated_reward - $this->actual_reward) / $this->calculated_reward) * 100;
        return round($reduction, 2);
    }
}
