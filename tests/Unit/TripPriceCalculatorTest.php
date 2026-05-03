<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\TripPriceCalculator;
use App\Models\PricingSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TripPriceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculate_price()
    {
        PricingSetting::updateOrCreate(
            ['category' => 'economy'],
            [
                'base_fare' => 300,
                'price_per_km' => 150,
                'is_active' => true
            ]
        );

        $calculator = new TripPriceCalculator();
        $pricing = $calculator->calculate('economy', 10);

        $this->assertEquals(300, $pricing['base_fare']);
        $this->assertEquals(1500, $pricing['distance_cost']);
        $this->assertEquals(1800, $pricing['total_price']);
    }

    public function test_financial_breakdown()
    {
        $calculator = new TripPriceCalculator();
        $breakdown = $calculator->calculateFinancialBreakdown(1000);

        $this->assertEquals(1000, $breakdown['trip_price']);
        $this->assertEquals(20, $breakdown['app_commission_rate']);
        $this->assertEquals(200, $breakdown['app_commission']);
        $this->assertEquals(800, $breakdown['driver_earnings']);
    }
}
