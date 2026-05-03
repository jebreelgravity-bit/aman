<?php

namespace App\Filament\Resources\ActivityLogResource\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ActivityLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('تفاصيل النشاط')
                    ->description('المعلومات الأساسية حول هذا السجل')
                    ->schema([
                        TextInput::make('user_id')
                            ->label('المستخدم (ID)')
                            ->numeric()
                            ->disabled()
                            ->default(null),

                        TextInput::make('action')
                            ->label('الإجراء')
                            ->required()
                            ->disabled(),

                        TextInput::make('ip_address')
                            ->label('عنوان IP')
                            ->disabled()
                            ->default(null),

                        TextInput::make('model_type')
                            ->label('نوع المورد (Model)')
                            ->disabled()
                            ->default(null),

                        TextInput::make('model_id')
                            ->label('معرف المورد (Model ID)')
                            ->numeric()
                            ->disabled()
                            ->default(null),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('البيانات المتأثرة')
                    ->description('توضيح للتغييرات التي طرأت على البيانات')
                    ->schema([
                        Textarea::make('old_values')
                            ->label('القيم القديمة')
                            ->disabled()
                            ->default(null)
                            ->columnSpanFull()
                            ->rows(4),

                        Textarea::make('new_values')
                            ->label('القيم الجديدة')
                            ->disabled()
                            ->default(null)
                            ->columnSpanFull()
                            ->rows(4),
                    ])->columns(1),

                \Filament\Schemas\Components\Section::make('معلومات إضافية')
                    ->schema([
                        Textarea::make('description')
                            ->label('الوصف')
                            ->disabled()
                            ->default(null)
                            ->columnSpanFull()
                            ->rows(3),

                        Textarea::make('user_agent')
                            ->label('المتصفح (User Agent)')
                            ->disabled()
                            ->default(null)
                            ->columnSpanFull()
                            ->rows(2),
                    ])->columns(1),
            ]);
    }
}
