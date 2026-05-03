<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    /**
     * Get user wallet balance
     */
    public function balance(Request $request)
    {
        $user = $request->user();
        
        $wallet = $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'currency' => 'YER']
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'balance' => $wallet->balance,
                'currency' => $wallet->currency,
            ]
        ]);
    }

    /**
     * Deposit funds (Simulated for now)
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        $user = $request->user();
        $amount = $request->amount;

        try {
            DB::beginTransaction();

            $wallet = $user->wallet()->firstOrCreate(
                ['user_id' => $user->id],
                ['balance' => 0.00, 'currency' => 'YER']
            );

            $wallet->balance += $amount;
            $wallet->save();

            WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'amount' => $amount,
                'type' => 'credit',
                'reference_id' => 'DEP-' . uniqid(),
                'status' => 'completed',
                'description' => 'إيداع رصيد في المحفظة',
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم الإيداع بنجاح',
                'data' => [
                    'balance' => $wallet->balance,
                    'currency' => $wallet->currency,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء عملية الإيداع: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet transactions history
     */
    public function transactions(Request $request)
    {
        $user = $request->user();
        
        $wallet = $user->wallet()->firstOrCreate(
            ['user_id' => $user->id],
            ['balance' => 0.00, 'currency' => 'YER']
        );

        $transactions = $wallet->transactions()->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $transactions
        ]);
    }
}
