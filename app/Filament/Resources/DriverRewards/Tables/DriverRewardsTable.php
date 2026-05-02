<?php

namespace App\Filament\Resources\DriverRewards\Tables;

use App\Models\DriverReward;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class DriverRewardsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('driver.name')
                    ->label('اسم الكابتن')
                    ->sortable()
                    ->searchable()
                    ->url(fn($record) => route('filament.admin.resources.drivers.edit', $record->driver_id)),

                TextColumn::make('week_number')
                    ->label('الأسبوع')
                    ->formatStateUsing(fn($record) => "الأسبوع {$record->week_number} - {$record->year}")
                    ->sortable(),

                TextColumn::make('week_start_date')
                    ->label('من')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('week_end_date')
                    ->label('إلى')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_trips')
                    ->label('عدد الرحلات')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('total_earnings')
                    ->label('إجمالي الأرباح')
                    ->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('calculated_reward')
                    ->label('المكافأة المحتسبة')
                    ->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('actual_reward')
                    ->label('المكافأة الفعلية')
                    ->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('reduction_info')
                    ->label('قاعدة الـ 10%')
                    ->formatStateUsing(function ($record) {
                        if ($record->calculated_reward <= 0) return '—';
                        $reduction = $record->getReductionPercentage();
                        if ($reduction > 0) {
                            return "⚠️ خُفضت بنسبة {$reduction}%\n(تجاوزت سقف 10% من أرباح التطبيق)";
                        }
                        return '✅ ضمن السقف';
                    })
                    ->color(fn($record) => $record->calculated_reward > 0 && $record->getReductionPercentage() > 0 ? 'warning' : 'success')
                    ->html()
                    ->toggleable(),

                \Filament\Tables\Columns\BadgeColumn::make('distribution_status')
                    ->label('الحالة')
                    ->formatStateUsing(fn($record) => $record->is_distributed ? '✅ تم الصرف' : '⏳ قيد المعالجة')
                    ->colors(fn($record) => $record->is_distributed ? ['success'] : ['warning']),

                TextColumn::make('distributed_at')
                    ->label('تاريخ الصرف')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_distributed')
                    ->label('حالة التوزيع')
                    ->trueLabel('تم الصرف')
                    ->falseLabel('قيد المعالجة'),

                \Filament\Tables\Filters\SelectFilter::make('year')
                    ->label('السنة')
                    ->options(function () {
                        $years = DriverReward::select('year')->distinct()->pluck('year')->toArray();
                        return collect($years)->mapWithKeys(fn($y) => [$y => $y])->toArray();
                    })
                    ->native(false),

                \Filament\Tables\Filters\SelectFilter::make('week_number')
                    ->label('رقم الأسبوع')
                    ->options(function () {
                        $weeks = range(1, 52);
                        return collect($weeks)->mapWithKeys(fn($w) => [$w => "الأسبوع {$w}"])->toArray();
                    })
                    ->native(false),
            ])
            ->recordActions([
                EditAction::make(),

                \Filament\Actions\Action::make('distribute')
                    ->label('تم الصرف')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد صرف المكافأة')
                    ->modalDescription('هل تم صرف هذه المكافأة للسائق فعلاً؟')
                    ->action(function (DriverReward $record) {
                        $record->update([
                            'is_distributed' => true,
                            'distributed_at' => now(),
                        ]);
                        Notification::make()
                            ->title('✅ تم صرف المكافأة')
                            ->body("تم صرف " . number_format($record->actual_reward) . " ر.ي للسائق {$record->driver->name}")
                            ->success()
                            ->send();
                    })
                    ->visible(fn(DriverReward $record) => !$record->is_distributed),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    \Filament\Actions\BulkAction::make('bulk_distribute')
                        ->label('صرف المكافآت المحددة')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('صرف مكافآت متعددة')
                        ->modalDescription('هل تريد صرف جميع المكافآت المحددة؟')
                        ->action(function ($records) {
                            $count = 0;
                            $records->each(function ($record) {
                                if (!$record->is_distributed) {
                                    $record->update([
                                        'is_distributed' => true,
                                        'distributed_at' => now(),
                                    ]);
                                    $count++;
                                }
                            });
                            Notification::make()
                                ->title("✅ تم صرف {$count} مكافأة")
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
