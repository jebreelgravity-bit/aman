<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');                    // اسم البنك
            $table->string('account_name');                 // اسم صاحب الحساب
            $table->string('account_number');               // رقم الحساب
            $table->string('iban')->nullable();             // IBAN
            $table->string('swift_code')->nullable();       // SWIFT
            $table->string('currency')->default('SAR');     // العملة
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->date('last_synced_at')->nullable();     // آخر مزامنة
            $table->boolean('is_primary')->default(false);  // الحساب الرئيسي
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
