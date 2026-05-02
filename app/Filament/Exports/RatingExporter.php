<?php

namespace App\Filament\Exports;

use App\Models\Rating;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class RatingExporter extends Exporter
{
    protected static ?string $model = Rating::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')->label('رقم التقييم'),
            ExportColumn::make('trip_id')->label('رقم الرحلة'),
            ExportColumn::make('customer.name')->label('العميل'),
            ExportColumn::make('driver.name')->label('السائق'),
            ExportColumn::make('driver_rating')->label('تقييم السائق'),
            ExportColumn::make('service_rating')->label('تقييم الخدمة'),
            ExportColumn::make('cleanliness_rating')->label('تقييم النظافة'),
            ExportColumn::make('comment')->label('التعليق'),
            ExportColumn::make('tags')
                ->label('الوسوم')
                ->formatStateUsing(function ($state) {
                    if (is_array($state)) return implode(', ', $state);
                    if (is_string($state)) {
                        $decoded = json_decode($state, true);
                        return is_array($decoded) ? implode(', ', $decoded) : $state;
                    }
                    return '';
                }),
            ExportColumn::make('created_at')->label('التاريخ'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير ' . number_format($export->successful_rows) . ' تقييم بنجاح.';
        if ($failedRows = $export->getFailedRowsCount()) {
            $body .= ' فشل تصدير ' . number_format($failedRows) . ' صف.';
        }
        return $body;
    }
}
