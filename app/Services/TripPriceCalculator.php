<?php

namespace App\Services;

use App\Models\PricingSetting;
use Illuminate\Support\Facades\Cache;

class TripPriceCalculator
{
    /**
     * Calculate trip price based on Amaan algorithm
     * 
     * @param string $category Category type: economy, vip, bus
     * @param float $distanceKm Distance in kilometers
     * @return array Contains breakdown of pricing
     */
    public function calculate(string $category, float $distanceKm): array
    {
        // Get pricing settings from cache or database
        $pricing = $this->getPricingSettings($category);
        
        if (!$pricing) {
            throw new \Exception("Pricing settings not found for category: {$category}");
        }
        
        // Calculate total price: base_fare + (price_per_km * distance)
        $baseFare = (float) $pricing->base_fare;
        $pricePerKm = (float) $pricing->price_per_km;
        $totalPrice = $baseFare + ($pricePerKm * $distanceKm);
        
        return [
            'category' => $category,
            'distance_km' => $distanceKm,
            'base_fare' => $baseFare,
            'price_per_km' => $pricePerKm,
            'distance_cost' => $pricePerKm * $distanceKm,
            'total_price' => round($totalPrice, 2),
            'currency' => 'YER', // Yemeni Rial
        ];
    }
    
    /**
     * Calculate financial breakdown for a trip
     * 
     * @param float $tripPrice Total trip price
     * @param float $commissionRate App commission rate (default 20%)
     * @return array Financial breakdown
     */
    public function calculateFinancialBreakdown(float $tripPrice, float $commissionRate = 20.00): array
    {
        $appCommission = ($tripPrice * $commissionRate) / 100;
        $driverEarnings = $tripPrice - $appCommission;
        
        return [
            'trip_price' => round($tripPrice, 2),
            'app_commission_rate' => $commissionRate,
            'app_commission' => round($appCommission, 2),
            'driver_earnings' => round($driverEarnings, 2),
        ];
    }
    
    /**
     * Get pricing settings with caching
     * 
     * @param string $category
     * @return PricingSetting|null
     */
    private function getPricingSettings(string $category): ?PricingSetting
    {
        return Cache::remember("pricing_settings_{$category}", 3600, function () use ($category) {
            return PricingSetting::where('category', $category)
                ->where('is_active', true)
                ->first();
        });
    }
    
    /**
     * Clear pricing cache (call this when pricing settings are updated)
     */
    public static function clearCache(): void
    {
        Cache::forget('pricing_settings_economy');
        Cache::forget('pricing_settings_vip');
        Cache::forget('pricing_settings_bus');
    }
    
    /**
     * Validate category
     * 
     * @param string $category
     * @return bool
     */
    public function isValidCategory(string $category): bool
    {
        return in_array($category, ['economy', 'vip', 'bus']);
    }
    
    /**
     * Get all active pricing settings
     * 
     * @return array
     */
    public function getAllPricing(): array
    {
        $categories = ['economy', 'vip', 'bus'];
        $pricing = [];
        
        foreach ($categories as $category) {
            $settings = $this->getPricingSettings($category);
            if ($settings) {
                $pricing[$category] = [
                    'base_fare' => (float) $settings->base_fare,
                    'price_per_km' => (float) $settings->price_per_km,
                    'is_active' => $settings->is_active,
                ];
            }
        }
        
        return $pricing;
    }
}
