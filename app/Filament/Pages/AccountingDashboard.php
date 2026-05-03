<?php

namespace App\Filament\Pages;

use App\Models\BankAccount;
use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Expense;
use App\Models\Transaction;
use App\Models\User;
use App\Exports\ExpensesExport;
use App\Exports\FinancialReportExport;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class AccountingDashboard extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'لوحة المحاسبة';
    protected static ?string $title = 'لوحة المحاسبة المتكاملة';
    protected static \UnitEnum|string|null $navigationGroup = 'المحاسبة';
    protected static ?int $navigationSort = 1;

    public function getView(): string
    {
        return 'filament.pages.accounting-dashboard';
    }

    public int $selectedYear;
    public int $selectedMonth;

    public function mount(): void
    {
        $this->selectedYear  = now()->year;
        $this->selectedMonth = now()->month;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && in_array($user->role, ['admin', 'super_admin', 'accountant']);
    }

    /* ─── بيانات الملخص المالي ─── */
    public function getFinancialSummary(): array
    {
        $year  = $this->selectedYear;
        $month = $this->selectedMonth;

        // إيرادات الرحلات (عمولة التطبيق)
        $tripRevenue = (float) Transaction::where('payment_status', 'completed')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('app_commission');

        // إجمالي المصروفات المعتمدة
        $totalExpenses = (float) Expense::whereNotIn('status', ['rejected'])
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        // المصروفات المعلقة (بانتظار موافقة)
        $pendingExpenses = (float) Expense::where('status', 'pending')
            ->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month)
            ->sum('amount');

        // عدد المصروفات المعلقة
        $pendingCount = Expense::where('status', 'pending')->count();

        // إجمالي أرصدة البنوك
        $bankBalance = (float) BankAccount::where('is_active', true)->sum('current_balance');

        // الربح الصافي
        $netProfit = $tripRevenue - $totalExpenses;

        return [
            'trip_revenue'     => $tripRevenue,
            'total_expenses'   => $totalExpenses,
            'pending_expenses' => $pendingExpenses,
            'pending_count'    => $pendingCount,
            'net_profit'       => $netProfit,
            'bank_balance'     => $bankBalance,
        ];
    }

    /* ─── مقارنة الميزانية بالفعلي ─── */
    public function getBudgetComparison(): array
    {
        $year  = $this->selectedYear;
        $month = $this->selectedMonth;

        $categories = BudgetCategory::where('type', 'expense')->where('is_active', true)->get();
        $result = [];

        foreach ($categories as $cat) {
            $budgeted = (float) Budget::where('category_id', $cat->id)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->sum('amount');

            $spent = (float) Expense::where('category_id', $cat->id)
                ->whereNotIn('status', ['rejected'])
                ->whereYear('expense_date', $year)
                ->whereMonth('expense_date', $month)
                ->sum('amount');

            if ($budgeted > 0 || $spent > 0) {
                $percentage = $budgeted > 0 ? min(round(($spent / $budgeted) * 100), 999) : 100;
                $result[] = [
                    'name'       => $cat->name,
                    'color'      => $cat->color,
                    'icon'       => $cat->icon,
                    'budgeted'   => $budgeted,
                    'spent'      => $spent,
                    'remaining'  => max($budgeted - $spent, 0),
                    'percentage' => $percentage,
                    'over'       => $spent > $budgeted && $budgeted > 0,
                ];
            }
        }

        return $result;
    }

    /* ─── آخر المصروفات ─── */
    public function getRecentExpenses(): \Illuminate\Support\Collection
    {
        return Expense::with(['category', 'paidBy'])
            ->latest()
            ->limit(8)
            ->get();
    }

    /* ─── المصروفات المعلقة ─── */
    public function getPendingExpenses(): \Illuminate\Support\Collection
    {
        return Expense::with(['category', 'paidBy'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();
    }

    /* ─── الحسابات البنكية ─── */
    public function getBankAccounts(): \Illuminate\Support\Collection
    {
        return BankAccount::where('is_active', true)->get();
    }

    /* ─── المصروفات الشهرية (12 شهر) ─── */
    public function getMonthlyExpenses(): array
    {
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = (float) Expense::whereNotIn('status', ['rejected'])
                ->whereYear('expense_date', $this->selectedYear)
                ->whereMonth('expense_date', $m)
                ->sum('amount');
        }
        return $months;
    }

    /* ─── المصروفات حسب الفئة (للرسم الدائري) ─── */
    public function getExpensesByCategory(): array
    {
        return Expense::whereNotIn('status', ['rejected'])
            ->whereYear('expense_date', $this->selectedYear)
            ->whereMonth('expense_date', $this->selectedMonth)
            ->join('budget_categories', 'expenses.category_id', '=', 'budget_categories.id')
            ->select('budget_categories.name', 'budget_categories.color', DB::raw('SUM(expenses.amount) as total'))
            ->groupBy('budget_categories.id', 'budget_categories.name', 'budget_categories.color')
            ->orderByDesc('total')
            ->get()
            ->toArray();
    }

    /* ─── Actions: الموافقة والرفض السريع ─── */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export_expenses')
                ->label('تصدير المصروفات Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(fn() => Excel::download(
                    new ExpensesExport($this->selectedYear, $this->selectedMonth),
                    "expenses_{$this->selectedYear}_{$this->selectedMonth}.xlsx"
                )),

            Action::make('export_report')
                ->label('تصدير التقرير المالي')
                ->icon('heroicon-o-document-chart-bar')
                ->color('info')
                ->action(fn() => Excel::download(
                    new FinancialReportExport($this->selectedYear, $this->selectedMonth),
                    "financial_report_{$this->selectedYear}_{$this->selectedMonth}.xlsx"
                )),
        ];
    }

    /* ─── الأشهر ─── */
    public function getMonthName(int $month): string
    {
        return [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس',
            4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
            7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر',
            10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
        ][$month] ?? '';
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Expense::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }
}
