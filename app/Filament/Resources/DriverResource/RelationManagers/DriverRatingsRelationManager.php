<?php

namespace App\Filament\Resources\DriverResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DriverRatingsRelationManager extends RelationManager
{
    protected static string $relationship = 'driverRatings';

    protected static ?string $title = '⭐ التقييمات';

    protected static ?string $modelLabel = 'تقييم';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('driver_rating')
                    ->label('تقييم السائق')
                    ->formatStateUsing(fn($state) => str_repeat('⭐', $state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('service_rating')
                    ->label('الخدمة')
                    ->formatStateUsing(fn($state) => $state ? str_repeat('⭐', $state) : '—'),

                Tables\Columns\TextColumn::make('cleanliness_rating')
                    ->label('النظافة')
                    ->formatStateUsing(fn($state) => $state ? str_repeat('⭐', $state) : '—'),

                Tables\Columns\TextColumn::make('comment')
                    ->label('التعليق')
                    ->limit(40)
                    ->default('—'),

                Tables\Columns\TextColumn::make('tags')
                    ->label('الوسوم')
                    ->formatStateUsing(function ($state) {
                        if (is_array($state)) return implode(', ', $state);
                        if (is_string($state)) {
                            $decoded = json_decode($state, true);
                            return is_array($decoded) ? implode(', ', $decoded) : $state;
                        }
                        return '—';
                    })
                    ->limit(30),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('driver_rating')
                    ->label('التقييم')
                    ->options([5 => '⭐⭐⭐⭐⭐', 4 => '⭐⭐⭐⭐', 3 => '⭐⭐⭐', 2 => '⭐⭐', 1 => '⭐'])
                    ->native(false),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
