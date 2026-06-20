<?php

namespace Modules\Wallets\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Wallets\Enums\TopUpStatus;

class TopUp extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'wallet_topups';

    protected $fillable = [
        'reference',
        'wallet_id',
        'customer_id',
        'transaction_id',
        'amount',
        'fee',
        'currency',
        'payment_method',
        'provider',
        'gateway_reference',
        'status',
        'description',
        'metadata',
        'failure_reason',
        'completed_at',
        'failed_at',
        'cancelled_at',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'status' => TopUpStatus::class,
        'metadata' => 'array',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => TopUpStatus::PENDING,
        'fee' => 0,
        'currency' => 'USD',
    ];

    // ==================== RELATIONSHIPS ====================

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(\Modules\Customer\Models\Customer::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    // ==================== BOOT ====================

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $topup) {
            if (empty($topup->reference)) {
                $topup->reference = self::generateReference();
            }
        });
    }

    // ==================== ACCESSORS ====================

    public function getNetAmountAttribute(): float
    {
        return (float) ($this->amount - $this->fee);
    }

    public function getIsFinalAttribute(): bool
    {
        return $this->status->isFinal();
    }

    // ==================== SCOPES ====================

    public function scopePending($query)
    {
        return $query->where('status', TopUpStatus::PENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', TopUpStatus::COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', TopUpStatus::FAILED);
    }

    public function scopeByCustomer($query, $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByWallet($query, $walletId)
    {
        return $query->where('wallet_id', $walletId);
    }

    // ==================== STATE TRANSITIONS ====================

    /**
     * Complete the top-up: credit the wallet and link the resulting transaction.
     * Returns the created Transaction, or null if the top-up cannot be completed.
     */
    public function complete(): ?Transaction
    {
        if (!$this->status->canComplete()) {
            return null;
        }

        return DB::transaction(function () {
            $transaction = $this->wallet->deposit(
                amount: (float) $this->amount,
                description: $this->description ?? "Top up {$this->reference}",
                externalReference: $this->gateway_reference,
                paymentMethod: $this->payment_method,
                metadata: array_merge($this->metadata ?? [], [
                    'topup_id' => $this->id,
                    'topup_reference' => $this->reference,
                    'provider' => $this->provider,
                ]),
            );

            $this->update([
                'status' => TopUpStatus::COMPLETED,
                'transaction_id' => $transaction->id,
                'completed_at' => now(),
            ]);

            return $transaction;
        });
    }

    public function markAsProcessing(): bool
    {
        if ($this->status !== TopUpStatus::PENDING) {
            return false;
        }

        return $this->update(['status' => TopUpStatus::PROCESSING]);
    }

    public function markAsFailed(?string $reason = null): bool
    {
        if ($this->status->isFinal()) {
            return false;
        }

        return $this->update([
            'status' => TopUpStatus::FAILED,
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
            'status' => TopUpStatus::CANCELLED,
            'cancelled_at' => now(),
        ]);
    }

    // ==================== HELPERS ====================

    public static function generateReference(): string
    {
        do {
            $reference = 'TOP-' . strtoupper(Str::random(12));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }
}
