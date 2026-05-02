<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Exports\TransactionExporter;
use App\Models\Transaction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('trip_id')
                    ->label('رقم الرحلة')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.trips.view', $record->trip_id)),

                TextColumn::make('customer.name')
                    ->label('اسم العميل')
                    ->searchable()
                    ->sortable()
                    ->default('—')
                    ->url(fn($record) => $record->customer_id ? route('filament.admin.resources.customers.edit', $record->customer_id) : null),

                TextColumn::make('driver.name')
                    ->label('اسم السائق')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.drivers.edit', $record->driver_id)),

                TextColumn::make('trip.category')
                    ->label('الفئة')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'economy' => '🚗 توفير',
                        'vip' => '👑 VIP',
                        'bus' => '🚌 باص',
                        default => $state ?? '—',
                    })
                    ->sortable(),

                TextColumn::make('trip_price')
                    ->label('المبلغ الإجمالي')
                    ->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('الإجمالي')->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')),

                TextColumn::make('app_commission')
                    ->label('عمولة التطبيق 20%')
                    ->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('العمولة')->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')),

                TextColumn::make('driver_earnings')
                    ->label('صافي ربح السائق')
                    ->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')
                    ->sortable()
                    ->summarize(\Filament\Tables\Columns\Summarizers\Sum::make()->label('للسائقين')->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')),

                \Filament\Tables\Columns\BadgeColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'cash' => '💵 نقدي',
                        'e_wallet' => '📱 محفظة',
                        'bank_transfer' => '🏦 تحويل',
                        'mobile_money' => '📲 موبايل',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'cash',
                        'info' => 'e_wallet',
                        'warning' => 'bank_transfer',
                        'primary' => 'mobile_money',
                    ]),

                \Filament\Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('الحالة')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => '⏳ معلقة',
                        'completed' => '✅ مكتمل',
                        'failed' => '❌ فشلت',
                        'under_review' => '🔍 قيد المراجعة',
                        'refunded' => '↩️ مسترجعة',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'danger' => 'failed',
                        'info' => 'under_review',
                        'gray' => 'refunded',
                    ]),

                TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('payment_status')
                    ->label('حالة الدفع')
                    ->options([
                        'pending' => '⏳ معلقة',
                        'completed' => '✅ مكتمل',
                        'failed' => '❌ فشلت',
                        'under_review' => '🔍 قيد المراجعة',
                        'refunded' => '↩️ مسترجعة',
                    ])
                    ->native(false),

                \Filament\Tables\Filters\SelectFilter::make('payment_method')
                    ->label('طريقة الدفع')
                    ->options([
                        'cash' => '💵 نقدي',
                        'e_wallet' => '📱 محفظة إلكترونية',
                        'bank_transfer' => '🏦 تحويل بنكي',
                        'mobile_money' => '📲 موبايل موني',
                    ])
                    ->native(false),

                \Filament\Tables\Filters\SelectFilter::make('trip_category')
                    ->label('فئة الرحلة')
                    ->options([
                        'economy' => '🚗 توفير',
                        'vip' => '👑 VIP',
                        'bus' => '🚌 باص',
                    ])
                    ->native(false)
                    ->query(fn($query, $state) => $state ? $query->whereHas('trip', fn($q) => $q->where('category', $state)) : $query),

                \Filament\Tables\Filters\Filter::make('created_at')
                    ->label('تاريخ المعاملة')
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
                EditAction::make(),

                \Filament\Actions\Action::make('confirm_payment')
                    ->label('تأكيد الدفع')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد استلام الدفعة')
                    ->modalDescription('هل تم التحقق من استلام المبلغ فعلاً؟')
                    ->action(function (Transaction $record) {
                        $record->update([
                            'payment_status' => 'completed',
                            'paid_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                        Notification::make()->title('✅ تم تأكيد الدفع')->success()->send();
                    })
                    ->visible(fn(Transaction $record) => in_array($record->payment_status, ['pending', 'under_review'])),

                \Filament\Actions\Action::make('mark_review')
                    ->label('قيد المراجعة')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function (Transaction $record) {
                        $record->update(['payment_status' => 'under_review']);
                        Notification::make()->title('🔍 تم وضع المعاملة قيد المراجعة')->info()->send();
                    })
                    ->visible(fn(Transaction $record) => $record->payment_status === 'pending'),
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('تصدير كشوفات')
                    ->exporter(TransactionExporter::class),

                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    \Filament\Actions\BulkAction::make('bulk_confirm')
                        ->label('تأكيد دفع المحددين')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $records->each(function ($record) {
                                if (in_array($record->payment_status, ['pending', 'under_review'])) {
                                    $record->update([
                                        'payment_status' => 'completed',
                                        'paid_at' => now(),
                                        'verified_by' => auth()->id(),
                                    ]);
                                }
                            });
                            Notification::make()->title('تم تأكيد الدفعات المحددة')->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
