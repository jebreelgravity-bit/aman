<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\UserSubscription;
use App\Http\Resources\SubscriptionResource;

class SubscriptionController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)->get();

        return response()->json([
            'status' => 'success',
            'data' => SubscriptionResource::collection($plans)
        ]);
    }

    public function subscribe(Request $request, $planId)
    {
        $validated = $request->validate([
            'document_path' => 'nullable|string', // Should actually be file upload, but keeping simple for API base
        ]);

        $plan = SubscriptionPlan::findOrFail($planId);

        if (!$plan->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'هذه الباقة غير متاحة حالياً',
            ], 400);
        }

        // Check if already has active subscription
        $activeSub = UserSubscription::where('user_id', $request->user()->id)
            ->where('status', 'active')
            ->where('end_date', '>', now())
            ->first();

        if ($activeSub) {
            return response()->json([
                'status' => 'error',
                'message' => 'لديك اشتراك فعال مسبقاً',
            ], 400);
        }

        $subscription = UserSubscription::create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'start_date' => now(),
            'end_date' => now()->addDays($plan->duration_days),
            'status' => 'pending', // Requires admin approval for students/employees
            'document_path' => $validated['document_path'] ?? null,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'تم طلب الاشتراك بنجاح، بانتظار التحقق من الوثائق',
            'data' => [
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
                'end_date' => $subscription->end_date,
            ]
        ]);
    }
}
