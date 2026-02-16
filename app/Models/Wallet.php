<?php

namespace Modules\Wallets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wallets';

    protected $fillable = [
        'wallet_id',
        'customer_id',
        'wallet_number',
        'balance',
        'locked_amount',
        'currency',
        'status',
        'description',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'locked_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    protected $attributes = [
        'balance' => 0,
        'locked_amount' => 0,
        'currency' => 'USD',
        'status' => 'active',
    ];

    /**
     * Get the customer that owns the wallet
     */
    public function customer()
    {
        return $this->belongsTo(\Modules\Customer\Models\Customer::class);
    }

    /**
     * Get the available balance
     */
    public function getAvailableBalanceAttribute()
    {
        return $this->balance - $this->locked_amount;
    }

    /**
     * Scope to filter by status
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Add money to wallet
     */
    public function addBalance($amount)
    {
        $this->increment('balance', $amount);
        return $this;
    }

    /**
     * Deduct money from wallet
     */
    public function deductBalance($amount)
    {
        if ($this->getAvailableBalanceAttribute() >= $amount) {
            $this->decrement('balance', $amount);
            return true;
        }
        return false;
    }

    /**
     * Lock amount in wallet
     */
    public function lockAmount($amount)
    {
        if ($this->getAvailableBalanceAttribute() >= $amount) {
            $this->increment('locked_amount', $amount);
            return true;
        }
        return false;
    }

    /**
     * Unlock amount in wallet
     */
    public function unlockAmount($amount)
    {
        if ($this->locked_amount >= $amount) {
            $this->decrement('locked_amount', $amount);
            return true;
        }
        return false;
    }
}
