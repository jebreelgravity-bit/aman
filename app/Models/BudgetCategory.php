<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'description', 'icon', 'color', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function budgets()
    {
        return $this->hasMany(Budget::class, 'category_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'category_id');
    }

    /** مجموع المصروفات الفعلية لفترة معينة */
    public function totalExpenses(int $year, ?int $month = null): float
    {
        $query = $this->expenses()->where('status', '!=', 'rejected')
            ->whereYear('expense_date', $year);

        if ($month) {
            $query->whereMonth('expense_date', $month);
        }

        return (float) $query->sum('amount');
    }

    /** الميزانية المخصصة لفترة معينة */
    public function budgetFor(int $year, ?int $month = null): float
    {
        return (float) $this->budgets()
            ->where('period_year', $year)
            ->when($month, fn($q) => $q->where('period_month', $month))
            ->sum('amount');
    }
}
