<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Discount details
            $table->string('type')->comment('نسبة مئوية أو مبلغ ثابت');
            $table->decimal('value', 10, 2)->comment('القيمة');
            $table->decimal('max_discount', 10, 2)->nullable()->comment('الحد الأقصى للخصم');
            $table->decimal('min_trip_amount', 10, 2)->nullable()->comment('الحد الأدنى لسعر الرحلة');
            
            // Usage limits
            $table->integer('usage_limit')->nullable()->comment('عدد مرات الاستخدام الكلي');
            $table->integer('usage_per_user')->default(1)->comment('عدد مرات الاستخدام لكل مستخدم');
            $table->integer('used_count')->default(0);
            
            // Validity
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Targeting
            $table->json('allowed_categories')->nullable()->comment('الفئات المسموحة');
            $table->json('allowed_users')->nullable()->comment('مستخدمين محددين');
            $table->string('user_type')->default('all');
            
            $table->timestamps();
            
            $table->index('is_active');
            $table->index(['starts_at', 'expires_at']);
        });

        Schema::create('coupon_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coupon_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->decimal('discount_amount', 10, 2);
            $table->timestamps();
            
            $table->index(['coupon_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupon_usage');
        Schema::dropIfExists('coupons');
    }
};
