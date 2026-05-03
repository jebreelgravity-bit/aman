<?php

namespace App\Filament\Resources\ActivityLogResource\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('المشرف')
                    ->searchable()
                    ->sortable()
                    ->default('نظام'),

                \Filament\Tables\Columns\BadgeColumn::make('action')
                    ->label('الإجراء')
                    ->searchable()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'trip_created' => '🆕 رحلة جديدة',
                        'trip_status_changed' => '🔄 تغيير حالة رحلة',
                        'transaction_created' => '💰 معاملة جديدة',
                        'pricing_updated' => '💲 تحديث أسعار',
                        'rewards_distributed' => '🎁 توزيع مكافآت',
                        'user_role_changed' => '👤 تغيير دور',
                        'created' => '➕ إنشاء',
                        'updated' => '✏️ تحديث',
                        'deleted' => '🗑️ حذف',
                        'login' => '🔑 تسجيل دخول',
                        'logout' => '🚪 تسجيل خروج',
                        default => $state,
                    })
                    ->colors([
                        'success' => ['trip_created', 'created', 'rewards_distributed'],
                        'warning' => ['updated', 'pricing_updated', 'trip_status_changed'],
                        'danger' => ['deleted'],
                        'info' => ['login', 'transaction_created'],
                        'gray' => ['logout', 'user_role_changed'],
                    ]),

                TextColumn::make('description')
                    ->label('الوصف')
                    ->searchable()
                    ->limit(50)
                    ->tooltip(fn($record) => $record->description),

                TextColumn::make('model_type')
                    ->label('على')
                    ->formatStateUsing(fn($state) => $state ? class_basename($state) : '—')
                    ->sortable(),

                TextColumn::make('old_new_values')
                    ->label('التغييرات')
                    ->formatStateUsing(function ($record) {
                        if (!$record->old_values && !$record->new_values) return '—';
                        $changes = [];
                        if ($record->old_values) {
                            $old = is_array($record->old_values) ? $record->old_values : json_decode($record->old_values, true);
                            if ($old) $changes[] = 'القديم: ' . json_encode($old, JSON_UNESCAPED_UNICODE);
                        }
                        if ($record->new_values) {
                            $new = is_array($record->new_values) ? $record->new_values : json_decode($record->new_values, true);
                            if ($new) $changes[] = 'الجديد: ' . json_encode($new, JSON_UNESCAPED_UNICODE);
                        }
                        return implode(' → ', $changes) ?: '—';
                    })
                    ->limit(40)
                    ->toggleable()
                    ->tooltip(fn($record) => ($record->old_values ? 'القديم: ' . json_encode($record->old_values, JSON_UNESCAPED_UNICODE) : '') . ($record->new_values ? ' | الجديد: ' . json_encode($record->new_values, JSON_UNESCAPED_UNICODE) : '')),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('user_agent')
                    ->label('المتصفح')
                    ->limit(20)
                    ->tooltip(fn($record) => $record->user_agent)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('action')
                    ->label('نوع الإجراء')
                    ->options([
                        'trip_created' => '🆕 رحلة جديدة',
                        'trip_status_changed' => '🔄 تغيير حالة رحلة',
                        'transaction_created' => '💰 معاملة جديدة',
                        'pricing_updated' => '💲 تحديث أسعار',
                        'rewards_distributed' => '🎁 توزيع مكافآت',
                        'user_role_changed' => '👤 تغيير دور',
                        'created' => '➕ إنشاء',
                        'updated' => '✏️ تحديث',
                        'deleted' => '🗑️ حذف',
                        'login' => '🔑 تسجيل دخول',
                    ])
                    ->native(false),

                \Filament\Tables\Filters\SelectFilter::make('user_id')
                    ->label('المشرف')
                    ->relationship('user', 'name')
                    ->native(false)
                    ->searchable(),

                \Filament\Tables\Filters\Filter::make('created_at')
                    ->label('الفترة الزمنية')
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
                \Filament\Actions\ViewAction::make()->label('عرض التفاصيل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
