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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            
            // Financial breakdown
            $table->decimal('trip_price', 10, 2)->comment('سعر الرحلة الكلي');
            $table->decimal('app_commission_rate', 5, 2)->default(20.00)->comment('نسبة عمولة التطبيق %');
            $table->decimal('app_commission', 10, 2)->comment('عمولة التطبيق');
            $table->decimal('driver_earnings', 10, 2)->comment('صافي ربح السائق');
            
            // Payment details
            $table->string('payment_method')->default('cash');
            $table->string('payment_status')->default('pending');
            $table->timestamp('paid_at')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['driver_id', 'created_at']);
            $table->index('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
