<?php

namespace Modules\Wallets\Enums;

enum TransactionStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REVERSED = 'reversed';
    case REFUNDED = 'refunded';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
            self::REVERSED => 'Reversed',
            self::REFUNDED => 'Refunded',
        };
    }

    /**
     * Get display color for UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'destructive',
            self::CANCELLED => 'secondary',
            self::REVERSED => 'outline',
            self::REFUNDED => 'warning',
        };
    }

    /**
     * Get badge variant for UI.
     */
    public function variant(): string
    {
        return match ($this) {
            self::PENDING => 'outline',
            self::PROCESSING => 'secondary',
            self::COMPLETED => 'default',
            self::FAILED => 'destructive',
            self::CANCELLED => 'secondary',
            self::REVERSED => 'outline',
            self::REFUNDED => 'outline',
        };
    }

    /**
     * Check if transaction is final (no more changes).
     */
    public function isFinal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::FAILED,
            self::CANCELLED,
            self::REVERSED,
            self::REFUNDED,
        ]);
    }

    /**
     * Check if transaction can be cancelled
     */
    public function canCancel(): bool
    {
        return in_array($this, [
            self::PENDING,
            self::PROCESSING,
        ]);
    }

    /**
     * Check if transaction can be reversed.
     */
    public function canReverse(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if transaction can be refunded.
     */
    public function canRefund(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Get all values as array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get options for select dropdown
     */
    public static function options(): array
    {
        return array_map(
            fn(self $status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases()
        );
    }
}
