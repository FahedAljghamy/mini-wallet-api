<?php

/**
 * Author: Eng.Fahed
 * Transaction model for wallet transfers
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Author: Eng.Fahed
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'amount',
        'commission_fee',
        'status',
    ];

    /**
     * The attributes that should be cast.
     * Author: Eng.Fahed
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'commission_fee' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the sender user
     * Author: Eng.Fahed
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the receiver user
     * Author: Eng.Fahed
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope for completed transactions
     * Author: Eng.Fahed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for pending transactions
     * Author: Eng.Fahed
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for failed transactions
     * Author: Eng.Fahed
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
