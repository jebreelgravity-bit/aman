<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Rating;
use App\Http\Resources\RatingResource;

class RatingController extends Controller
{
    public function rateTrip(Request $request, $tripId)
    {
        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $trip = Trip::findOrFail($tripId);

        // Ensure user is the customer of this trip and trip is completed
        if ($trip->customer_id !== $request->user()->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'غير مصرح لك بتقييم هذه الرحلة',
            ], 403);
        }

        if ($trip->status !== 'completed') {
            return response()->json([
                'status' => 'error',
                'message' => 'لا يمكن تقييم إلا الرحلات المكتملة',
            ], 400);
        }

        // Check if already rated
        if (Rating::where('trip_id', $trip->id)->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'تم تقييم هذه الرحلة مسبقاً',
            ], 400);
        }

        $rating = Rating::create([
            'trip_id' => $trip->id,
            'driver_id' => $trip->driver_id,
            'customer_id' => $request->user()->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم إرسال التقييم بنجاح',
            'data' => [
                'rating' => new RatingResource($rating)
            ]
        ]);
    }
}
