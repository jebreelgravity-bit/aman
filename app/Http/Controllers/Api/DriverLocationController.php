<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverLocation;
use App\Models\Trip;
use Illuminate\Http\Request;

class DriverLocationController extends Controller
{
    /**
     * استقبال موقع GPS من السائق (كل 10 ثواني)
     * POST /api/driver/location
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'speed'     => 'nullable|numeric|min:0',
            'heading'   => 'nullable|numeric|between:0,360',
            'accuracy'  => 'nullable|numeric|min:0',
            'status'    => 'nullable|in:online,on_trip,offline',
            'trip_id'   => 'nullable|exists:trips,id',
        ]);

        $driver = $request->user();

        if (! $driver || $driver->role !== 'driver') {
            return response()->json(['error' => 'غير مصرح'], 403);
        }

        $location = DriverLocation::create([
            'driver_id'   => $driver->id,
            'latitude'    => $validated['latitude'],
            'longitude'   => $validated['longitude'],
            'speed'       => $validated['speed'] ?? null,
            'heading'     => $validated['heading'] ?? null,
            'accuracy'    => $validated['accuracy'] ?? null,
            'status'      => $validated['status'] ?? 'online',
            'trip_id'     => $validated['trip_id'] ?? null,
            'recorded_at' => now(),
        ]);

        // حذف السجلات القديمة (احتفظ بآخر 100 فقط لكل سائق)
        DriverLocation::where('driver_id', $driver->id)
            ->orderByDesc('recorded_at')
            ->skip(100)
            ->take(99999)
            ->delete();

        return response()->json([
            'success' => true,
            'location_id' => $location->id,
            'recorded_at' => $location->recorded_at,
        ]);
    }

    /**
     * جلب موقع سائق لرحلة معينة (للتتبع الحي)
     * GET /api/trips/{trip}/driver-location
     */
    public function getForTrip(int $tripId)
    {
        $trip = Trip::findOrFail($tripId);

        if (! $trip->driver_id) {
            return response()->json(['error' => 'لم يتم تعيين سائق'], 404);
        }

        $location = DriverLocation::latestForDriver($trip->driver_id);

        if (! $location) {
            return response()->json(['error' => 'موقع السائق غير متاح'], 404);
        }

        $driver = $trip->driver ?? \App\Models\User::find($trip->driver_id);

        return response()->json([
            'latitude'    => (float) $location->latitude,
            'longitude'   => (float) $location->longitude,
            'speed'       => $location->speed,
            'heading'     => $location->heading,
            'status'      => $location->status,
            'recorded_at' => $location->recorded_at?->toIso8601String(),
            'driver' => [
                'name'   => $driver?->name,
                'phone'  => $driver?->phone,
                'vehicle_plate' => $driver?->vehicle_plate,
                'vehicle_model' => $driver?->vehicle_model,
                'vehicle_grade' => $driver?->vehicle_grade,
                'avatar' => $driver?->avatar_url
                    ? url('storage/' . $driver->avatar_url)
                    : null,
            ],
        ]);
    }
}
