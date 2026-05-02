<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('payment_provider')->nullable()->comment('مزود خدمة الدفع: فلوسك، جوالي، سبأفون كاش');
            $table->string('payment_reference')->nullable()->comment('رقم مرجع التحويل/الإيصال');
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete()->comment('المدير الذي تحقق من الدفع');
            $table->timestamp('verified_at')->nullable()->comment('تاريخ التحقق من الدفع');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['payment_provider', 'payment_reference', 'verified_by', 'verified_at']);
        });
    }
};
