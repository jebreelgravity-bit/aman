<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Trip;
use App\Models\PricingSetting;
use Laravel\Sanctum\Sanctum;

class WalletTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_check_wallet_balance()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        $response = $this->getJson('/api/wallet/balance');

        $response->assertStatus(200)
                 ->assertJsonStructure(['status', 'data' => ['balance', 'currency']]);
    }

    public function test_user_can_deposit_funds()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallet/deposit', [
            'amount' => 5000
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('wallet_transactions', [
            'amount' => 5000,
            'type'   => 'credit',
        ]);
    }

    public function test_trip_payment_flow()
    {
        // 1. إنشاء عميل وسائق
        $customer = User::factory()->create(['role' => 'customer']);
        $driver   = User::factory()->create(['role' => 'driver']);

        // 2. إعداد التسعير
        PricingSetting::updateOrCreate(
            ['category' => 'economy'],
            [
                'base_fare'    => 300,
                'price_per_km' => 150,
                'is_active'    => true,
            ]
        );

        // 3. إيداع رصيد للعميل
        Sanctum::actingAs($customer);
        $this->postJson('/api/wallet/deposit', ['amount' => 10000]);

        // 4. إنشاء رحلة في حالة "started" مباشرةً
        $trip = Trip::create([
            'customer_id'       => $customer->id,
            'driver_id'         => $driver->id,
            'category'          => 'economy',
            'pickup_address'    => 'عنوان الانطلاق',
            'pickup_latitude'   => 33.0,
            'pickup_longitude'  => 44.0,
            'dropoff_address'   => 'عنوان الوصول',
            'dropoff_latitude'  => 33.1,
            'dropoff_longitude' => 44.1,
            'distance_km'       => 10,
            'estimated_price'   => 1800, // 300 + (150 * 10)
            'status'            => 'started',
        ]);

        // 5. السائق ينهي الرحلة
        Sanctum::actingAs($driver);
        $response = $this->postJson("/api/trips/{$trip->id}/complete");
        $response->assertStatus(200);

        // 6. التحقق من رصيد العميل: 10000 - 1800 = 8200
        $customerWallet = $customer->fresh()->wallet;
        $this->assertNotNull($customerWallet);
        $this->assertEquals(8200.0, (float) $customerWallet->balance);

        // 7. التحقق من رصيد السائق: 1800 * 0.80 = 1440
        $driverWallet = $driver->fresh()->wallet;
        $this->assertNotNull($driverWallet);
        $this->assertEquals(1440.0, (float) $driverWallet->balance);
    }
}
