<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExpensesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithTitle
{
    public function __construct(
        protected ?int $year = null,
        protected ?int $month = null,
        protected ?string $status = null
    ) {
        $this->year  = $year  ?? now()->year;
        $this->month = $month ?? null;
    }

    public function collection()
    {
        return Expense::with(['category', 'paidBy', 'approvedBy'])
            ->when($this->year,   fn($q) => $q->whereYear('expense_date', $this->year))
            ->when($this->month,  fn($q) => $q->whereMonth('expense_date', $this->month))
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->orderBy('expense_date', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            '#', 'الفئة', 'الوصف', 'المورّد', 'رقم الفاتورة',
            'تاريخ الصرف', 'المبلغ', 'طريقة الدفع', 'الحالة',
            'سجّله', 'وافق عليه', 'تاريخ الموافقة',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->id,
            $expense->category?->name ?? '—',
            $expense->description,
            $expense->vendor_name ?? '—',
            $expense->invoice_number ?? '—',
            $expense->expense_date?->format('Y-m-d'),
            number_format($expense->amount, 2),
            match($expense->payment_method) {
                'cash'          => 'نقداً',
                'bank_transfer' => 'تحويل بنكي',
                'card'          => 'بطاقة',
                'check'         => 'شيك',
                default         => $expense->payment_method,
            },
            match($expense->status) {
                'pending'  => 'معلّق',
                'approved' => 'موافق',
                'rejected' => 'مرفوض',
                'paid'     => 'مدفوع',
                default    => $expense->status,
            },
            $expense->paidBy?->name ?? '—',
            $expense->approvedBy?->name ?? '—',
            $expense->approved_at?->format('Y-m-d') ?? '—',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FFD700']]],
        ];
    }

    public function title(): string
    {
        return 'المصروفات';
    }
}
