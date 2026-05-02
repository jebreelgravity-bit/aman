<?php

namespace App\Filament\Resources\DriverResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class DriverTripsRelationManager extends RelationManager
{
    protected static string $relationship = 'driverTrips';

    protected static ?string $title = '🚗 رحلات السائق';

    protected static ?string $modelLabel = 'رحلة';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('الفئة')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'economy' => 'توفير', 'vip' => 'VIP', 'bus' => 'باص', default => $state,
                    })
                    ->colors([
                        'success' => 'economy', 'warning' => 'vip', 'primary' => 'bus',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'انتظار', 'accepted' => 'مقبولة', 'started' => 'جارية',
                        'completed' => 'مكتملة', 'cancelled' => 'ملغاة', default => $state,
                    })
                    ->colors([
                        'warning' => 'pending', 'info' => 'accepted', 'primary' => 'started',
                        'success' => 'completed', 'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('distance_km')
                    ->label('المسافة')
                    ->numeric(1)
                    ->suffix(' كم'),

                Tables\Columns\TextColumn::make('final_price')
                    ->label('السعر')
                    ->money('YER')
                    ->default('—'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'completed' => 'مكتملة', 'cancelled' => 'ملغاة',
                        'pending' => 'انتظار', 'started' => 'جارية',
                    ])
                    ->native(false),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
