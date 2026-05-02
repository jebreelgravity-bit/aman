<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerComplaintsRelationManager extends RelationManager
{
    protected static string $relationship = 'complaints';

    protected static ?string $title = '📝 شكاوى العميل';

    protected static ?string $modelLabel = 'شكوى';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ticket_number')
                    ->label('رقم التذكرة')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('الموضوع')
                    ->limit(40)
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('category')
                    ->label('التصنيف')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'delay' => 'تأخير', 'behavior' => 'سلوك', 'pricing' => 'تسعير',
                        'safety' => 'أمان', 'cleanliness' => 'نظافة', 'other' => 'أخرى',
                        default => $state,
                    }),

                Tables\Columns\BadgeColumn::make('priority')
                    ->label('الأولوية')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'low' => 'منخفضة', 'medium' => 'متوسطة',
                        'high' => 'عالية', 'urgent' => 'عاجلة',
                        default => $state,
                    })
                    ->colors([
                        'gray' => 'low', 'info' => 'medium',
                        'warning' => 'high', 'danger' => 'urgent',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'open' => 'مفتوحة', 'in_progress' => 'قيد المعالجة',
                        'resolved' => 'محلولة', 'closed' => 'مغلقة',
                        default => $state,
                    })
                    ->colors([
                        'danger' => 'open', 'warning' => 'in_progress',
                        'success' => 'resolved', 'gray' => 'closed',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
