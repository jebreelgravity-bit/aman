<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // جدول مواقع السائقين في الوقت الحقيقي (GPS)
        Schema::create('driver_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->float('speed', 5, 2)->nullable()->comment('السرعة كم/ساعة');
            $table->float('heading', 5, 2)->nullable()->comment('الاتجاه بالدرجات');
            $table->float('accuracy', 6, 2)->nullable()->comment('دقة GPS بالمتر');
            $table->string('status', 20)->default('online')
                ->comment('online/on_trip/offline');
            $table->unsignedBigInteger('trip_id')->nullable()
                ->comment('الرحلة الحالية إن وجدت');
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();

            $table->index(['driver_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_locations');
    }
};
