<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Expense extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'category_id', 'amount', 'description', 'vendor_name',
        'invoice_number', 'invoice_date', 'expense_date',
        'paid_by', 'payment_method', 'receipt_path',
        'status', 'approved_by', 'approved_at', 'rejection_reason', 'notes',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'invoice_date' => 'date',
        'expense_date' => 'date',
        'approved_at'  => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(BudgetCategory::class, 'category_id');
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function bankTransactions()
    {
        return $this->hasMany(BankTransaction::class, 'expense_id');
    }

    /** الموافقة على المصروف */
    public function approve(int $adminId): void
    {
        $this->update([
            'status'      => 'approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
        ]);
    }

    /** رفض المصروف */
    public function reject(int $adminId, string $reason): void
    {
        $this->update([
            'status'           => 'rejected',
            'approved_by'      => $adminId,
            'approved_at'      => now(),
            'rejection_reason' => $reason,
        ]);
    }

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isApproved(): bool  { return $this->status === 'approved'; }
    public function isRejected(): bool  { return $this->status === 'rejected'; }
    public function isPaid(): bool      { return $this->status === 'paid'; }

    public function scopePending($q)  { return $q->where('status', 'pending'); }
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopeByCategory($q, int $catId) { return $q->where('category_id', $catId); }
}
