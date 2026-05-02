<?php

namespace App\Filament\Exports;

use App\Models\Transaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('رقم المعاملة'),
            ExportColumn::make('trip_id')->label('رقم الرحلة'),
            ExportColumn::make('driver.name')->label('السائق'),
            ExportColumn::make('customer.name')->label('العميل'),
            ExportColumn::make('trip_price')->label('سعر الرحلة'),
            ExportColumn::make('app_commission')->label('عمولة التطبيق'),
            ExportColumn::make('driver_earnings')->label('أرباح السائق'),
            ExportColumn::make('payment_method')
                ->label('طريقة الدفع')
                ->formatStateUsing(fn($state) => match ($state) {
                    'cash' => 'نقدي', 'e_wallet' => 'محفظة إلكترونية',
                    'bank_transfer' => 'تحويل بنكي', 'mobile_money' => 'موبايل موني',
                    default => $state,
                }),
            ExportColumn::make('payment_status')
                ->label('حالة الدفع')
                ->formatStateUsing(fn($state) => match ($state) {
                    'pending' => 'معلقة', 'completed' => 'مكتمل',
                    'failed' => 'فاشل', 'under_review' => 'قيد المراجعة',
                    default => $state,
                }),
            ExportColumn::make('payment_provider')->label('مزود الخدمة'),
            ExportColumn::make('payment_reference')->label('رقم المرجع'),
            ExportColumn::make('paid_at')->label('تاريخ الدفع'),
            ExportColumn::make('created_at')->label('تاريخ الإنشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير ' . number_format($export->successful_rows) . ' معاملة بنجاح.';
        if ($failedRows = $export->getFailedRowsCount()) {
            $body .= ' فشل تصدير ' . number_format($failedRows) . ' صف.';
        }
        return $body;
    }
}
