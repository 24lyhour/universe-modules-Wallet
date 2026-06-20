<?php

namespace Modules\Wallets\Enums;

enum TopUpStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::FAILED => 'destructive',
            self::CANCELLED => 'secondary',
        };
    }

    public function variant(): string
    {
        return match ($this) {
            self::PENDING => 'outline',
            self::PROCESSING => 'secondary',
            self::COMPLETED => 'default',
            self::FAILED => 'destructive',
            self::CANCELLED => 'secondary',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::CANCELLED]);
    }

    public function canComplete(): bool
    {
        return in_array($this, [self::PENDING, self::PROCESSING]);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::PENDING, self::PROCESSING]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function options(): array
    {
        return array_map(
            fn (self $s) => ['value' => $s->value, 'label' => $s->label()],
            self::cases()
        );
    }
}
