<?php

/**
 * TransactionController
 *
 * Controller for handling wallet transactions with proper validation
 * and API resource formatting
 *
 * @author Fahed
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserResource;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Requests\TestRequest;

class TransactionController extends Controller
{
    /**
     * TransactionService instance
     * Author: Fahed
     */
    protected $transactionService;

    /**
     * Constructor
     * Author: Fahed
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Get all transactions for authenticated user with pagination
     * Returns current balance along with transactions
     * Author: Fahed
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            // Get paginated transactions for the user
            $transactions = Transaction::where('sender_id', $user->id)
                ->orWhere('receiver_id', $user->id)
                ->with(['sender', 'receiver'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'message' => 'Transactions retrieved successfully',
                'data' => [
                    'user' => new UserResource($user),
                    'current_balance' => number_format($user->balance, 2),
                    'transactions' => TransactionResource::collection($transactions),
                    'pagination' => [
                        'current_page' => $transactions->currentPage(),
                        'last_page' => $transactions->lastPage(),
                        'per_page' => $transactions->perPage(),
                        'total' => $transactions->total(),
                        'from' => $transactions->firstItem(),
                        'to' => $transactions->lastItem(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transactions',
                'error' => $e->getMessage(),
                'error_code' => 'TRANSACTIONS_RETRIEVAL_FAILED',
            ], 500);
        }
    }

    /**
     * Create a new transaction
     * Uses FormRequest for validation and TransactionService for processing
     * Author: Fahed
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        try {
            $sender = $request->user();
            $receiver = User::where('email', $request->receiver_email)->first();

            // Use TransactionService to process the transaction
            $result = $this->transactionService->processTransaction(
                $sender->id,
                $receiver->id,
                $request->amount,
                $request->commission_fee
            );

            return response()->json([
                'success' => true,
                'message' => 'Transaction completed successfully',
                'data' => [
                    'transaction' => new TransactionResource($result['transaction']),
                    'sender_balance' => number_format($result['sender_balance'], 2),
                    'receiver_balance' => number_format($result['receiver_balance'], 2),
                ],
            ], 201);

        } catch (\Exception $e) {
            $statusCode = 400;
            $errorCode = 'TRANSACTION_FAILED';

            // Determine specific error type and status code
            if (str_contains($e->getMessage(), 'Insufficient balance')) {
                $statusCode = 422;
                $errorCode = 'INSUFFICIENT_BALANCE';
            } elseif (str_contains($e->getMessage(), 'Cannot transfer to yourself')) {
                $statusCode = 422;
                $errorCode = 'INVALID_RECEIVER';
            } elseif (str_contains($e->getMessage(), 'Cannot transfer to unverified account')) {
                $statusCode = 422;
                $errorCode = 'UNVERIFIED_ACCOUNT';
            }

            return response()->json([
                'success' => false,
                'message' => 'Transaction failed',
                'error' => $e->getMessage(),
                'error_code' => $errorCode,
            ], $statusCode);
        }
    }

    /**
     * Get transaction details
     * Author: Fahed
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();

            $transaction = Transaction::where('id', $id)
                ->where(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                        ->orWhere('receiver_id', $user->id);
                })
                ->with(['sender', 'receiver'])
                ->first();

            if (! $transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction retrieved successfully',
                'data' => new TransactionResource($transaction),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve transaction',
                'error' => $e->getMessage(),
                'error_code' => 'TRANSACTION_RETRIEVAL_FAILED',
            ], 500);
        }
    }

    /**
     * Get user transaction statistics
     * Author: Fahed
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $user = $request->user();

            $stats = $this->transactionService->getUserTransactionStats($user->id);

            return response()->json([
                'success' => true,
                'message' => 'Statistics retrieved successfully',
                'data' => [
                    'user' => new UserResource($user),
                    'statistics' => [
                        'total_sent' => number_format($stats['total_sent'], 2),
                        'total_received' => number_format($stats['total_received'], 2),
                        'total_commission_paid' => number_format($stats['total_commission_paid'], 2),
                        'sent_count' => $stats['sent_count'],
                        'received_count' => $stats['received_count'],
                        'total_transactions' => $stats['total_transactions'],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics',
                'error' => $e->getMessage(),
                'error_code' => 'STATISTICS_RETRIEVAL_FAILED',
            ], 500);
        }
    }
}
