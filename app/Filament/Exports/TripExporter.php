<?php

namespace App\Filament\Exports;

use App\Models\Trip;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class TripExporter extends Exporter
{
    protected static ?string $model = Trip::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('رقم الرحلة'),
            ExportColumn::make('customer.name')->label('العميل'),
            ExportColumn::make('driver.name')->label('السائق'),
            ExportColumn::make('category')
                ->label('الفئة')
                ->formatStateUsing(fn($state) => match ($state) {
                    'economy' => 'توفير', 'vip' => 'VIP', 'bus' => 'باص', default => $state,
                }),
            ExportColumn::make('status')
                ->label('الحالة')
                ->formatStateUsing(fn($state) => match ($state) {
                    'pending' => 'انتظار', 'accepted' => 'مقبولة', 'started' => 'جارية',
                    'completed' => 'مكتملة', 'cancelled' => 'ملغاة', default => $state,
                }),
            ExportColumn::make('pickup_address')->label('نقطة الانطلاق'),
            ExportColumn::make('dropoff_address')->label('نقطة الوصول'),
            ExportColumn::make('distance_km')->label('المسافة (كم)'),
            ExportColumn::make('estimated_price')->label('السعر المتوقع'),
            ExportColumn::make('final_price')->label('السعر النهائي'),
            ExportColumn::make('created_at')->label('تاريخ الإنشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير ' . number_format($export->successful_rows) . ' رحلة بنجاح.';
        if ($failedRows = $export->getFailedRowsCount()) {
            $body .= ' فشل تصدير ' . number_format($failedRows) . ' صف.';
        }
        return $body;
    }
}
