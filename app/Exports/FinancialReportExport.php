<?php

namespace App\Exports;

use App\Models\BudgetCategory;
use App\Models\Expense;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialReportExport implements FromArray, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        protected int $year,
        protected ?int $month = null
    ) {}

    public function array(): array
    {
        $rows = [];

        // ── إيرادات الرحلات ──
        $tripRevenue = Transaction::where('payment_status', 'completed')
            ->whereYear('created_at', $this->year)
            ->when($this->month, fn($q) => $q->whereMonth('created_at', $this->month))
            ->sum('app_commission');

        $rows[] = ['إيرادات الرحلات (عمولة 20%)', number_format($tripRevenue, 2), 'إيراد', '—'];

        // ── فئات المصروفات ──
        $categories = BudgetCategory::where('type', 'expense')->get();
        foreach ($categories as $cat) {
            $spent = Expense::where('category_id', $cat->id)
                ->whereNotIn('status', ['rejected'])
                ->whereYear('expense_date', $this->year)
                ->when($this->month, fn($q) => $q->whereMonth('expense_date', $this->month))
                ->sum('amount');

            if ($spent > 0) {
                $rows[] = [$cat->name, number_format($spent, 2), 'مصروف', '—'];
            }
        }

        // ── ملخص ──
        $totalExpenses = collect($rows)
            ->where(3, '—')
            ->where(2, 'مصروف')
            ->sum(fn($r) => (float) str_replace(',', '', $r[1]));

        $rows[] = ['', '', '', ''];
        $rows[] = ['إجمالي الإيرادات', number_format($tripRevenue, 2), '', ''];
        $rows[] = ['إجمالي المصروفات', number_format($totalExpenses, 2), '', ''];
        $rows[] = ['صافي الربح / الخسارة', number_format($tripRevenue - $totalExpenses, 2), '', ''];

        return $rows;
    }

    public function headings(): array
    {
        return ['البند', 'المبلغ (ر.س)', 'النوع', 'ملاحظات'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1a1a2e']]],
        ];
    }

    public function title(): string
    {
        return "التقرير المالي {$this->year}";
    }
}
