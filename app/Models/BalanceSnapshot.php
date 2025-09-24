<?php

/**
 * BalanceSnapshot Model
 * 
 * Model for managing balance snapshots for efficient
 * balance tracking in large-scale scenarios.
 * 
 * @author Fahed
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalanceSnapshot extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * Author: Fahed
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'balance',
        'snapshot_date',
    ];

    /**
     * The attributes that should be cast.
     * Author: Fahed
     *
     * @var array<string, string>
     */
    protected $casts = [
        'balance' => 'decimal:2',
        'snapshot_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the balance snapshot.
     * Author: Fahed
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get the latest snapshot for a user.
     * Author: Fahed
     */
    public function scopeLatestForUser($query, int $userId)
    {
        return $query->where('user_id', $userId)
                    ->orderBy('snapshot_date', 'desc')
                    ->limit(1);
    }

    /**
     * Scope to get snapshots within a date range.
     * Author: Fahed
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('snapshot_date', [$startDate, $endDate]);
    }
}