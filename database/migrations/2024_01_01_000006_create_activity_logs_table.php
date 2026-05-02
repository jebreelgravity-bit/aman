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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Activity details
            $table->string('action')->comment('نوع العملية');
            $table->string('model_type')->nullable()->comment('نوع النموذج');
            $table->unsignedBigInteger('model_id')->nullable()->comment('معرف النموذج');
            
            // Changes tracking
            $table->json('old_values')->nullable()->comment('القيم القديمة');
            $table->json('new_values')->nullable()->comment('القيم الجديدة');
            
            // Request information
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('description')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
