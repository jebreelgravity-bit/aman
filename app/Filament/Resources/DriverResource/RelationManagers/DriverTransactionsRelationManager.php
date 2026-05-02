<?php

namespace App\Filament\Resources\DriverResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DriverTransactionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transactions';

    protected static ?string $title = '💰 المعاملات المالية';

    protected static ?string $modelLabel = 'معاملة';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('trip_id')
                    ->label('رقم الرحلة')
                    ->sortable(),

                Tables\Columns\TextColumn::make('trip_price')
                    ->label('سعر الرحلة')
                    ->money('YER'),

                Tables\Columns\TextColumn::make('app_commission')
                    ->label('عمولة التطبيق')
                    ->money('YER'),

                Tables\Columns\TextColumn::make('driver_earnings')
                    ->label('أرباح السائق')
                    ->money('YER')
                    ->color('success'),

                Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'cash' => '💵 نقدي', 'e_wallet' => '📱 محفظة',
                        'bank_transfer' => '🏦 تحويل', 'mobile_money' => '📲 موبايل',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('حالة الدفع')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'completed' => 'مكتمل', 'pending' => 'معلق',
                        'failed' => 'فاشل', 'under_review' => 'قيد المراجعة',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'completed', 'warning' => 'pending',
                        'danger' => 'failed', 'info' => 'under_review',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
