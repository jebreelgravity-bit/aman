<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('long_distance_trips', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('اسم الرحلة مثل: صنعاء → عدن');
            $table->string('origin_city');
            $table->string('destination_city');
            $table->decimal('origin_lat', 10, 7)->nullable();
            $table->decimal('origin_lng', 10, 7)->nullable();
            $table->decimal('dest_lat', 10, 7)->nullable();
            $table->decimal('dest_lng', 10, 7)->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();

            // التسعير
            $table->decimal('base_price', 10, 2)->comment('السعر الأساسي بالريال');
            $table->string('vehicle_type')->nullable()->comment('نوع المركبة: Rav4, باص, إلخ');
            $table->unsignedTinyInteger('max_passengers')->default(4);

            // التوقيت
            $table->time('departure_time')->nullable()->comment('موعد الانطلاق');
            $table->enum('frequency', ['daily', 'weekly', 'custom'])->default('daily');
            $table->json('departure_days')->nullable()->comment('أيام الأسبوع: [0,1,2...]');

            // الحالة
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // جدول علاقة السائقين المخصصين للرحلات الطويلة
        Schema::create('long_distance_trip_driver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('long_distance_trip_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_primary')->default(false)->comment('هل هو السائق الرئيسي');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('long_distance_trip_driver');
        Schema::dropIfExists('long_distance_trips');
    }
};
