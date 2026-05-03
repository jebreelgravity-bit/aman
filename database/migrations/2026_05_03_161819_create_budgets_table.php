<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('budget_categories')->onDelete('cascade');
            $table->decimal('amount', 15, 2);                          // المبلغ المخصص
            $table->enum('period_type', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->unsignedSmallInteger('period_year');               // 2025, 2026...
            $table->unsignedTinyInteger('period_month')->nullable();   // 1-12 للشهري
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();

            $table->unique(['category_id', 'period_type', 'period_year', 'period_month'], 'budgets_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
