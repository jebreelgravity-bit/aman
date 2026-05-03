<?php

namespace App\Filament\Resources\CouponUsageResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponUsagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('coupon.code')
                    ->label('كود الكوبون')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('coupon.name')
                    ->label('اسم الكوبون')
                    ->searchable()
                    ->limit(20),

                \Filament\Tables\Columns\BadgeColumn::make('coupon.type')
                    ->label('نوع الخصم')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'percentage' => ' نسبة %',
                        'fixed' => 'مبلغ ثابت',
                        default => $state,
                    })
                    ->colors([
                        'info' => 'percentage',
                        'success' => 'fixed',
                    ]),

                TextColumn::make('user.name')
                    ->label('المستخدم')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.customers.edit', $record->user_id)),

                TextColumn::make('trip_id')
                    ->label('رقم الرحلة')
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.trips.view', $record->trip_id)),

                TextColumn::make('discount_amount')
                    ->label('حجم الوفر المالي')
                    ->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')
                    ->sortable()
                    ->color('success')
                    ->weight('bold'),

                TextColumn::make('trip.final_price')
                    ->label('سعر الرحلة بعد الخصم')
                    ->formatStateUsing(fn($state) => $state ? number_format($state) . ' ر.ي' : '—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('تاريخ الاستخدام')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('coupon_id')
                    ->label('الكوبون')
                    ->relationship('coupon', 'code')
                    ->native(false)
                    ->searchable(),

                \Filament\Tables\Filters\SelectFilter::make('user_id')
                    ->label('المستخدم')
                    ->relationship('user', 'name')
                    ->native(false)
                    ->searchable(),

                \Filament\Tables\Filters\Filter::make('created_at')
                    ->label('الفترة')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')->label('من'),
                        \Filament\Forms\Components\DatePicker::make('to')->label('إلى'),
                    ])
                    ->query(fn($query, $data) => $query
                        ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['to'], fn($q) => $q->whereDate('created_at', '<=', $data['to']))
                    ),
            ])
            ->recordActions([
                ViewAction::make()->label('عرض'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
