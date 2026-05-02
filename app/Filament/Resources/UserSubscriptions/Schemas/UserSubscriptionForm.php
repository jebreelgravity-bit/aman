<?php

namespace App\Filament\Resources\UserSubscriptions\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class UserSubscriptionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('معلومات المشترك')
                    ->description('المستخدم وباقة الاشتراك')
                    ->schema([
                        Select::make('user_id')
                            ->label('المشترك')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('subscription_plan_id')
                            ->label('باقة الاشتراك')
                            ->relationship('plan', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label('حالة الاشتراك')
                            ->options([
                                'active' => '✅ نشط',
                                'expired' => '⏰ منتهي',
                                'cancelled' => '❌ ملغي',
                                'pending' => '⏳ قيد الانتظار',
                            ])
                            ->default('pending')
                            ->native(false)
                            ->required(),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('تفاصيل الاشتراك')
                    ->schema([
                        DatePicker::make('starts_at')
                            ->label('تاريخ البدء')
                            ->required(),

                        DatePicker::make('expires_at')
                            ->label('تاريخ الانتهاء')
                            ->required(),

                        DatePicker::make('next_billing_date')
                            ->label('تاريخ التجديد القادم'),

                        TextInput::make('trips_used')
                            ->label('الرحلات المستخدمة')
                            ->required()
                            ->numeric()
                            ->default(0),

                        TextInput::make('total_savings')
                            ->label('إجمالي التوفير (ر.ي)')
                            ->required()
                            ->numeric()
                            ->prefix('ر.ي')
                            ->default(0.0),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('التحقق من الوثائق')
                    ->description('مراجعة وثائق المشترك (مثل البطاقة الجامعية للطلاب)')
                    ->schema([
                        FileUpload::make('verification_document')
                            ->label('وثيقة التحقق')
                            ->directory('verification-documents')
                            ->acceptedFileTypes(['image/*', 'application/pdf'])
                            ->downloadable()
                            ->openable(),

                        Select::make('verification_status')
                            ->label('حالة التحقق')
                            ->options([
                                'pending' => '⏳ قيد المراجعة',
                                'approved' => '✅ مقبول',
                                'rejected' => '❌ مرفوض',
                            ])
                            ->default('pending')
                            ->native(false)
                            ->required()
                            ->live()
                            ->reactive(),

                        Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->placeholder('مثال: البطاقة الجامعية منتهية الصلاحية')
                            ->visible(fn($get) => $get('verification_status') === 'rejected')
                            ->required(fn($get) => $get('verification_status') === 'rejected')
                            ->columnSpanFull(),
                    ])->columns(2),
            ]);
    }
}
