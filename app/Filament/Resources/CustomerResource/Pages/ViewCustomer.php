<?php

namespace App\Filament\Resources\CustomerResource\Pages;

use App\Filament\Resources\CustomerResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use Filament\Infolists;
use Filament\Schemas\Schema;
use App\Models\Trip;
use App\Models\Complaint;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

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
                \Filament\Schemas\Components\Section::make('👤 بيانات العميل')
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
                            ->formatStateUsing(fn($state) => $state ? '🟢 نشط' : '🔴 موقوف')
                            ->color(fn($state) => $state ? 'success' : 'danger'),
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('تاريخ التسجيل')
                            ->dateTime('Y-m-d'),
                    ])
                    ->columns(3),

                \Filament\Schemas\Components\Section::make('📊 إحصائيات العميل')
                    ->schema([
                        Infolists\Components\TextEntry::make('total_trips')
                            ->label('إجمالي الرحلات')
                            ->state(fn($record) => Trip::where('customer_id', $record->id)->count())
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('completed_trips')
                            ->label('مكتملة')
                            ->state(fn($record) => Trip::where('customer_id', $record->id)->where('status', 'completed')->count())
                            ->badge()
                            ->color('success'),

                        Infolists\Components\TextEntry::make('total_spent')
                            ->label('إجمالي الإنفاق')
                            ->state(fn($record) => number_format(Trip::where('customer_id', $record->id)->where('status', 'completed')->sum('final_price')) . ' ر.ي')
                            ->color('primary'),

                        Infolists\Components\TextEntry::make('open_complaints')
                            ->label('شكاوى مفتوحة')
                            ->state(fn($record) => Complaint::where('user_id', $record->id)->where('status', 'open')->count())
                            ->badge()
                            ->color(fn($state) => $state > 0 ? 'danger' : 'success'),

                        Infolists\Components\TextEntry::make('last_trip')
                            ->label('آخر رحلة')
                            ->state(function ($record) {
                                $trip = Trip::where('customer_id', $record->id)->latest()->first();
                                return $trip ? $trip->created_at->diffForHumans() : 'لا توجد رحلات';
                            }),

                        Infolists\Components\TextEntry::make('fav_category')
                            ->label('الفئة المفضلة')
                            ->state(function ($record) {
                                $category = Trip::where('customer_id', $record->id)
                                    ->selectRaw('category, COUNT(*) as cnt')
                                    ->groupBy('category')
                                    ->orderByDesc('cnt')
                                    ->first();
                                return $category ? match ($category->category) {
                                    'economy' => '🚗 توفير', 'vip' => '🌟 VIP', 'bus' => '🚌 باص', default => $category->category,
                                } : '—';
                            }),
                    ])
                    ->columns(3),
            ]);
    }
}
