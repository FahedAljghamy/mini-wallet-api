<?php

/**
 * TransactionLock Model
 * 
 * Model for managing transaction locks to prevent
 * race conditions in high-concurrency scenarios.
 * 
 * @author Fahed
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TransactionLock extends Model
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
        'lock_type',
        'locked_at',
        'expires_at',
        'lock_id',
    ];

    /**
     * The attributes that should be cast.
     * Author: Fahed
     *
     * @var array<string, string>
     */
    protected $casts = [
        'locked_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the lock.
     * Author: Fahed
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Create a new lock for a user.
     * Author: Fahed
     */
    public static function createLock(int $userId, string $lockType = 'balance', int $durationSeconds = 30): self
    {
        // Clean up expired locks first
        self::cleanupExpiredLocks();

        // Check if user already has an active lock
        $existingLock = self::where('user_id', $userId)
                          ->where('lock_type', $lockType)
                          ->where('expires_at', '>', now())
                          ->first();

        if ($existingLock) {
            throw new \Exception("User {$userId} already has an active {$lockType} lock");
        }

        return self::create([
            'user_id' => $userId,
            'lock_type' => $lockType,
            'locked_at' => now(),
            'expires_at' => now()->addSeconds($durationSeconds),
            'lock_id' => Str::uuid(),
        ]);
    }

    /**
     * Release a lock by lock ID.
     * Author: Fahed
     */
    public static function releaseLock(string $lockId): bool
    {
        return self::where('lock_id', $lockId)->delete() > 0;
    }

    /**
     * Clean up expired locks.
     * Author: Fahed
     */
    public static function cleanupExpiredLocks(): int
    {
        return self::where('expires_at', '<', now())->delete();
    }

    /**
     * Check if a user has an active lock.
     * Author: Fahed
     */
    public static function hasActiveLock(int $userId, string $lockType = 'balance'): bool
    {
        return self::where('user_id', $userId)
                  ->where('lock_type', $lockType)
                  ->where('expires_at', '>', now())
                  ->exists();
    }

    /**
     * Scope to get active locks.
     * Author: Fahed
     */
    public function scopeActive($query)
    {
        return $query->where('expires_at', '>', now());
    }

    /**
     * Scope to get locks for a specific user.
     * Author: Fahed
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}