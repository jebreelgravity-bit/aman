<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pricing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('category')->unique();
            $table->decimal('base_fare', 10, 2)->comment('فتحة العداد');
            $table->decimal('price_per_km', 10, 2)->comment('سعر الكيلومتر');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default pricing
        DB::table('pricing_settings')->insert([
            [
                'category' => 'economy',
                'base_fare' => 300.00,
                'price_per_km' => 150.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'vip',
                'base_fare' => 500.00,
                'price_per_km' => 220.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'category' => 'bus',
                'base_fare' => 400.00,
                'price_per_km' => 180.00,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_settings');
    }
};
