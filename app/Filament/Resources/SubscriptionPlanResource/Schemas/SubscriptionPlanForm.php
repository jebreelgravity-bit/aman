<?php

namespace App\Filament\Resources\SubscriptionPlanResource\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SubscriptionPlanForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('معلومات الباقة الأساسية')
                    ->description('تفاصيل خطة الاشتراك')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم الباقة')
                            ->required()
                            ->placeholder('مثال: باقة الطلاب، باصة الموظفين'),

                        Select::make('type')
                            ->label('نوع الباقة')
                            ->options([
                                'student' => '🎓 طلاب',
                                'employee' => '💼 موظفين',
                                'corporate' => '🏢 شركات',
                            ])
                            ->required()
                            ->native(false),

                        Textarea::make('description')
                            ->label('الوصف العام')
                            ->default(null)
                            ->columnSpanFull(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('التسعير')
                    ->schema([
                        TextInput::make('monthly_price')
                            ->label('السعر الشهري')
                            ->required()
                            ->numeric()
                            ->prefix('ر.ي')
                            ->live(onBlur: true),

                        TextInput::make('discount_percentage')
                            ->label('نسبة الخصم على الرحلات (%)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0.0)
                            ->suffix('%'),

                        TextInput::make('free_trips_per_month')
                            ->label('عدد الرحلات المجانية شهرياً')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('المميزات والإعدادات')
                    ->schema([
                        Textarea::make('features')
                            ->label('المميزات الإضافية')
                            ->default(null)
                            ->columnSpanFull()
                            ->helperText('يمكنك سرد المميزات بفواصل أو أسطر'),

                        Toggle::make('priority_booking')
                            ->label('أولوية في الحجز (VIP)'),

                        Toggle::make('is_active')
                            ->label('الباقة نشطة؟')
                            ->default(true),
                    ])->columns(2),
            ]);
    }
}
