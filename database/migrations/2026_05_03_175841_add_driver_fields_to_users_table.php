<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // بيانات المركبة
            $table->string('vehicle_plate')->nullable()->after('gender')->comment('رقم لوحة المركبة');
            $table->enum('vehicle_type', ['taxi', 'minibus', 'bus', 'suv', 'van'])->nullable()->after('vehicle_plate')->comment('نوع المركبة');
            $table->enum('vehicle_grade', [
                'economy',   // اقتصادي
                'standard',  // قياسي
                'comfort',   // مريح
                'premium',   // مميز
                'business',  // رجال أعمال
                'vip',       // VIP
            ])->nullable()->after('vehicle_type')->comment('درجة المركبة');
            $table->string('vehicle_model')->nullable()->after('vehicle_grade')->comment('موديل السيارة مثل: تويوتا كامري 2023');
            $table->string('vehicle_color')->nullable()->after('vehicle_model')->comment('لون المركبة');
            $table->year('vehicle_year')->nullable()->after('vehicle_color')->comment('سنة الصنع');
            $table->string('vehicle_photo')->nullable()->after('vehicle_year')->comment('صورة المركبة');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_plate', 'vehicle_type', 'vehicle_grade',
                'vehicle_model', 'vehicle_color', 'vehicle_year', 'vehicle_photo',
            ]);
        });
    }
};
