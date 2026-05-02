<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use App\Models\Trip;
use App\Models\User;

class LiveTrackingPage extends Page
{
    protected static \BackedEnum|string|null $navigationIcon = Heroicon::OutlinedMap;
    protected static ?string $navigationLabel = 'تتبع حي';
    protected static ?string $title = 'خريطة التتبع الحي';
    protected static \UnitEnum|string|null $navigationGroup = 'إدارة الرحلات';
    protected static ?int $navigationSort = 2;
    protected string $view = 'filament.pages.live-tracking';

    public function getViewData(): array
    {
        $activeDrivers = User::where('role', 'driver')
            ->where('is_active', true)
            ->get()
            ->map(fn($driver) => [
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->phone,
                'status' => $this->getDriverStatus($driver->id),
                'latitude' => $driver->latitude ?? 15.3694,
                'longitude' => $driver->longitude ?? 44.1910,
            ]);

        $activeTrips = Trip::whereIn('status', ['accepted', 'started'])
            ->with(['driver', 'customer'])
            ->get()
            ->map(fn($trip) => [
                'id' => $trip->id,
                'driver_name' => $trip->driver?->name,
                'customer_name' => $trip->customer?->name,
                'status' => $trip->status,
                'pickup_address' => $trip->pickup_address,
                'dropoff_address' => $trip->dropoff_address,
                'pickup_latitude' => $trip->pickup_latitude,
                'pickup_longitude' => $trip->pickup_longitude,
                'dropoff_latitude' => $trip->dropoff_latitude,
                'dropoff_longitude' => $trip->dropoff_longitude,
                'category' => $trip->category,
            ]);

        return [
            'drivers' => $activeDrivers,
            'trips' => $activeTrips,
            'default_lat' => 15.3694,
            'default_lng' => 44.1910,
            'default_zoom' => 13,
        ];
    }

    private function getDriverStatus(int $driverId): string
    {
        $activeTrip = Trip::where('driver_id', $driverId)
            ->whereIn('status', ['accepted', 'started'])
            ->exists();

        if ($activeTrip) return 'in_trip';
        return 'available';
    }
}
