<?php

namespace App\Filament\Widgets;

use App\Models\Trip;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestTripsWidget extends BaseWidget
{
    protected static ?string $heading = 'آخر الرحلات';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Trip::with(['customer', 'driver'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('رقم الرحلة')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('العميل')
                    ->searchable(),

                Tables\Columns\TextColumn::make('driver.name')
                    ->label('السائق')
                    ->searchable()
                    ->default('غير محدد'),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('الفئة')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'economy' => 'توفير',
                        'vip' => 'VIP',
                        'bus' => 'باص',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'economy',
                        'warning' => 'vip',
                        'primary' => 'bus',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => 'قيد الانتظار',
                        'accepted' => 'مقبولة',
                        'started' => 'جارية',
                        'completed' => 'مكتملة',
                        'cancelled' => 'ملغاة',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'info' => 'accepted',
                        'primary' => 'started',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('distance_km')
                    ->label('المسافة (كم)')
                    ->numeric(2),

                Tables\Columns\TextColumn::make('final_price')
                    ->label('السعر')
                    ->money('YER')
                    ->default('غير محدد'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
