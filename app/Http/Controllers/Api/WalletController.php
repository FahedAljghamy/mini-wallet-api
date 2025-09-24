<?php

/**
 * Author: Eng.Fahed
 * Wallet controller for wallet API
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    /**
     * Get wallet balance
     * Author: Eng.Fahed
     */
    public function balance(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $user->balance,
                'currency' => 'USD',
            ],
        ]);
    }

    /**
     * Add money to wallet (for testing purposes)
     * Author: Eng.Fahed
     */
    public function addMoney(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01|max:10000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
                'error_code' => 'VALIDATION_ERROR',
            ], 422);
        }

        $user = $request->user();
        $user->increment('balance', $request->amount);

        return response()->json([
            'success' => true,
            'message' => 'Money added successfully',
            'data' => [
                'new_balance' => $user->fresh()->balance,
            ],
        ]);
    }

    /**
     * Get wallet statistics
     * Author: Eng.Fahed
     */
    public function statistics(Request $request)
    {
        $user = $request->user();

        $sentTransactions = $user->sentTransactions()->completed()->count();
        $receivedTransactions = $user->receivedTransactions()->completed()->count();
        $totalSent = $user->sentTransactions()->completed()->sum('amount');
        $totalReceived = $user->receivedTransactions()->completed()->sum('amount');
        $totalCommissionPaid = $user->sentTransactions()->completed()->sum('commission_fee');

        return response()->json([
            'success' => true,
            'data' => [
                'current_balance' => $user->balance,
                'total_sent' => $totalSent,
                'total_received' => $totalReceived,
                'total_commission_paid' => $totalCommissionPaid,
                'sent_transactions_count' => $sentTransactions,
                'received_transactions_count' => $receivedTransactions,
                'total_transactions_count' => $sentTransactions + $receivedTransactions,
            ],
        ]);
    }
}
