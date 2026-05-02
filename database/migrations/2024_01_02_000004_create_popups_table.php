<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('popups', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('image_url')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_url')->nullable();
            
            // Targeting
            $table->string('target_audience')->default('all');
            $table->json('target_cities')->nullable();
            
            // Display settings
            $table->string('display_frequency')->default('once');
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            
            // Scheduling
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            
            // Analytics
            $table->integer('views_count')->default(0);
            $table->integer('clicks_count')->default(0);
            
            $table->timestamps();
            
            $table->index('is_active');
        });

        Schema::create('popup_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('popup_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action');
            $table->timestamp('created_at');
            
            $table->index(['popup_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('popup_interactions');
        Schema::dropIfExists('popups');
    }
};
