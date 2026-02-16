<?php

namespace Modules\Wallets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Modules\Wallets\Enums\TransactionType;
use Modules\Wallets\Enums\TransactionStatus;

class Wallet extends Model
{
    use HasFactory, SoftDeletes;

    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_INACTIVE,
        self::STATUS_SUSPENDED,
    ];

    protected $table = 'wallets';

    protected $fillable = [
        'customer_id',
        'wallet_number',
        'balance',
        'locked_amount',
        'currency',
        'status',
        'description',
        'suspended_at',
        'suspended_reason',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'locked_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    protected $attributes = [
        'balance' => 0,
        'locked_amount' => 0,
        'currency' => 'USD',
        'status' => self::STATUS_ACTIVE,
    ];

    /**
     * Get the customer that owns the wallet
     */
    public function customer()
    {
        return $this->belongsTo(\Modules\Customer\Models\Customer::class);
    }

    /**
     * Get all transactions for this wallet
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get incoming transfer transactions
     */
    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'related_wallet_id')
            ->where('type', TransactionType::TRANSFER_OUT->value);
    }

    /**
     * Get the available balance
     */
    public function getAvailableBalanceAttribute(): float
    {
        return (float) ($this->balance - $this->locked_amount);
    }

    /**
     * Check if wallet is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if wallet is inactive
     */
    public function isInactive(): bool
    {
        return $this->status === self::STATUS_INACTIVE;
    }

    /**
     * Check if wallet is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /**
     * Check if wallet can perform transactions
     */
    public function canTransact(): bool
    {
        return $this->isActive();
    }

    /**
     * Activate the wallet
     */
    public function activate(): bool
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'suspended_at' => null,
            'suspended_reason' => null,
        ]);
        return true;
    }

    /**
     * Deactivate the wallet (permanent)
     */
    public function deactivate(): bool
    {
        $this->update([
            'status' => self::STATUS_INACTIVE,
        ]);
        return true;
    }

    /**
     * Suspend the wallet (temporary)
     */
    public function suspend(?string $reason = null): bool
    {
        $this->update([
            'status' => self::STATUS_SUSPENDED,
            'suspended_at' => now(),
            'suspended_reason' => $reason,
        ]);
        return true;
    }

    /**
     * Unsuspend the wallet (reactivate from suspended)
     */
    public function unsuspend(): bool
    {
        if (!$this->isSuspended()) {
            return false;
        }
        return $this->activate();
    }

    // ==================== SCOPES ====================

    /**
     * Scope to filter by active status
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter by inactive status
     */
    public function scopeInactive($query)
    {
        return $query->where('status', self::STATUS_INACTIVE);
    }

    /**
     * Scope to filter by suspended status
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', self::STATUS_SUSPENDED);
    }

    /**
     * Scope to filter by customer
     */
    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Scope to filter wallets that can transact
     */
    public function scopeCanTransact($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // ==================== BALANCE OPERATIONS ====================

    /**
     * Deposit money into wallet
     */
    public function deposit(
        float $amount,
        ?string $description = null,
        ?string $externalReference = null,
        ?string $paymentMethod = null,
        array $metadata = []
    ): Transaction {
        if (!$this->canTransact()) {
            throw new \Exception('Wallet cannot perform transactions in current status.');
        }

        return DB::transaction(function () use ($amount, $description, $externalReference, $paymentMethod, $metadata) {
            $balanceBefore = (float) $this->balance;
            $balanceAfter = $balanceBefore + $amount;

            $transaction = $this->transactions()->create([
                'type' => TransactionType::DEPOSIT,
                'status' => TransactionStatus::COMPLETED,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'currency' => $this->currency,
                'description' => $description ?? 'Deposit',
                'external_reference' => $externalReference,
                'payment_method' => $paymentMethod,
                'metadata' => $metadata,
                'completed_at' => now(),
            ]);

            $this->update(['balance' => $balanceAfter]);

            return $transaction;
        });
    }

    /**
     * Withdraw money from wallet
     */
    public function withdraw(
        float $amount,
        ?string $description = null,
        ?string $externalReference = null,
        array $metadata = []
    ): Transaction {
        if (!$this->canTransact()) {
            throw new \Exception('Wallet cannot perform transactions in current status.');
        }

        if ($this->available_balance < $amount) {
            throw new \Exception('Insufficient balance.');
        }

        return DB::transaction(function () use ($amount, $description, $externalReference, $metadata) {
            $balanceBefore = (float) $this->balance;
            $balanceAfter = $balanceBefore - $amount;

            $transaction = $this->transactions()->create([
                'type' => TransactionType::WITHDRAWAL,
                'status' => TransactionStatus::COMPLETED,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'currency' => $this->currency,
                'description' => $description ?? 'Withdrawal',
                'external_reference' => $externalReference,
                'metadata' => $metadata,
                'completed_at' => now(),
            ]);

            $this->update(['balance' => $balanceAfter]);

            return $transaction;
        });
    }

    /**
     * Transfer money to another wallet
     */
    public function transferTo(
        Wallet $destinationWallet,
        float $amount,
        ?string $description = null,
        array $metadata = []
    ): array {
        if (!$this->canTransact()) {
            throw new \Exception('Source wallet cannot perform transactions in current status.');
        }

        if (!$destinationWallet->canTransact()) {
            throw new \Exception('Destination wallet cannot perform transactions in current status.');
        }

        if ($this->available_balance < $amount) {
            throw new \Exception('Insufficient balance.');
        }

        if ($this->id === $destinationWallet->id) {
            throw new \Exception('Cannot transfer to the same wallet.');
        }

        return DB::transaction(function () use ($destinationWallet, $amount, $description, $metadata) {
            // Debit from source
            $sourceBalanceBefore = (float) $this->balance;
            $sourceBalanceAfter = $sourceBalanceBefore - $amount;

            $outTransaction = $this->transactions()->create([
                'type' => TransactionType::TRANSFER_OUT,
                'status' => TransactionStatus::COMPLETED,
                'amount' => $amount,
                'balance_before' => $sourceBalanceBefore,
                'balance_after' => $sourceBalanceAfter,
                'currency' => $this->currency,
                'related_wallet_id' => $destinationWallet->id,
                'description' => $description ?? "Transfer to {$destinationWallet->wallet_number}",
                'metadata' => $metadata,
                'completed_at' => now(),
            ]);

            $this->update(['balance' => $sourceBalanceAfter]);

            // Credit to destination
            $destBalanceBefore = (float) $destinationWallet->balance;
            $destBalanceAfter = $destBalanceBefore + $amount;

            $inTransaction = $destinationWallet->transactions()->create([
                'type' => TransactionType::TRANSFER_IN,
                'status' => TransactionStatus::COMPLETED,
                'amount' => $amount,
                'balance_before' => $destBalanceBefore,
                'balance_after' => $destBalanceAfter,
                'currency' => $destinationWallet->currency,
                'related_wallet_id' => $this->id,
                'description' => $description ?? "Transfer from {$this->wallet_number}",
                'metadata' => $metadata,
                'completed_at' => now(),
            ]);

            $destinationWallet->update(['balance' => $destBalanceAfter]);

            return [
                'out' => $outTransaction,
                'in' => $inTransaction,
            ];
        });
    }

    /**
     * Make a payment from wallet
     */
    public function pay(
        float $amount,
        ?string $description = null,
        ?string $externalReference = null,
        array $metadata = []
    ): Transaction {
        if (!$this->canTransact()) {
            throw new \Exception('Wallet cannot perform transactions in current status.');
        }

        if ($this->available_balance < $amount) {
            throw new \Exception('Insufficient balance.');
        }

        return DB::transaction(function () use ($amount, $description, $externalReference, $metadata) {
            $balanceBefore = (float) $this->balance;
            $balanceAfter = $balanceBefore - $amount;

            $transaction = $this->transactions()->create([
                'type' => TransactionType::PAYMENT,
                'status' => TransactionStatus::COMPLETED,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'currency' => $this->currency,
                'description' => $description ?? 'Payment',
                'external_reference' => $externalReference,
                'metadata' => $metadata,
                'completed_at' => now(),
            ]);

            $this->update(['balance' => $balanceAfter]);

            return $transaction;
        });
    }

    /**
     * Process a refund to wallet
     */
    public function refund(
        float $amount,
        ?string $description = null,
        ?string $externalReference = null,
        array $metadata = []
    ): Transaction {
        if (!$this->canTransact()) {
            throw new \Exception('Wallet cannot perform transactions in current status.');
        }

        return DB::transaction(function () use ($amount, $description, $externalReference, $metadata) {
            $balanceBefore = (float) $this->balance;
            $balanceAfter = $balanceBefore + $amount;

            $transaction = $this->transactions()->create([
                'type' => TransactionType::REFUND,
                'status' => TransactionStatus::COMPLETED,
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'currency' => $this->currency,
                'description' => $description ?? 'Refund',
                'external_reference' => $externalReference,
                'metadata' => $metadata,
                'completed_at' => now(),
            ]);

            $this->update(['balance' => $balanceAfter]);

            return $transaction;
        });
    }

    /**
     * Lock amount in wallet
     */
    public function lockAmount(float $amount): bool
    {
        if (!$this->canTransact()) {
            throw new \Exception('Wallet cannot perform transactions in current status.');
        }
        if ($this->available_balance >= $amount) {
            $this->increment('locked_amount', $amount);
            return true;
        }
        return false;
    }

    /**
     * Unlock amount in wallet
     */
    public function unlockAmount(float $amount): bool
    {
        if ($this->locked_amount >= $amount) {
            $this->decrement('locked_amount', $amount);
            return true;
        }
        return false;
    }

    /**
     * Get total credits for this wallet
     */
    public function getTotalCredits(): float
    {
        return (float) $this->transactions()
            ->completed()
            ->credits()
            ->sum('amount');
    }

    /**
     * Get total debits for this wallet
     */
    public function getTotalDebits(): float
    {
        return (float) $this->transactions()
            ->completed()
            ->debits()
            ->sum('amount');
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE => 'success',
            self::STATUS_INACTIVE => 'secondary',
            self::STATUS_SUSPENDED => 'warning',
            default => 'secondary',
        };
    }
}
