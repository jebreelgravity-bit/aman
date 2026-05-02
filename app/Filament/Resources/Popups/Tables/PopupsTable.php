<?php

namespace App\Filament\Resources\Popups\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PopupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_url')
                    ->label('الصورة')
                    ->circular(),

                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->limit(20),

                \Filament\Tables\Columns\BadgeColumn::make('target_audience')
                    ->label('الجمهور')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'all' => 'الجميع',
                        'customers' => 'الركاب',
                        'drivers' => 'الكباتن',
                        'new_users' => '🆕 جدد',
                        'inactive_users' => '💤 غير نشطين',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'all',
                        'info' => 'customers',
                        'warning' => 'drivers',
                        'success' => 'new_users',
                        'danger' => 'inactive_users',
                    ]),

                \Filament\Tables\Columns\BadgeColumn::make('display_frequency')
                    ->label('الظهور')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'once' => 'مرة واحدة',
                        'daily' => 'يومياً',
                        'always' => 'دائماً',
                        default => $state,
                    })
                    ->colors(['gray']),

                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('views_count')
                    ->label('مشاهدات')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('clicks_count')
                    ->label('نقرات')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                TextColumn::make('starts_at')
                    ->label('يبدأ في')
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
