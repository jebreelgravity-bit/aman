<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\CouponUsage;

class CouponController extends Controller
{
    public function applyCoupon(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string',
            'trip_amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $validated['code'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')
                      ->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', now());
            })
            ->first();

        if (!$coupon) {
            return response()->json([
                'status' => 'error',
                'message' => 'الكوبون غير صالح أو منتهي الصلاحية',
            ], 400);
        }

        // Check target role
        if ($coupon->target_role && $coupon->target_role !== 'all' && $request->user()->role !== $coupon->target_role) {
            return response()->json([
                'status' => 'error',
                'message' => 'هذا الكوبون غير مخصص لحسابك',
            ], 400);
        }

        // Check usage limits
        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            return response()->json([
                'status' => 'error',
                'message' => 'تم استنفاد الحد الأقصى لاستخدام هذا الكوبون',
            ], 400);
        }

        // Calculate discount
        $discountAmount = 0;
        if ($coupon->type === 'percentage') {
            $discountAmount = ($validated['trip_amount'] * $coupon->value) / 100;
        } else {
            $discountAmount = $coupon->value;
        }

        // Apply max discount limit if set
        if ($coupon->max_discount_amount && $discountAmount > $coupon->max_discount_amount) {
            $discountAmount = $coupon->max_discount_amount;
        }

        // Ensure discount doesn't exceed total trip amount
        if ($discountAmount > $validated['trip_amount']) {
            $discountAmount = $validated['trip_amount'];
        }

        return response()->json([
            'status' => 'success',
            'message' => 'تم تطبيق الكوبون بنجاح',
            'data' => [
                'coupon_id' => $coupon->id,
                'code' => $coupon->code,
                'discount_amount' => $discountAmount,
                'final_amount' => $validated['trip_amount'] - $discountAmount,
            ]
        ]);
    }
}
