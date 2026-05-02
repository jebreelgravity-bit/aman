<?php

namespace App\Filament\Resources\DriverResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DriverRewardsRelationManager extends RelationManager
{
    protected static string $relationship = 'rewards';

    protected static ?string $title = '🎁 المكافآت';

    protected static ?string $modelLabel = 'مكافأة';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reward_type')
                    ->label('النوع')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'trip_bonus' => '🚗 مكافأة رحلة',
                        'rating_bonus' => '⭐ مكافأة تقييم',
                        'referral_bonus' => '👥 مكافأة إحالة',
                        'weekly_target' => '🎯 هدف أسبوعي',
                        'peak_hour' => '⏰ ساعة الذروة',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('amount')
                    ->label('المبلغ')
                    ->money('YER')
                    ->color('success'),

                Tables\Columns\IconColumn::make('is_distributed')
                    ->label('تم التوزيع')
                    ->boolean(),

                Tables\Columns\TextColumn::make('distributed_at')
                    ->label('تاريخ التوزيع')
                    ->dateTime('Y-m-d')
                    ->default('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
