/**
 * Transaction Type Definitions
 *
 * This file contains all transaction-related type definitions including:
 * - Transaction type and status enums
 * - Transaction model and related interfaces
 * - Transaction page props
 * - Transaction UI configuration
 *
 * @module Wallets/Types/Transaction
 */

import type {
    BadgeVariant,
    PaginatedResponse,
    SelectOption,
    StatusConfig,
} from './common';
import type { Wallet, WalletSummary } from './Wallet';

// ============================================================================
// TRANSACTION ENUMS & TYPES
// ============================================================================

/** Transaction type enum values */
export const TransactionTypeEnum = {
    DEPOSIT: 'deposit',
    WITHDRAWAL: 'withdrawal',
    TRANSFER_IN: 'transfer_in',
    TRANSFER_OUT: 'transfer_out',
    PAYMENT: 'payment',
    REFUND: 'refund',
    FEE: 'fee',
    ADJUSTMENT: 'adjustment',
} as const;

export type TransactionType = (typeof TransactionTypeEnum)[keyof typeof TransactionTypeEnum];

/** Transaction status enum values */
export const TransactionStatusEnum = {
    PENDING: 'pending',
    PROCESSING: 'processing',
    COMPLETED: 'completed',
    FAILED: 'failed',
    CANCELLED: 'cancelled',
    REVERSED: 'reversed',
} as const;

export type TransactionStatus = (typeof TransactionStatusEnum)[keyof typeof TransactionStatusEnum];

// ============================================================================
// TRANSACTION RELATED MODELS
// ============================================================================

/** Related wallet reference (for transfers) */
export interface RelatedWallet {
    id: number;
    wallet_number: string;
}

/** Transaction reference (for reversals) */
export interface TransactionReference {
    id: number;
    reference: string;
}

/** Available wallet for transfer selection */
export interface TransferableWallet {
    id: number;
    wallet_number: string;
    customer_name: string;
    currency: string;
}

// ============================================================================
// TRANSACTION MODEL
// ============================================================================

/** Core transaction model */
export interface Transaction {
    id: number;
    reference: string;
    type: TransactionType;
    type_label: string;
    type_color: string;
    status: TransactionStatus;
    status_label: string;
    status_variant: BadgeVariant;
    amount: number;
    fee: number;
    net_amount: number;
    signed_amount: number;
    balance_before: number;
    balance_after: number;
    currency: string;
    description: string | null;
    external_reference: string | null;
    payment_method: string | null;
    is_credit: boolean;
    is_debit: boolean;
    is_reversed: boolean;
    related_wallet: RelatedWallet | null;
    created_at: string;
    completed_at: string | null;
}

/** Extended transaction with full details */
export interface TransactionDetail extends Transaction {
    metadata: Record<string, unknown> | null;
    is_final: boolean;
    can_reverse: boolean;
    can_cancel: boolean;
    reversed_transaction: TransactionReference | null;
    reversal_transaction: TransactionReference | null;
    processed_at: string | null;
    failed_at: string | null;
    failure_reason: string | null;
    reversed_at: string | null;
    updated_at: string;
}

/** Transaction statistics */
export interface TransactionStats {
    total_transactions: number;
    completed: number;
    pending: number;
    failed: number;
    total_credits: number;
    total_debits: number;
}

/** Transaction filters for list queries */
export interface TransactionFilters {
    type?: TransactionType | '';
    status?: TransactionStatus | '';
    date_from?: string;
    date_to?: string;
}

// ============================================================================
// TRANSACTION PAGE PROPS
// ============================================================================

/** Props for Transaction Index page */
export interface TransactionIndexProps {
    wallet: WalletSummary;
    transactions: PaginatedResponse<Transaction>;
    filters: TransactionFilters;
    stats: TransactionStats;
    transactionTypes: SelectOption[];
    transactionStatuses: SelectOption[];
}

/** Props for Transaction Show page */
export interface TransactionShowProps {
    wallet: Pick<Wallet, 'id' | 'wallet_number'>;
    transaction: TransactionDetail;
}

/** Props for Deposit modal */
export interface DepositProps {
    wallet: Pick<WalletSummary, 'id' | 'wallet_number' | 'balance' | 'currency' | 'status' | 'can_transact'>;
}

/** Props for Withdraw modal */
export interface WithdrawProps {
    wallet: Pick<WalletSummary, 'id' | 'wallet_number' | 'balance' | 'available_balance' | 'currency' | 'status' | 'can_transact'>;
}

/** Props for Transfer modal */
export interface TransferProps {
    wallet: Pick<WalletSummary, 'id' | 'wallet_number' | 'balance' | 'available_balance' | 'currency' | 'status' | 'can_transact'>;
    availableWallets: TransferableWallet[];
}

// ============================================================================
// TRANSACTION UI CONFIGURATION
// ============================================================================

/** Transaction type configuration */
export interface TransactionTypeConfig extends StatusConfig {
    icon: string;
}

/** Transaction type UI configuration */
export const TRANSACTION_TYPE_CONFIG: Record<TransactionType, TransactionTypeConfig> = {
    [TransactionTypeEnum.DEPOSIT]: {
        label: 'Deposit',
        variant: 'default',
        color: 'text-green-600',
        icon: 'ArrowDownCircle',
    },
    [TransactionTypeEnum.WITHDRAWAL]: {
        label: 'Withdrawal',
        variant: 'destructive',
        color: 'text-red-600',
        icon: 'ArrowUpCircle',
    },
    [TransactionTypeEnum.TRANSFER_IN]: {
        label: 'Transfer In',
        variant: 'default',
        color: 'text-green-600',
        icon: 'ArrowLeftCircle',
    },
    [TransactionTypeEnum.TRANSFER_OUT]: {
        label: 'Transfer Out',
        variant: 'destructive',
        color: 'text-red-600',
        icon: 'ArrowRightCircle',
    },
    [TransactionTypeEnum.PAYMENT]: {
        label: 'Payment',
        variant: 'destructive',
        color: 'text-red-600',
        icon: 'CreditCard',
    },
    [TransactionTypeEnum.REFUND]: {
        label: 'Refund',
        variant: 'default',
        color: 'text-green-600',
        icon: 'RotateCcw',
    },
    [TransactionTypeEnum.FEE]: {
        label: 'Fee',
        variant: 'outline',
        color: 'text-orange-600',
        icon: 'Percent',
    },
    [TransactionTypeEnum.ADJUSTMENT]: {
        label: 'Adjustment',
        variant: 'secondary',
        color: 'text-blue-600',
        icon: 'Settings',
    },
} as const;

/** Transaction status UI configuration */
export const TRANSACTION_STATUS_CONFIG: Record<TransactionStatus, StatusConfig> = {
    [TransactionStatusEnum.PENDING]: {
        label: 'Pending',
        variant: 'outline',
        color: 'text-yellow-600',
    },
    [TransactionStatusEnum.PROCESSING]: {
        label: 'Processing',
        variant: 'secondary',
        color: 'text-blue-600',
    },
    [TransactionStatusEnum.COMPLETED]: {
        label: 'Completed',
        variant: 'default',
        color: 'text-green-600',
    },
    [TransactionStatusEnum.FAILED]: {
        label: 'Failed',
        variant: 'destructive',
        color: 'text-red-600',
    },
    [TransactionStatusEnum.CANCELLED]: {
        label: 'Cancelled',
        variant: 'secondary',
        color: 'text-gray-600',
    },
    [TransactionStatusEnum.REVERSED]: {
        label: 'Reversed',
        variant: 'outline',
        color: 'text-purple-600',
    },
} as const;

// ============================================================================
// TRANSACTION HELPER FUNCTIONS
// ============================================================================

/** Credit transaction types (adds money to wallet) */
const CREDIT_TYPES: TransactionType[] = [
    TransactionTypeEnum.DEPOSIT,
    TransactionTypeEnum.TRANSFER_IN,
    TransactionTypeEnum.REFUND,
];

/** Debit transaction types (removes money from wallet) */
const DEBIT_TYPES: TransactionType[] = [
    TransactionTypeEnum.WITHDRAWAL,
    TransactionTypeEnum.TRANSFER_OUT,
    TransactionTypeEnum.PAYMENT,
    TransactionTypeEnum.FEE,
];

/** Final transaction statuses (no more changes possible) */
const FINAL_STATUSES: TransactionStatus[] = [
    TransactionStatusEnum.COMPLETED,
    TransactionStatusEnum.FAILED,
    TransactionStatusEnum.CANCELLED,
    TransactionStatusEnum.REVERSED,
];

/** Check if transaction type is a credit (adds money) */
export const isCreditTransaction = (type: TransactionType): boolean => {
    return CREDIT_TYPES.includes(type);
};

/** Check if transaction type is a debit (removes money) */
export const isDebitTransaction = (type: TransactionType): boolean => {
    return DEBIT_TYPES.includes(type);
};

/** Check if transaction status is final (no more changes possible) */
export const isTransactionFinal = (status: TransactionStatus): boolean => {
    return FINAL_STATUSES.includes(status);
};

/** Get transaction type options for select */
export const getTransactionTypeOptions = (): SelectOption<TransactionType>[] => {
    return Object.values(TransactionTypeEnum).map((value) => ({
        value,
        label: TRANSACTION_TYPE_CONFIG[value]?.label ?? value,
    }));
};

/** Get transaction status options for select */
export const getTransactionStatusOptions = (): SelectOption<TransactionStatus>[] => {
    return Object.values(TransactionStatusEnum).map((value) => ({
        value,
        label: TRANSACTION_STATUS_CONFIG[value]?.label ?? value,
    }));
};
