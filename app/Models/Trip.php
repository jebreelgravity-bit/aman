<?php

namespace App\Models;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'driver_id',
        'category',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'dropoff_address',
        'dropoff_latitude',
        'dropoff_longitude',
        'distance_km',
        'estimated_price',
        'final_price',
        'status',
        'accepted_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'pickup_latitude' => 'decimal:8',
        'pickup_longitude' => 'decimal:8',
        'dropoff_latitude' => 'decimal:8',
        'dropoff_longitude' => 'decimal:8',
        'distance_km' => 'decimal:2',
        'estimated_price' => 'decimal:2',
        'final_price' => 'decimal:2',
        'accepted_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Log trip creation
        static::created(function ($trip) {
            ActivityLogger::logTripCreated($trip);
        });

        // Log status changes
        static::updating(function ($trip) {
            if ($trip->isDirty('status')) {
                $oldStatus = $trip->getOriginal('status');
                $newStatus = $trip->status;
                ActivityLogger::logTripStatusChange($trip, $oldStatus, $newStatus);
            }
        });
    }

    /**
     * Get the customer for the trip
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Get the driver for the trip
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the transaction for the trip
     */
    public function transaction()
    {
        return $this->hasOne(Transaction::class);
    }

    /**
     * Get the pricing setting for this trip category
     */
    public function pricingSetting()
    {
        return $this->belongsTo(PricingSetting::class, 'category', 'category');
    }

    /**
     * Scope: Filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by driver
     */
    public function scopeByDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    /**
     * Scope: Filter by customer
     */
    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope: Completed trips
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if trip is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if trip is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if trip is in progress
     */
    public function isInProgress(): bool
    {
        return in_array($this->status, ['accepted', 'started']);
    }
}
