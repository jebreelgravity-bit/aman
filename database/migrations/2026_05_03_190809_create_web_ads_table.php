<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_ads', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('offer_code', 50)->nullable()->comment('كود الخصم');
            $table->unsignedTinyInteger('discount_percentage')->nullable();
            $table->string('bg_color', 20)->default('#1a1a2e');
            $table->string('btn_text', 100)->default('استخدم العرض');
            $table->string('btn_link')->nullable();
            $table->enum('target', ['all', 'inactive', 'loyal', 'new'])->default('all')
                ->comment('الفئة المستهدفة: الكل/غير النشطين/الدائمين/الجدد');
            $table->boolean('show_once')->default(true)->comment('يظهر مرة واحدة');
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_ads');
    }
};
