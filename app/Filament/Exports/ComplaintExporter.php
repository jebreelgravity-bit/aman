<?php

namespace App\Filament\Exports;

use App\Models\Complaint;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class ComplaintExporter extends Exporter
{
    protected static ?string $model = Complaint::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('ticket_number')->label('رقم التذكرة'),
            ExportColumn::make('user.name')->label('المستخدم'),
            ExportColumn::make('driver.name')->label('السائق'),
            ExportColumn::make('trip_id')->label('رقم الرحلة'),
            ExportColumn::make('category')
                ->label('التصنيف')
                ->formatStateUsing(fn($state) => match ($state) {
                    'delay' => 'تأخير', 'behavior' => 'سلوك', 'pricing' => 'تسعير',
                    'safety' => 'أمان', 'cleanliness' => 'نظافة', 'other' => 'أخرى',
                    default => $state,
                }),
            ExportColumn::make('priority')
                ->label('الأولوية')
                ->formatStateUsing(fn($state) => match ($state) {
                    'low' => 'منخفضة', 'medium' => 'متوسطة',
                    'high' => 'عالية', 'urgent' => 'عاجلة',
                    default => $state,
                }),
            ExportColumn::make('subject')->label('الموضوع'),
            ExportColumn::make('description')->label('الوصف'),
            ExportColumn::make('status')
                ->label('الحالة')
                ->formatStateUsing(fn($state) => match ($state) {
                    'open' => 'مفتوحة', 'in_progress' => 'قيد المعالجة',
                    'resolved' => 'محلولة', 'closed' => 'مغلقة',
                    default => $state,
                }),
            ExportColumn::make('assignedTo.name')->label('مسند إلى'),
            ExportColumn::make('resolution')->label('الحل'),
            ExportColumn::make('created_at')->label('تاريخ الإنشاء'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'تم تصدير ' . number_format($export->successful_rows) . ' شكوى بنجاح.';
        if ($failedRows = $export->getFailedRowsCount()) {
            $body .= ' فشل تصدير ' . number_format($failedRows) . ' صف.';
        }
        return $body;
    }
}
