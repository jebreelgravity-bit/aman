<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerTripsRelationManager extends RelationManager
{
    protected static string $relationship = 'customerTrips';

    protected static ?string $title = '🚗 رحلات العميل';

    protected static ?string $modelLabel = 'رحلة';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('driver.name')
                    ->label('السائق')
                    ->searchable()
                    ->default('غير محدد'),

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

                Tables\Columns\TextColumn::make('pickup_address')
                    ->label('من')
                    ->limit(25),

                Tables\Columns\TextColumn::make('dropoff_address')
                    ->label('إلى')
                    ->limit(25),

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
