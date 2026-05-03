<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->onDelete('cascade');
            $table->enum('type', ['credit', 'debit']);             // إيداع أو سحب
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);               // الرصيد بعد العملية
            $table->string('reference_number')->nullable();        // رقم العملية البنكية
            $table->string('description');                         // وصف العملية
            $table->date('transaction_date');                      // تاريخ العملية
            $table->foreignId('expense_id')->nullable()->constrained('expenses'); // ربط بالمصروف
            $table->string('related_type')->nullable();            // polymorphic للإيرادات
            $table->unsignedBigInteger('related_id')->nullable();
            $table->boolean('is_reconciled')->default(false);      // مطابق مع كشف الحساب
            $table->text('notes')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->index(['bank_account_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
