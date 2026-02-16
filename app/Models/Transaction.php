<?php

namespace Modules\Wallets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Modules\Wallets\Enums\TransactionType;
use Modules\Wallets\Enums\TransactionStatus;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wallet_transactions';

    protected $fillable = [
        'reference',
        'wallet_id',
        'related_wallet_id',
        'type',
        'status',
        'amount',
        'fee',
        'balance_before',
        'balance_after',
        'currency',
        'description',
        'metadata',
        'external_reference',
        'payment_method',
        'processed_at',
        'completed_at',
        'failed_at',
        'failure_reason',
        'reversed_transaction_id',
        'reversed_at',
    ];

    protected $casts = [
        'type' => TransactionType::class,
        'status' => TransactionStatus::class,
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'reversed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => TransactionStatus::PENDING,
        'fee' => 0,
        'currency' => 'USD',
    ];

    // ==================== RELATIONSHIPS ====================

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function relatedWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'related_wallet_id');
    }

    public function reversedTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reversed_transaction_id');
    }

    public function reversalTransaction(): HasOne
    {
        return $this->hasOne(self::class, 'reversed_transaction_id');
    }

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $transaction) {
            if (empty($transaction->reference)) {
                $transaction->reference = self::generateReference();
            }
        });
    }

    // ==================== ACCESSORS ====================

    public function getNetAmountAttribute(): float
    {
        return (float) ($this->amount - $this->fee);
    }

    public function getIsReversedAttribute(): bool
    {
        return $this->reversed_at !== null;
    }

    public function getIsCreditAttribute(): bool
    {
        return $this->type->isCredit();
    }

    public function getIsDebitAttribute(): bool
    {
        return $this->type->isDebit();
    }

    public function getIsFinalAttribute(): bool
    {
        return $this->status->isFinal();
    }

    public function getSignedAmountAttribute(): float
    {
        return $this->is_credit ? (float) $this->amount : -((float) $this->amount);
    }

    // ==================== SCOPES ====================

    public function scopeByType($query, TransactionType|string $type)
    {
        $typeValue = $type instanceof TransactionType ? $type->value : $type;
        return $query->where('type', $typeValue);
    }

    public function scopeByStatus($query, TransactionStatus|string $status)
    {
        $statusValue = $status instanceof TransactionStatus ? $status->value : $status;
        return $query->where('status', $statusValue);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', TransactionStatus::COMPLETED);
    }

    public function scopePending($query)
    {
        return $query->where('status', TransactionStatus::PENDING);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', TransactionStatus::FAILED);
    }

    public function scopeCredits($query)
    {
        return $query->whereIn('type', [
            TransactionType::DEPOSIT->value,
            TransactionType::TRANSFER_IN->value,
            TransactionType::REFUND->value,
        ]);
    }

    public function scopeDebits($query)
    {
        return $query->whereIn('type', [
            TransactionType::WITHDRAWAL->value,
            TransactionType::TRANSFER_OUT->value,
            TransactionType::PAYMENT->value,
            TransactionType::FEE->value,
        ]);
    }

    public function scopeNotReversed($query)
    {
        return $query->whereNull('reversed_at');
    }

    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ==================== STATUS METHODS ====================

    public function markAsProcessing(): bool
    {
        if ($this->status !== TransactionStatus::PENDING) {
            return false;
        }

        return $this->update([
            'status' => TransactionStatus::PROCESSING,
            'processed_at' => now(),
        ]);
    }

    public function markAsCompleted(): bool
    {
        if (!in_array($this->status, [TransactionStatus::PENDING, TransactionStatus::PROCESSING])) {
            return false;
        }

        return $this->update([
            'status' => TransactionStatus::COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function markAsFailed(string $reason = null): bool
    {
        if ($this->status->isFinal()) {
            return false;
        }

        return $this->update([
            'status' => TransactionStatus::FAILED,
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    public function cancel(): bool
    {
        if (!$this->status->canCancel()) {
            return false;
        }

        return $this->update([
            'status' => TransactionStatus::CANCELLED,
        ]);
    }

    public function reverse(?string $description = null): ?self
    {
        if (!$this->status->canReverse() || $this->is_reversed) {
            return null;
        }

        $wallet = $this->wallet;

        // Create reversal transaction
        $reversalType = $this->is_credit
            ? TransactionType::WITHDRAWAL
            : TransactionType::DEPOSIT;

        $balanceBefore = (float) $wallet->balance;
        $newBalance = $this->is_credit
            ? $balanceBefore - $this->amount
            : $balanceBefore + $this->amount;

        $reversal = self::create([
            'wallet_id' => $this->wallet_id,
            'related_wallet_id' => $this->related_wallet_id,
            'type' => $reversalType,
            'status' => TransactionStatus::COMPLETED,
            'amount' => $this->amount,
            'fee' => 0,
            'balance_before' => $balanceBefore,
            'balance_after' => $newBalance,
            'currency' => $this->currency,
            'description' => $description ?? "Reversal of {$this->reference}",
            'reversed_transaction_id' => $this->id,
            'completed_at' => now(),
        ]);

        // Update wallet balance
        $wallet->update(['balance' => $newBalance]);

        // Mark original as reversed
        $this->update([
            'status' => TransactionStatus::REVERSED,
            'reversed_at' => now(),
        ]);

        return $reversal;
    }

    // ==================== HELPERS ====================

    public static function generateReference(): string
    {
        do {
            $reference = 'TXN-' . strtoupper(Str::random(12));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }
}
