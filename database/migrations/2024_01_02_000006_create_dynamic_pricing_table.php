<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dynamic_pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            
            // Rule conditions
            $table->json('cities')->nullable()->comment('المدن المستهدفة');
            $table->json('areas')->nullable()->comment('المناطق المستهدفة');
            $table->time('start_time')->nullable()->comment('وقت البدء');
            $table->time('end_time')->nullable()->comment('وقت الانتهاء');
            $table->json('days_of_week')->nullable()->comment('أيام الأسبوع');
            $table->json('categories')->nullable()->comment('الفئات المستهدفة');
            
            // Pricing adjustments
            $table->string('adjustment_type')->default('multiplier');
            $table->decimal('adjustment_value', 10, 2)->comment('قيمة التعديل');
            
            // Priority and status
            $table->integer('priority')->default(0)->comment('الأولوية عند تطبيق عدة قواعد');
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dynamic_pricing_rules');
    }
};
