<?php

namespace App\Filament\Resources\DriverDocuments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class DriverDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('driver_id')
                    ->relationship('driver', 'name', fn ($query) => $query->where('role', 'driver'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label('السائق'),
                Select::make('document_type')
                    ->options([
                        'driving_license' => 'إجازة سياقة',
                        'id_card' => 'بطاقة وطنية',
                        'vehicle_registration' => 'سنوية السيارة',
                    ])
                    ->required()
                    ->label('نوع الوثيقة'),
                FileUpload::make('file_path')
                    ->directory('driver-documents')
                    ->image()
                    ->required()
                    ->label('ملف الوثيقة'),
                DatePicker::make('expiry_date')
                    ->label('تاريخ الانتهاء'),
                Select::make('status')
                    ->options([
                        'pending' => 'قيد المراجعة',
                        'approved' => 'مقبول',
                        'rejected' => 'مرفوض',
                    ])
                    ->default('pending')
                    ->required()
                    ->label('الحالة'),
                Textarea::make('rejection_reason')
                    ->default(null)
                    ->columnSpanFull()
                    ->label('سبب الرفض (إن وجد)'),
            ]);
    }
}
