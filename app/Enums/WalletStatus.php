<?php

namespace Modules\Wallets\Enums;

enum WalletStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Suspended = 'suspended';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Suspended => 'Suspended',
        };
    }

    /**
     * Get display color for UI.
     */
    public function color(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Inactive => 'secondary',
            self::Suspended => 'warning',
        };
    }

    /**
     * Get badge variant for UI.
     */
    public function variant(): string
    {
        return match ($this) {
            self::Active => 'default',
            self::Inactive => 'secondary',
            self::Suspended => 'destructive',
        };
    }

    /**
     * Check if wallet can perform transactions.
     */
    public function canTransact(): bool
    {
        return $this === self::Active;
    }

    /**
     * Get all values as array.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get options for select dropdown.
     */
    public static function options(): array
    {
        return array_map(
            fn (self $status) => ['value' => $status->value, 'label' => $status->label()],
            self::cases()
        );
    }
}
