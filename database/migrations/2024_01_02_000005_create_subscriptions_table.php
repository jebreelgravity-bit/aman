<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->comment('طالب، موظف، شركات');
            
            // Pricing
            $table->decimal('monthly_price', 10, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0)->comment('نسبة الخصم على الرحلات');
            $table->integer('free_trips_per_month')->default(0);
            
            // Features
            $table->json('features')->nullable();
            $table->boolean('priority_booking')->default(false);
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
        });

        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('cascade');
            
            // Subscription details
            $table->string('status')->default('pending');
            $table->date('starts_at');
            $table->date('expires_at');
            $table->date('next_billing_date')->nullable();
            
            // Usage tracking
            $table->integer('trips_used')->default(0);
            $table->decimal('total_savings', 10, 2)->default(0);
            
            // Verification (for students/employees)
            $table->string('verification_document')->nullable();
            $table->string('verification_status')->default('pending');
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
        Schema::dropIfExists('subscription_plans');
    }
};
