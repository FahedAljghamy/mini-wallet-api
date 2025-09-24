<?php

/**
 * TransactionCreated Event
 *
 * Event fired when a new transaction is created
 * Broadcasts transaction details to private channels
 *
 * @author Fahed
 */

namespace App\Events;

use App\Models\Transaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TransactionCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The transaction instance
     * Author: Fahed
     */
    public $transaction;

    /**
     * Create a new event instance
     * Author: Fahed
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Get the channels the event should broadcast on
     * Author: Fahed
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('transactions.'.$this->transaction->sender_id),
            new PrivateChannel('transactions.'.$this->transaction->receiver_id),
        ];
    }

    /**
     * Get the data to broadcast
     * Author: Fahed
     */
    public function broadcastWith(): array
    {
        return [
            'transaction' => [
                'id' => $this->transaction->id,
                'amount' => number_format($this->transaction->amount, 2),
                'commission_fee' => number_format($this->transaction->commission_fee, 2),
                'status' => $this->transaction->status,
                'sender' => [
                    'id' => $this->transaction->sender->id,
                    'name' => $this->transaction->sender->name,
                    'email' => $this->transaction->sender->email,
                ],
                'receiver' => [
                    'id' => $this->transaction->receiver->id,
                    'name' => $this->transaction->receiver->name,
                    'email' => $this->transaction->receiver->email,
                ],
                'created_at' => $this->transaction->created_at->format('Y-m-d H:i:s'),
            ],
            'type' => 'transaction_created',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name
     * Author: Fahed
     */
    public function broadcastAs(): string
    {
        return 'transaction.created';
    }
}
