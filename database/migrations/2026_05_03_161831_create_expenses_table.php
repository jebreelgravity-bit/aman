<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('budget_categories')->onDelete('restrict');
            $table->decimal('amount', 15, 2);
            $table->string('description');
            $table->string('vendor_name')->nullable();              // اسم المورد / الجهة
            $table->string('invoice_number')->nullable();           // رقم الفاتورة
            $table->date('invoice_date')->nullable();               // تاريخ الفاتورة
            $table->date('expense_date');                           // تاريخ الصرف الفعلي
            $table->foreignId('paid_by')->constrained('users');    // المحاسب الذي سجّل
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'check'])->default('cash');
            $table->string('receipt_path')->nullable();             // صورة الإيصال
            $table->enum('status', ['pending', 'approved', 'rejected', 'paid'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['category_id', 'expense_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
