<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'amount', 'period_type', 'period_year', 'period_month', 'notes', 'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(BudgetCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** المصروف الفعلي مقابل هذه الميزانية */
    public function getActualSpentAttribute(): float
    {
        return $this->category->totalExpenses($this->period_year, $this->period_month);
    }

    /** نسبة الاستهلاك */
    public function getUsagePercentageAttribute(): float
    {
        if ($this->amount == 0) return 0;
        return min(round(($this->actual_spent / $this->amount) * 100, 1), 999);
    }

    /** هل تجاوز الميزانية؟ */
    public function isOverBudget(): bool
    {
        return $this->actual_spent > $this->amount;
    }
}
