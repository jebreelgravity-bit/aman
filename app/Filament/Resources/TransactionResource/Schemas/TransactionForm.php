<?php

namespace App\Filament\Resources\TransactionResource\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                \Filament\Schemas\Components\Section::make('معلومات الرحلة')
                    ->description('ارتباط هذه المعاملة بالرحلة والأطراف')
                    ->schema([
                        Select::make('trip_id')
                            ->label('رقم الرحلة')
                            ->relationship('trip', 'id')
                            ->searchable()
                            ->required(),

                        Select::make('customer_id')
                            ->label('اسم العميل')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('driver_id')
                            ->label('الكابتن (السائق)')
                            ->relationship('driver', 'name')
                            ->searchable()
                            ->required(),
                    ])->columns(3),

                \Filament\Schemas\Components\Section::make('التفاصيل المالية')
                    ->schema([
                        TextInput::make('trip_price')
                            ->label('المبلغ الإجمالي')
                            ->required()
                            ->numeric()
                            ->prefix('ر.ي')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $rate = $get('app_commission_rate') ?? 20;
                                $commission = ($state * $rate) / 100;
                                $set('app_commission', round($commission, 2));
                                $set('driver_earnings', round($state - $commission, 2));
                            }),

                        TextInput::make('app_commission_rate')
                            ->label('نسبة عمولة التطبيق (%)')
                            ->required()
                            ->numeric()
                            ->default(20.0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $price = $get('trip_price') ?? 0;
                                $commission = ($price * $state) / 100;
                                $set('app_commission', round($commission, 2));
                                $set('driver_earnings', round($price - $commission, 2));
                            }),

                        TextInput::make('app_commission')
                            ->label('عمولة التطبيق (20%)')
                            ->required()
                            ->numeric()
                            ->prefix('ر.ي')
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('driver_earnings')
                            ->label('صافي ربح السائق')
                            ->required()
                            ->numeric()
                            ->prefix('ر.ي')
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(2),

                \Filament\Schemas\Components\Section::make('الدفع')
                    ->schema([
                        Select::make('payment_method')
                            ->label('طريقة الدفع')
                            ->options([
                                'cash' => '💵 نقدي (كاش)',
                                'e_wallet' => '📱 محفظة إلكترونية',
                                'bank_transfer' => '🏦 تحويل بنكي',
                                'mobile_money' => '📲 موبايل موني',
                            ])
                            ->default('cash')
                            ->native(false)
                            ->required()
                            ->reactive(),

                        TextInput::make('payment_provider')
                            ->label('مزود الخدمة')
                            ->placeholder('فلوسك، جوالي، سبأفون كاش...')
                            ->visible(fn($get) => in_array($get('payment_method'), ['e_wallet', 'bank_transfer', 'mobile_money'])),

                        TextInput::make('payment_reference')
                            ->label('رقم مرجع التحويل / الإيصال')
                            ->visible(fn($get) => in_array($get('payment_method'), ['e_wallet', 'bank_transfer', 'mobile_money'])),

                        Select::make('payment_status')
                            ->label('حالة الدفع')
                            ->options([
                                'pending' => '⏳ معلقة',
                                'completed' => '✅ مكتملة',
                                'failed' => '❌ فشلت',
                                'under_review' => '🔍 قيد المراجعة',
                                'refunded' => '↩️ مُسترجعة',
                            ])
                            ->default('pending')
                            ->native(false)
                            ->required(),

                        DateTimePicker::make('paid_at')
                            ->label('تاريخ الدفع')
                            ->default(now()),
                    ])->columns(2),
            ]);
    }
}
