<?php

/**
 * TransactionService
 *
 * Service class for handling wallet transactions with proper validation,
 * database locking, and event firing.
 *
 * @author Fahed
 */

namespace App\Services;

use App\Events\TransactionCreated;
use App\Models\BalanceSnapshot;
use App\Models\Transaction;
use App\Models\TransactionLock;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    /**
     * Default commission fee percentage
     *
     * @author Fahed
     *
     * @var float
     */
    private const DEFAULT_COMMISSION_RATE = 0.015; // 1.5%

    /**
     * Process a wallet transaction between two users with high concurrency support
     *
     * This method handles the complete transaction flow including validation,
     * database locking, balance updates, and event firing with support for
     * high-concurrency scenarios.
     *
     * @author Fahed
     *
     * @param  int  $senderId  The ID of the user sending money
     * @param  int  $receiverId  The ID of the user receiving money
     * @param  float  $amount  The amount to transfer
     * @param  float|null  $commissionFee  Optional custom commission fee
     * @return array Transaction result with success status and data
     *
     * @throws Exception If transaction fails
     */
    public function processTransaction(
        int $senderId,
        int $receiverId,
        float $amount,
        ?float $commissionFee = null
    ): array {
        // Validate input parameters
        $this->validateInput($senderId, $receiverId, $amount);

        // Calculate commission fee if not provided
        $commissionFee = $commissionFee ?? ($amount * self::DEFAULT_COMMISSION_RATE);
        $totalAmount = $amount + $commissionFee;

        try {
            return DB::transaction(function () use ($senderId, $receiverId, $amount, $commissionFee, $totalAmount) {
                // Use high-concurrency locking mechanism
                $lockId = $this->acquireTransactionLock($senderId, $receiverId);

                try {
                    // Lock and fetch both users for update with row-level locking
                    $sender = User::lockForUpdate()->findOrFail($senderId);
                    $receiver = User::lockForUpdate()->findOrFail($receiverId);

                    // Validate business rules
                    $this->validateTransaction($sender, $receiver, $totalAmount);

                    // Create transaction record
                    $transaction = Transaction::create([
                        'sender_id' => $senderId,
                        'receiver_id' => $receiverId,
                        'amount' => $amount,
                        'commission_fee' => $commissionFee,
                        'status' => 'pending',
                    ]);

                    // Update sender balance (deduct total amount)
                    $sender->decrement('balance', $totalAmount);

                    // Update receiver balance (add transfer amount only)
                    $receiver->increment('balance', $amount);

                    // Update transaction status to completed
                    $transaction->update(['status' => 'completed']);

                    // Create balance snapshot for audit trail
                    $this->createBalanceSnapshot($senderId, $sender->fresh()->balance);
                    $this->createBalanceSnapshot($receiverId, $receiver->fresh()->balance);

                    // Fire TransactionCreated event
                    event(new TransactionCreated($transaction->load(['sender', 'receiver'])));

                    // Log successful transaction
                    Log::info('Transaction completed successfully', [
                        'transaction_id' => $transaction->id,
                        'sender_id' => $senderId,
                        'receiver_id' => $receiverId,
                        'amount' => $amount,
                        'commission_fee' => $commissionFee,
                        'lock_id' => $lockId,
                    ]);

                    return [
                        'success' => true,
                        'transaction' => $transaction->load(['sender', 'receiver']),
                        'sender_balance' => $sender->fresh()->balance,
                        'receiver_balance' => $receiver->fresh()->balance,
                    ];
                } finally {
                    // Always release the lock
                    $this->releaseTransactionLock($lockId);
                }
            });
        } catch (Exception $e) {
            // Log transaction failure
            Log::error('Transaction failed', [
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Validate input parameters for transaction
     *
     * @author Fahed
     *
     * @throws Exception If validation fails
     */
    private function validateInput(int $senderId, int $receiverId, float $amount): void
    {
        if ($senderId <= 0) {
            throw new Exception('Invalid sender ID');
        }

        if ($receiverId <= 0) {
            throw new Exception('Invalid receiver ID');
        }

        if ($amount <= 0) {
            throw new Exception('Transfer amount must be greater than zero');
        }

        if ($amount > 100000) {
            throw new Exception('Transfer amount exceeds maximum limit');
        }
    }

    /**
     * Validate transaction business rules
     *
     * @author Fahed
     *
     * @throws Exception If validation fails
     */
    private function validateTransaction(User $sender, User $receiver, float $totalAmount): void
    {
        // Check if sender and receiver are different
        if ($sender->id === $receiver->id) {
            throw new Exception('Cannot transfer money to yourself');
        }

        // Check if sender has sufficient balance with detailed error messages
        if ($sender->balance < $totalAmount) {
            $amount = $totalAmount - ($totalAmount * 0.015 / 1.015); // Calculate original amount
            $commissionFee = $totalAmount - $amount;

            if ($sender->balance < $amount) {
                throw new Exception('Insufficient balance. You have $' . number_format($sender->balance, 2) .
                    ' but trying to transfer $' . number_format($amount, 2));
            } else {
                throw new Exception('Low balance. You have $' . number_format($sender->balance, 2) .
                    ' but need $' . number_format($totalAmount, 2) .
                    ' (amount + commission fee of $' . number_format($commissionFee, 2) . ')');
            }
        }

        // Check if receiver account is active (you can add more business rules here)
        if (! $receiver->email_verified_at) {
            throw new Exception('Cannot transfer to unverified account');
        }
    }

    /**
     * Get transaction history for a user
     *
     * @author Fahed
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserTransactions(int $userId, int $limit = 50)
    {
        return Transaction::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->with(['sender', 'receiver'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get transaction statistics for a user
     *
     * @author Fahed
     */
    public function getUserTransactionStats(int $userId): array
    {
        $sentTransactions = Transaction::where('sender_id', $userId)->completed();
        $receivedTransactions = Transaction::where('receiver_id', $userId)->completed();

        return [
            'total_sent' => $sentTransactions->sum('amount'),
            'total_received' => $receivedTransactions->sum('amount'),
            'total_commission_paid' => $sentTransactions->sum('commission_fee'),
            'sent_count' => $sentTransactions->count(),
            'received_count' => $receivedTransactions->count(),
            'total_transactions' => $sentTransactions->count() + $receivedTransactions->count(),
        ];
    }

    /**
     * Cancel a pending transaction
     *
     * @author Fahed
     *
     * @throws Exception
     */
    public function cancelTransaction(int $transactionId, int $userId): bool
    {
        return DB::transaction(function () use ($transactionId, $userId) {
            $transaction = Transaction::where('id', $transactionId)
                ->where('sender_id', $userId)
                ->where('status', 'pending')
                ->first();

            if (! $transaction) {
                throw new Exception('Transaction not found or cannot be cancelled');
            }

            $transaction->update(['status' => 'cancelled']);

            Log::info('Transaction cancelled', [
                'transaction_id' => $transactionId,
                'user_id' => $userId,
            ]);

            return true;
        });
    }

    /**
     * Acquire transaction lock for high concurrency
     *
     * @author Fahed
     */
    private function acquireTransactionLock(int $senderId, int $receiverId): string
    {
        // Try to acquire locks for both users
        $senderLock = TransactionLock::createLock($senderId, 'balance', 30);
        $receiverLock = TransactionLock::createLock($receiverId, 'balance', 30);

        // Return combined lock ID for tracking
        return $senderLock->lock_id . ':' . $receiverLock->lock_id;
    }

    /**
     * Release transaction lock
     *
     * @author Fahed
     */
    private function releaseTransactionLock(string $lockId): void
    {
        $lockIds = explode(':', $lockId);
        foreach ($lockIds as $id) {
            TransactionLock::releaseLock($id);
        }
    }

    /**
     * Create balance snapshot for audit trail
     *
     * @author Fahed
     */
    private function createBalanceSnapshot(int $userId, float $balance): void
    {
        BalanceSnapshot::create([
            'user_id' => $userId,
            'balance' => $balance,
            'snapshot_date' => now(),
        ]);
    }

    /**
     * Get user balance efficiently for large-scale data
     *
     * @author Fahed
     */
    public function getUserBalance(int $userId): float
    {
        // For large-scale data, we can use the latest balance snapshot
        // instead of calculating from all transactions
        $latestSnapshot = BalanceSnapshot::latestForUser($userId)->first();

        if ($latestSnapshot) {
            // Get transactions after the latest snapshot
            $transactionsAfterSnapshot = Transaction::where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                      ->orWhere('receiver_id', $userId);
            })
            ->where('created_at', '>', $latestSnapshot->snapshot_date)
            ->where('status', 'completed')
            ->get();

            $balance = $latestSnapshot->balance;

            foreach ($transactionsAfterSnapshot as $transaction) {
                if ($transaction->sender_id === $userId) {
                    $balance -= ($transaction->amount + $transaction->commission_fee);
                } else {
                    $balance += $transaction->amount;
                }
            }

            return $balance;
        }

        // Fallback to current user balance if no snapshot exists
        return User::findOrFail($userId)->balance;
    }
}
