<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('primary_color')->nullable()->default('#FFD700')->change();
        });

        // Fix any existing NULL values
        DB::table('system_settings')
            ->whereNull('primary_color')
            ->update(['primary_color' => '#FFD700']);
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('primary_color')->nullable(false)->default('#0ea5e9')->change();
        });
    }
};
