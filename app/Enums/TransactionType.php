<?php

namespace Modules\Wallets\Enums;

enum TransactionType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case PAYMENT = 'payment';
    case REFUND = 'refund';
    case FEE = 'fee';
    case ADJUSTMENT = 'adjustment';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Deposit',
            self::WITHDRAWAL => 'Withdrawal',
            self::TRANSFER_IN => 'Transfer In',
            self::TRANSFER_OUT => 'Transfer Out',
            self::PAYMENT => 'Payment',
            self::REFUND => 'Refund',
            self::FEE => 'Fee',
            self::ADJUSTMENT => 'Adjustment',
        };
    }

    /**
     * Get display color for UI
     */
    public function color(): string
    {
        return match ($this) {
            self::DEPOSIT, self::TRANSFER_IN, self::REFUND => 'success',
            self::WITHDRAWAL, self::TRANSFER_OUT, self::PAYMENT, self::FEE => 'destructive',
            self::ADJUSTMENT => 'secondary',
        };
    }

    /**
     * Check if this is a credit transaction (adds money)
     */
    public function isCredit(): bool
    {
        return in_array($this, [
            self::DEPOSIT,
            self::TRANSFER_IN,
            self::REFUND,
        ]);
    }

    /**
     * Check if this is a debit transaction (removes money)
     */
    public function isDebit(): bool
    {
        return in_array($this, [
            self::WITHDRAWAL,
            self::TRANSFER_OUT,
            self::PAYMENT,
            self::FEE,
        ]);
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
            fn(self $type) => ['value' => $type->value, 'label' => $type->label()],
            self::cases()
        );
    }
}
