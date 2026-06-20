/**
 * Wallets Module - TypeScript Type Definitions
 *
 * This is the main entry point for all Wallets module types.
 * It re-exports from specialized files:
 * - common.ts: Shared/generic types (pagination, currency, customer)
 * - Wallet.ts: Wallet-related types, enums, configs, helpers
 * - Transaction.ts: Transaction-related types, enums, configs, helpers
 *
 * @module Wallets/Types
 */

// ============================================================================
// RE-EXPORTS: COMMON TYPES
// ============================================================================

export type {
    CurrencyCode,
    BadgeVariant,
    PaginationMeta,
    PaginatedResponse,
    SelectOption,
    StatusConfig,
    Customer,
    CustomerOption,
} from './common';

export { getSelectOptions } from './common';

// ============================================================================
// RE-EXPORTS: WALLET TYPES
// ============================================================================

export {
    WalletStatusEnum,
    type WalletStatus,
    type Wallet,
    type WalletSummary,
    type WalletFormData,
    type WalletStats,
    type WalletFilters,
    type WalletIndexProps,
    type WalletCreateProps,
    type WalletEditProps,
    type WalletDeleteProps,
    type WalletShowProps,
    WALLET_STATUS_CONFIG,
    canWalletTransact,
    getWalletStatusOptions,
} from './Wallet';

// ============================================================================
// RE-EXPORTS: TRANSACTION TYPES
// ============================================================================

export {
    TransactionTypeEnum,
    type TransactionType,
    TransactionStatusEnum,
    type TransactionStatus,
    type RelatedWallet,
    type TransactionReference,
    type TransferableWallet,
    type Transaction,
    type TransactionDetail,
    type TransactionStats,
    type TransactionFilters,
    type TransactionIndexProps,
    type TransactionShowProps,
    type DepositProps,
    type WithdrawProps,
    type TransferProps,
    type TransactionTypeConfig,
    TRANSACTION_TYPE_CONFIG,
    TRANSACTION_STATUS_CONFIG,
    isCreditTransaction,
    isDebitTransaction,
    isTransactionFinal,
    getTransactionTypeOptions,
    getTransactionStatusOptions,
} from './Transaction';

// ============================================================================
// RE-EXPORTS: TOP-UP TYPES
// ============================================================================

export {
    TopUpStatusEnum,
    type TopUpStatus,
    type TopUp,
    type TopUpWalletOption,
    type TopUpStats,
    type TopUpFilters,
    type TopUpFormData,
    type TopUpIndexProps,
    type TopUpCreateProps,
    type TopUpShowProps,
    type TopUpDeleteProps,
    TOPUP_STATUS_CONFIG,
    PAYMENT_METHOD_OPTIONS,
    getTopUpStatusVariant,
} from './TopUp';
