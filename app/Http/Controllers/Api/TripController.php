<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Http\Resources\TripResource;
use App\Services\TripPriceCalculator;
use App\Events\TripRequested;
use App\Events\TripStatusUpdated;

class TripController extends Controller
{
    public function createTrip(Request $request, TripPriceCalculator $calculator)
    {
        $validated = $request->validate([
            'category' => 'required|string|in:economy,vip,bus',
            'distance_km' => 'required|numeric|min:0.1',
            'pickup_address' => 'required|string',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'dropoff_address' => 'required|string',
            'dropoff_latitude' => 'required|numeric',
            'dropoff_longitude' => 'required|numeric',
        ]);

        $pricing = $calculator->calculate($validated['category'], $validated['distance_km']);

        $trip = Trip::create([
            'customer_id' => $request->user()->id,
            'category' => $validated['category'],
            'distance_km' => $validated['distance_km'],
            'pickup_address' => $validated['pickup_address'],
            'pickup_latitude' => $validated['pickup_latitude'],
            'pickup_longitude' => $validated['pickup_longitude'],
            'dropoff_address' => $validated['dropoff_address'],
            'dropoff_latitude' => $validated['dropoff_latitude'],
            'dropoff_longitude' => $validated['dropoff_longitude'],
            'estimated_price' => $pricing['total_price'],
            'status' => 'pending',
        ]);

        // Dispatch the Real-time Event to notify drivers
        TripRequested::dispatch($trip);

        return response()->json([
            'status' => 'success',
            'message' => 'تم طلب الرحلة بنجاح',
            'data' => [
                'trip' => new TripResource($trip),
                'pricing_details' => $pricing,
            ]
        ], 201);
    }

    public function myTrips(Request $request)
    {
        $user = $request->user();
        
        $trips = Trip::with(['customer', 'driver'])
            ->where(function($query) use ($user) {
                if ($user->role === 'customer') {
                    $query->where('customer_id', $user->id);
                } elseif ($user->role === 'driver') {
                    $query->where('driver_id', $user->id);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => TripResource::collection($trips)->response()->getData(true)
        ]);
    }

    public function acceptTrip(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);

        if ($trip->status !== 'pending') {
            return response()->json([
                'status' => 'error',
                'message' => 'الرحلة غير متاحة للقبول',
            ], 400);
        }

        $trip->update([
            'driver_id' => $request->user()->id,
            'status' => 'accepted',
        ]);

        // Dispatch Event to notify the customer that their trip was accepted
        TripStatusUpdated::dispatch($trip);

        return response()->json([
            'status' => 'success',
            'message' => 'تم قبول الرحلة بنجاح',
            'data' => [
                'trip' => new TripResource($trip)
            ]
        ]);
    }

    public function startTrip(Request $request, $id)
    {
        $trip = Trip::findOrFail($id);

        if ($trip->driver_id !== $request->user()->id || $trip->status !== 'accepted') {
            return response()->json(['status' => 'error', 'message' => 'لا يمكنك بدء هذه الرحلة'], 400);
        }

        $trip->update([
            'status' => 'started',
            'started_at' => now(),
        ]);

        TripStatusUpdated::dispatch($trip);

        return response()->json([
            'status' => 'success',
            'message' => 'تم بدء الرحلة',
            'data' => ['trip' => new TripResource($trip)]
        ]);
    }

    public function completeTrip(Request $request, $id, TripPriceCalculator $calculator)
    {
        $trip = Trip::findOrFail($id);

        if ($trip->driver_id !== $request->user()->id || $trip->status !== 'started') {
            return response()->json(['status' => 'error', 'message' => 'لا يمكنك إنهاء هذه الرحلة'], 400);
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            $trip->update([
                'status' => 'completed',
                'completed_at' => now(),
                'final_price' => $trip->estimated_price, // Assuming final price is estimated price for now
            ]);

            // Calculate financial breakdown
            $breakdown = $calculator->calculateFinancialBreakdown($trip->final_price);

            // Get customer and driver wallets
            $customerWallet = $trip->customer->wallet()->firstOrCreate(
                ['user_id' => $trip->customer_id],
                ['balance' => 0.00, 'currency' => 'YER']
            );

            $driverWallet = $trip->driver->wallet()->firstOrCreate(
                ['user_id' => $trip->driver_id],
                ['balance' => 0.00, 'currency' => 'YER']
            );

            // Check if customer has enough balance
            if ($customerWallet->balance < $trip->final_price) {
                // In a real app, this might fall back to cash payment. 
                // For this implementation, we allow negative balance or enforce deposit.
                // Let's just deduct it (allowing negative balance for testing purposes, or we could throw exception)
            }

            // Deduct from customer
            $customerWallet->balance -= $trip->final_price;
            $customerWallet->save();

            \App\Models\WalletTransaction::create([
                'wallet_id' => $customerWallet->id,
                'amount' => $trip->final_price,
                'type' => 'debit',
                'reference_id' => 'TRIP-' . $trip->id,
                'status' => 'completed',
                'description' => 'دفع قيمة الرحلة رقم ' . $trip->id,
            ]);

            // Add to driver (earnings after commission)
            $driverWallet->balance += $breakdown['driver_earnings'];
            $driverWallet->save();

            \App\Models\WalletTransaction::create([
                'wallet_id' => $driverWallet->id,
                'amount' => $breakdown['driver_earnings'],
                'type' => 'credit',
                'reference_id' => 'TRIP-EARN-' . $trip->id,
                'status' => 'completed',
                'description' => 'أرباح الرحلة رقم ' . $trip->id,
            ]);

            // Create Transaction Record
            \App\Models\Transaction::create([
                'trip_id' => $trip->id,
                'driver_id' => $trip->driver_id,
                'customer_id' => $trip->customer_id,
                'trip_price' => $breakdown['trip_price'],
                'app_commission_rate' => $breakdown['app_commission_rate'],
                'app_commission' => $breakdown['app_commission'],
                'driver_earnings' => $breakdown['driver_earnings'],
                'payment_method' => 'wallet',
                'payment_status' => 'completed',
                'paid_at' => now(),
            ]);

            \Illuminate\Support\Facades\DB::commit();

            TripStatusUpdated::dispatch($trip);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إنهاء الرحلة ودفع القيمة بنجاح',
                'data' => [
                    'trip' => new TripResource($trip),
                    'financials' => $breakdown
                ]
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }
}
