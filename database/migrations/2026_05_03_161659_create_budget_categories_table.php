<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // الإعلانات، اللواصق، المكتب...
            $table->enum('type', ['expense', 'income'])->default('expense');
            $table->string('description')->nullable();
            $table->string('icon')->default('heroicon-o-folder'); // heroicon name
            $table->string('color')->default('#6366f1');          // hex color
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_categories');
    }
};
