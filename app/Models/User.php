<?php

/**
 * Author: Eng.Fahed
 * User model for wallet application
 */

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'balance' => 'decimal:2',
    ];

    /**
     * Get transactions where user is the sender
     * Author: Eng.Fahed
     */
    public function sentTransactions()
    {
        return $this->hasMany(Transaction::class, 'sender_id');
    }

    /**
     * Get transactions where user is the receiver
     * Author: Eng.Fahed
     */
    public function receivedTransactions()
    {
        return $this->hasMany(Transaction::class, 'receiver_id');
    }

    /**
     * Get all transactions for the user (both sent and received)
     * Author: Eng.Fahed
     */
    public function transactions()
    {
        return $this->sentTransactions()->union($this->receivedTransactions());
    }

    /**
     * Get user's saved beneficiaries
     * Author: Fahed
     */
    public function beneficiaries()
    {
        return $this->hasMany(Beneficiary::class, 'user_id');
    }

    /**
     * Get beneficiaries where this user is the beneficiary
     * Author: Fahed
     */
    public function beneficiaryOf()
    {
        return $this->hasMany(Beneficiary::class, 'beneficiary_user_id');
    }
}
