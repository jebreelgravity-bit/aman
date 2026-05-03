<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('image')->nullable()->comment('صورة البانر');
            $table->string('link')->nullable()->comment('الرابط عند النقر');
            $table->string('bg_color', 20)->default('#FFD700')->comment('لون الخلفية');
            $table->string('text_color', 20)->default('#000000');
            $table->enum('type', ['top_bar', 'popup', 'card', 'full'])->default('top_bar');
            $table->enum('position', ['home', 'booking', 'all'])->default('all');
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_banners');
    }
};
