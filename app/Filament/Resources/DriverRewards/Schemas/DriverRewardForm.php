<?php

namespace App\Filament\Resources\DriverRewards\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DriverRewardForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('معلومات المكافأة')
                    ->description('تفاصيل مكافأة السائق للأسبوع المحدد')
                    ->schema([
                        \Filament\Forms\Components\Select::make('driver_id')
                            ->label('كابتن (السائق)')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->required(),

                        TextInput::make('week_number')
                            ->label('رقم الأسبوع')
                            ->required()
                            ->numeric(),

                        TextInput::make('year')
                            ->label('السنة (Year)')
                            ->required()
                            ->numeric(),

                        DatePicker::make('week_start_date')
                            ->label('بداية الأسبوع')
                            ->required(),

                        DatePicker::make('week_end_date')
                            ->label('نهاية الأسبوع')
                            ->required(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('الأداء والمستحقات')
                    ->schema([
                        TextInput::make('total_trips')
                            ->label('إجمالي الرحلات')
                            ->required()
                            ->numeric()
                            ->default(0),

                        TextInput::make('total_earnings')
                            ->label('إجمالي الأرباح (ر.ي)')
                            ->required()
                            ->numeric()
                            ->default(0.0),

                        TextInput::make('calculated_reward')
                            ->label('المكافأة المحتسبة (ر.ي)')
                            ->required()
                            ->numeric()
                            ->default(0.0),

                        TextInput::make('actual_reward')
                            ->label('المكافأة الفعلية (ر.ي)')
                            ->required()
                            ->numeric()
                            ->default(0.0),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('حالة التوزيع')
                    ->schema([
                        Toggle::make('is_distributed')
                            ->label('تم التوزيع؟')
                            ->required(),

                        DateTimePicker::make('distributed_at')
                            ->label('تاريخ التوزيع'),

                        Textarea::make('notes')
                            ->label('ملاحظات إضافية')
                            ->default(null)
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
