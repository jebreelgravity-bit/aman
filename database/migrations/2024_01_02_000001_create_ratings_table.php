<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            
            // Ratings (1-5 stars)
            $table->unsignedTinyInteger('driver_rating')->comment('تقييم السائق');
            $table->unsignedTinyInteger('service_rating')->comment('تقييم الخدمة');
            $table->unsignedTinyInteger('cleanliness_rating')->nullable()->comment('تقييم النظافة');
            
            // Feedback
            $table->text('comment')->nullable();
            $table->json('tags')->nullable()->comment('وسوم: مهذب، سريع، آمن، إلخ');
            
            $table->timestamps();
            
            $table->index(['driver_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
