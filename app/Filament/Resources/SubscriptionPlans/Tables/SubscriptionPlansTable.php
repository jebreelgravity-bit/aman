<?php

namespace App\Filament\Resources\SubscriptionPlans\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SubscriptionPlansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable(),

                \Filament\Tables\Columns\BadgeColumn::make('type')
                    ->label('النوع')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'student' => 'طلاب',
                        'employee' => 'موظفين',
                        'corporate' => 'شركات',
                        default => $state,
                    })
                    ->colors([
                        'info' => 'student',
                        'warning' => 'employee',
                        'primary' => 'corporate',
                    ]),

                TextColumn::make('monthly_price')
                    ->label('السعر الشهري')
                    ->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')
                    ->sortable(),

                TextColumn::make('discount_percentage')
                    ->label('الخصم')
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->sortable(),

                TextColumn::make('free_trips_per_month')
                    ->label('الرحلات المجانية')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('priority_booking')
                    ->label('أولوية حجز')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_active')
                    ->label('النشاط')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->recordActions([
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
