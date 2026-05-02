<?php

namespace App\Filament\Resources\DriverResource\Pages;

use App\Filament\Resources\DriverResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Infolists;
use Filament\Schemas\Schema;
use App\Models\Trip;
use App\Models\Transaction;
use App\Models\Rating;

class ViewDriver extends ViewRecord
{
    protected static string $resource = DriverResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                // ─── البيانات الشخصية ───
                \Filament\Schemas\Components\Section::make('👤 البيانات الشخصية')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('الاسم'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('البريد الإلكتروني')
                            ->copyable(),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('الهاتف')
                            ->copyable()
                            ->default('—'),
                        Infolists\Components\TextEntry::make('is_active')
                            ->label('الحالة')
                            ->badge()
                            ->formatStateUsing(fn($state) => $state ? '🟢 نشط' : '🔴 معلّق')
                            ->color(fn($state) => $state ? 'success' : 'danger'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ التسجيل')
                            ->dateTime('Y-m-d'),
                    ])
                    ->columns(3),

                // ─── الإحصائيات ───
                \Filament\Schemas\Components\Section::make('📊 إحصائيات الأداء')
                    ->schema([
                        Infolists\Components\TextEntry::make('driverTrips_count')
                            ->label('إجمالي الرحلات')
                            ->state(fn($record) => Trip::where('driver_id', $record->id)->count())
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('completed_trips')
                            ->label('رحلات مكتملة')
                            ->state(fn($record) => Trip::where('driver_id', $record->id)->where('status', 'completed')->count())
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('cancelled_trips')
                            ->label('رحلات ملغاة')
                            ->state(fn($record) => Trip::where('driver_id', $record->id)->where('status', 'cancelled')->count())
                            ->badge()
                            ->color('danger'),

                        Infolists\Components\TextEntry::make('completion_rate')
                            ->label('معدل الإنجاز')
                            ->state(function ($record) {
                                $total = Trip::where('driver_id', $record->id)->count();
                                $completed = Trip::where('driver_id', $record->id)->where('status', 'completed')->count();
                                return $total > 0 ? round(($completed / $total) * 100, 1) . '%' : '0%';
                            })
                            ->badge()
                            ->color(fn($state) => (float) $state >= 80 ? 'success' : ((float) $state >= 50 ? 'warning' : 'danger')),

                        Infolists\Components\TextEntry::make('total_earnings')
                            ->label('إجمالي الأرباح')
                            ->state(fn($record) => number_format(Transaction::where('driver_id', $record->id)->where('payment_status', 'completed')->sum('driver_earnings')) . ' ر.ي')
                            ->color('success'),

                        Infolists\Components\TextEntry::make('avg_rating')
                            ->label('متوسط التقييم')
                            ->state(function ($record) {
                                $avg = Rating::where('driver_id', $record->id)->avg('driver_rating');
                                $count = Rating::where('driver_id', $record->id)->count();
                                return $avg ? round($avg, 1) . ' ⭐ (' . $count . ' تقييم)' : 'لا توجد تقييمات';
                            })
                            ->color(fn($state) => str_contains($state, '⭐') ? 'warning' : 'gray'),

                        Infolists\Components\TextEntry::make('this_week_trips')
                            ->label('رحلات هذا الأسبوع')
                            ->state(fn($record) => Trip::where('driver_id', $record->id)->where('created_at', '>=', now()->startOfWeek())->count())
                            ->badge()
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('this_week_earnings')
                            ->label('أرباح هذا الأسبوع')
                            ->state(fn($record) => number_format(Transaction::where('driver_id', $record->id)->where('payment_status', 'completed')->where('created_at', '>=', now()->startOfWeek())->sum('driver_earnings')) . ' ر.ي')
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('pending_payments')
                            ->label('مدفوعات معلقة')
                            ->state(fn($record) => number_format(Transaction::where('driver_id', $record->id)->where('payment_status', 'pending')->sum('driver_earnings')) . ' ر.ي')
                            ->color('warning'),
                    ])
                    ->columns(3),
            ]);
    }
}
