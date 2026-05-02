<?php

namespace App\Models;

use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'driver_id',
        'customer_id',
        'trip_price',
        'app_commission_rate',
        'app_commission',
        'driver_earnings',
        'payment_method',
        'payment_provider',
        'payment_reference',
        'payment_status',
        'paid_at',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'trip_price' => 'decimal:2',
        'app_commission_rate' => 'decimal:2',
        'app_commission' => 'decimal:2',
        'driver_earnings' => 'decimal:2',
        'paid_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Log transaction creation
        static::created(function ($transaction) {
            ActivityLogger::logTransactionCreated($transaction);
        });
    }

    /**
     * Get the trip for the transaction
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the driver for the transaction
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    /**
     * Get the customer for the transaction
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Scope: Filter by payment status
     */
    public function scopePaymentStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope: Filter by driver
     */
    public function scopeByDriver($query, int $driverId)
    {
        return $query->where('driver_id', $driverId);
    }

    /**
     * Scope: Completed payments
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'completed');
    }

    /**
     * Mark transaction as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => 'completed',
            'paid_at' => now(),
        ]);
    }

    /**
     * Check if transaction is completed
     */
    public function isCompleted(): bool
    {
        return $this->payment_status === 'completed';
    }
}
