<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // هوية وطنية
            $table->string('national_id')->nullable()->after('phone')->comment('رقم الهوية الوطنية');
            $table->string('national_id_front')->nullable()->after('national_id')->comment('صورة الهوية - الوجه الأمامي');
            $table->string('national_id_back')->nullable()->after('national_id_front')->comment('صورة الهوية - الوجه الخلفي');

            // بيانات شخصية
            $table->date('date_of_birth')->nullable()->after('national_id_back')->comment('تاريخ الميلاد');
            $table->string('address')->nullable()->after('date_of_birth')->comment('العنوان');
            $table->string('city')->nullable()->after('address')->comment('المدينة');
            $table->enum('gender', ['male', 'female'])->nullable()->after('city')->comment('الجنس');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'national_id', 'national_id_front', 'national_id_back',
                'date_of_birth', 'address', 'city', 'gender',
            ]);
        });
    }
};
