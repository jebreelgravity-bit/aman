<?php

namespace App\Models;

use App\Services\ActivityLogger;
use App\Services\TripPriceCalculator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PricingSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'category',
        'base_fare',
        'price_per_km',
        'is_active',
    ];

    protected $casts = [
        'base_fare' => 'decimal:2',
        'price_per_km' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        // Log pricing updates
        static::updating(function ($pricingSetting) {
            $oldValues = [
                'base_fare' => $pricingSetting->getOriginal('base_fare'),
                'price_per_km' => $pricingSetting->getOriginal('price_per_km'),
                'is_active' => $pricingSetting->getOriginal('is_active'),
            ];

            $newValues = [
                'base_fare' => $pricingSetting->base_fare,
                'price_per_km' => $pricingSetting->price_per_km,
                'is_active' => $pricingSetting->is_active,
            ];

            ActivityLogger::logPricingUpdate($pricingSetting, $oldValues, $newValues);
            
            // Clear cache when pricing is updated
            TripPriceCalculator::clearCache();
        });
    }

    /**
     * Get trips using this pricing category
     */
    public function trips()
    {
        return $this->hasMany(Trip::class, 'category', 'category');
    }
}
