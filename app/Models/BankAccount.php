<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name', 'account_name', 'account_number', 'iban',
        'swift_code', 'currency', 'current_balance',
        'last_synced_at', 'is_primary', 'is_active', 'notes',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'last_synced_at'  => 'date',
        'is_primary'      => 'boolean',
        'is_active'       => 'boolean',
    ];

    public function transactions()
    {
        return $this->hasMany(BankTransaction::class, 'bank_account_id');
    }

    /** إجمالي الإيداعات */
    public function totalCredits(?string $from = null, ?string $to = null): float
    {
        return (float) $this->transactions()
            ->where('type', 'credit')
            ->when($from, fn($q) => $q->whereDate('transaction_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('transaction_date', '<=', $to))
            ->sum('amount');
    }

    /** إجمالي السحوبات */
    public function totalDebits(?string $from = null, ?string $to = null): float
    {
        return (float) $this->transactions()
            ->where('type', 'debit')
            ->when($from, fn($q) => $q->whereDate('transaction_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('transaction_date', '<=', $to))
            ->sum('amount');
    }
}
