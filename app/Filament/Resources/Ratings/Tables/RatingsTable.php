<?php

namespace App\Filament\Resources\Ratings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RatingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trip_id')
                    ->label('رقم الرحلة')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('customer.name')
                    ->label('الراكب')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.customers.edit', $record->customer_id)),

                TextColumn::make('driver.name')
                    ->label('الكابتن')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.drivers.edit', $record->driver_id)),

                TextColumn::make('driver_rating')
                    ->label('السائق ⭐')
                    ->formatStateUsing(fn($state) => str_repeat('⭐', $state) . " ({$state})")
                    ->sortable(),

                TextColumn::make('service_rating')
                    ->label('الخدمة ⭐')
                    ->formatStateUsing(fn($state) => str_repeat('⭐', $state) . " ({$state})")
                    ->sortable(),

                TextColumn::make('cleanliness_rating')
                    ->label('النظافة ⭐')
                    ->formatStateUsing(fn($state) => $state ? str_repeat('⭐', $state) . " ({$state})" : '—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('average_rating')
                    ->label('المتوسط')
                    ->formatStateUsing(fn($record) => '📊 ' . $record->average_rating . '/5')
                    ->sortable(query: function ($query, string $direction) {
                        return $query->orderByRaw("(driver_rating + service_rating + COALESCE(cleanliness_rating, 0)) / (2 + CASE WHEN cleanliness_rating IS NOT NULL THEN 1 ELSE 0 END) {$direction}");
                    })
                    ->color(fn($record) => match(true) {
                        $record->average_rating >= 4.0 => 'success',
                        $record->average_rating >= 3.0 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('comment')
                    ->label('التعليق')
                    ->limit(30)
                    ->searchable()
                    ->toggleable()
                    ->tooltip(fn($record) => $record->comment),

                TextColumn::make('tags')
                    ->label('الوسوم')
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '—';
                        $tags = is_array($state) ? $state : json_decode($state, true);
                        if (!$tags) return '—';
                        return collect($tags)->map(fn($t) => "🏷️{$t}")->join(' ');
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('تاريخ التقييم')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('driver_rating')
                    ->label('تقييم السائق')
                    ->options([
                        '5' => '⭐⭐⭐⭐⭐ ممتاز (5)',
                        '4' => '⭐⭐⭐⭐ جيد جداً (4)',
                        '3' => '⭐⭐⭐ جيد (3)',
                        '2' => '⭐⭐ مقبول (2)',
                        '1' => '⭐ ضعيف (1)',
                    ])
                    ->native(false),

                \Filament\Tables\Filters\Filter::make('low_ratings')
                    ->label('تقييمات منخفضة فقط (≤ 2)')
                    ->query(fn($query) => $query->where('driver_rating', '<=', 2))
                    ->toggle(),

                \Filament\Tables\Filters\Filter::make('has_comment')
                    ->label('يحتوي على تعليق')
                    ->query(fn($query) => $query->whereNotNull('comment')->where('comment', '!=', ''))
                    ->toggle(),

                \Filament\Tables\Filters\Filter::make('today')
                    ->label('تقييمات اليوم')
                    ->query(fn($query) => $query->whereDate('created_at', today()))
                    ->toggle(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
