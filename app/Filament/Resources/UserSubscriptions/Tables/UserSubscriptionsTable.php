<?php

namespace App\Filament\Resources\UserSubscriptions\Tables;

use App\Models\UserSubscription;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class UserSubscriptionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('اسم المشترك')
                    ->searchable()
                    ->sortable()
                    ->url(fn($record) => route('filament.admin.resources.customers.edit', $record->user_id)),

                TextColumn::make('plan.name')
                    ->label('الباقة')
                    ->searchable()
                    ->sortable(),

                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->label('حالة الاشتراك')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active' => '✅ نشط',
                        'expired' => '⏰ منتهي',
                        'cancelled' => '❌ ملغي',
                        'pending' => '⏳ قيد الانتظار',
                        default => $state,
                    })
                    ->colors([
                        'success' => 'active',
                        'warning' => 'expired',
                        'danger' => 'cancelled',
                        'info' => 'pending',
                    ]),

                TextColumn::make('starts_at')
                    ->label('تاريخ البدء')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('expires_at')
                    ->label('تاريخ الانتهاء')
                    ->date('Y-m-d')
                    ->sortable(),

                TextColumn::make('remaining_trips')
                    ->label('الرحلات المتبقية')
                    ->formatStateUsing(function ($record) {
                        $total = $record->plan->free_trips_per_month ?? 0;
                        $used = $record->trips_used;
                        $remaining = $total - $used;
                        return "{$remaining} من {$total}";
                    }),

                TextColumn::make('total_savings')
                    ->label('التوفير')
                    ->formatStateUsing(fn($state) => number_format($state) . ' ر.ي')
                    ->sortable(),

                \Filament\Tables\Columns\BadgeColumn::make('verification_status')
                    ->label('التحقق')
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending' => '⏳ قيد المراجعة',
                        'approved' => '✅ مقبول',
                        'rejected' => '❌ مرفوض',
                        default => $state,
                    })
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('حالة الاشتراك')
                    ->options([
                        'active' => '✅ نشط',
                        'expired' => '⏰ منتهي',
                        'cancelled' => '❌ ملغي',
                        'pending' => '⏳ قيد الانتظار',
                    ])
                    ->native(false),

                \Filament\Tables\Filters\SelectFilter::make('verification_status')
                    ->label('حالة التحقق')
                    ->options([
                        'pending' => '⏳ قيد المراجعة',
                        'approved' => '✅ مقبول',
                        'rejected' => '❌ مرفوض',
                    ])
                    ->native(false),

                \Filament\Tables\Filters\SelectFilter::make('subscription_plan_id')
                    ->label('الباقة')
                    ->relationship('plan', 'name')
                    ->native(false)
                    ->searchable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),

                \Filament\Actions\Action::make('approve_verification')
                    ->label('قبول الوثيقة')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('قبول وثيقة التحقق')
                    ->modalDescription('هل تم التحقق من صحة الوثيقة المقدمة؟')
                    ->action(function (UserSubscription $record) {
                        $record->update([
                            'verification_status' => 'approved',
                            'status' => 'active',
                        ]);
                        Notification::make()->title('✅ تم قبول الوثيقة وتفعيل الاشتراك')->success()->send();
                    })
                    ->visible(fn(UserSubscription $record) => $record->verification_status === 'pending'),

                \Filament\Actions\Action::make('reject_verification')
                    ->label('رفض الوثيقة')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('رفض وثيقة التحقق')
                    ->modalDescription('هل أنت متأكد من رفض هذه الوثيقة؟')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('rejection_reason')
                            ->label('سبب الرفض')
                            ->required()
                            ->placeholder('مثال: البطاقة الجامعية منتهية الصلاحية'),
                    ])
                    ->action(function (UserSubscription $record, array $data) {
                        $record->update([
                            'verification_status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                        ]);
                        Notification::make()->title('❌ تم رفض الوثيقة')->danger()->send();
                    })
                    ->visible(fn(UserSubscription $record) => $record->verification_status === 'pending'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
