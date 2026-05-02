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
        Schema::create('driver_rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            
            // Week tracking
            $table->integer('week_number')->comment('رقم الأسبوع في السنة');
            $table->integer('year');
            $table->date('week_start_date');
            $table->date('week_end_date');
            
            // Performance metrics
            $table->integer('total_trips')->default(0)->comment('عدد الرحلات');
            $table->decimal('total_earnings', 10, 2)->default(0)->comment('إجمالي أرباح السائق');
            
            // Reward calculation
            $table->decimal('calculated_reward', 10, 2)->default(0)->comment('المكافأة المحسوبة');
            $table->decimal('actual_reward', 10, 2)->default(0)->comment('المكافأة الفعلية بعد التوزيع النسبي');
            $table->boolean('is_distributed')->default(false);
            $table->timestamp('distributed_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Unique constraint to prevent duplicate rewards
            $table->unique(['driver_id', 'week_number', 'year']);
            
            // Indexes
            $table->index(['week_number', 'year']);
            $table->index('is_distributed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_rewards');
    }
};
