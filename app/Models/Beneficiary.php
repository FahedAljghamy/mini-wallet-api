<?php

/**
 * Beneficiary Model
 *
 * Model for managing user's saved beneficiaries for quick transfers.
 *
 * @author Fahed
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Beneficiary extends Model
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
        'beneficiary_user_id',
        'nickname',
        'notes',
        'is_favorite',
    ];

    /**
     * The attributes that should be cast.
     * Author: Fahed
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_favorite' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who owns this beneficiary.
     * Author: Fahed
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the beneficiary user details.
     * Author: Fahed
     */
    public function beneficiaryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_user_id');
    }

    /**
     * Scope to get favorites only.
     * Author: Fahed
     */
    public function scopeFavorites($query)
    {
        return $query->where('is_favorite', true);
    }

    /**
     * Scope to search by nickname or beneficiary name/email.
     * Author: Fahed
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('nickname', 'like', "%{$search}%")
              ->orWhereHas('beneficiaryUser', function ($userQuery) use ($search) {
                  $userQuery->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%");
              });
        });
    }
}
