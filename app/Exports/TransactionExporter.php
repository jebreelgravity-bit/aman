<?php

namespace App\Exports;

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
            ExportColumn::make('id')
                ->label('رقم المعاملة'),
            ExportColumn::make('trip_id')
                ->label('رقم الرحلة'),
            ExportColumn::make('customer.name')
                ->label('اسم العميل'),
            ExportColumn::make('driver.name')
                ->label('اسم السائق'),
            ExportColumn::make('trip.category')
                ->label('فئة الرحلة')
                ->formatStateUsing(fn($state) => match ($state) {
                    'economy' => 'توفير',
                    'vip' => 'VIP',
                    'bus' => 'باص',
                    default => $state,
                }),
            ExportColumn::make('trip_price')
                ->label('المبلغ الإجمالي (ر.ي)'),
            ExportColumn::make('app_commission_rate')
                ->label('نسبة العمولة (%)'),
            ExportColumn::make('app_commission')
                ->label('عمولة التطبيق (ر.ي)'),
            ExportColumn::make('driver_earnings')
                ->label('صافي ربح السائق (ر.ي)'),
            ExportColumn::make('payment_method')
                ->label('طريقة الدفع')
                ->formatStateUsing(fn($state) => match ($state) {
                    'cash' => 'نقدي',
                    'e_wallet' => 'محفظة إلكترونية',
                    'bank_transfer' => 'تحويل بنكي',
                    'mobile_money' => 'موبايل موني',
                    default => $state,
                }),
            ExportColumn::make('payment_status')
                ->label('حالة الدفع')
                ->formatStateUsing(fn($state) => match ($state) {
                    'pending' => 'معلقة',
                    'completed' => 'مكتملة',
                    'failed' => 'فشلت',
                    'under_review' => 'قيد المراجعة',
                    'refunded' => 'مسترجعة',
                    default => $state,
                }),
            ExportColumn::make('payment_provider')
                ->label('مزود الخدمة'),
            ExportColumn::make('payment_reference')
                ->label('رقم المرجع'),
            ExportColumn::make('paid_at')
                ->label('تاريخ الدفع'),
            ExportColumn::make('created_at')
                ->label('تاريخ الإنشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير الكشوفات المالية بنجاح';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= " مع {$failedRowsCount} صف فشل.";
        }

        return $body;
    }
}
