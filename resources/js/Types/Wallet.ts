/**
 * Wallet Type Definitions
 *
 * This file contains all wallet-related type definitions including:
 * - Wallet status enum and types
 * - Wallet model and related interfaces
 * - Wallet page props
 * - Wallet UI configuration
 *
 * @module Wallets/Types/Wallet
 */

import type {
    BadgeVariant,
    CurrencyCode,
    Customer,
    CustomerOption,
    PaginatedResponse,
    SelectOption,
    StatusConfig,
} from './common';

// ============================================================================
// WALLET ENUMS & TYPES
// ============================================================================

/** Wallet status enum values */
export const WalletStatusEnum = {
    ACTIVE: 'active',
    INACTIVE: 'inactive',
    SUSPENDED: 'suspended',
} as const;

export type WalletStatus = (typeof WalletStatusEnum)[keyof typeof WalletStatusEnum];

// ============================================================================
// WALLET MODEL
// ============================================================================

/** Core wallet model */
export interface Wallet {
    id: number;
    customer_id: number;
    wallet_number: string;
    balance: number;
    locked_amount: number;
    currency: CurrencyCode | string;
    status: WalletStatus;
    description: string | null;
    suspended_at: string | null;
    suspended_reason: string | null;
    created_at: string;
    updated_at: string;
    customer?: Customer;
}

/** Wallet summary for transaction pages */
export interface WalletSummary {
    id: number;
    wallet_number: string;
    balance: number;
    locked_amount: number;
    available_balance: number;
    currency: string;
    status: WalletStatus;
    can_transact: boolean;
    customer: Pick<Customer, 'id' | 'name'> | null;
}

/** Wallet form data for create/edit */
export interface WalletFormData {
    customer_id: number | null;
    wallet_number: string;
    balance: number;
    locked_amount: number;
    currency: string;
    status: WalletStatus;
    description: string;
}

/** Wallet statistics for dashboard/index */
export interface WalletStats {
    total: number;
    active: number;
    inactive: number;
    suspended: number;
    total_balance: number;
    total_locked: number;
}

/** Wallet filters for list queries */
export interface WalletFilters {
    status?: WalletStatus | '';
    search?: string;
}

// ============================================================================
// WALLET PAGE PROPS
// ============================================================================

/** Props for Wallet Index page */
export interface WalletIndexProps {
    walletItems: PaginatedResponse<Wallet>;
    filters: WalletFilters;
    stats: WalletStats;
}

/** Props for Wallet Create modal */
export interface WalletCreateProps {
    customers: CustomerOption[];
}

/** Props for Wallet Edit modal */
export interface WalletEditProps {
    wallet: Wallet;
    customers: CustomerOption[];
}

/** Props for Wallet Delete modal */
export interface WalletDeleteProps {
    wallet: Wallet;
}

/** Props for Wallet Show page */
export interface WalletShowProps {
    wallet: Wallet;
}

// ============================================================================
// WALLET UI CONFIGURATION
// ============================================================================

/** Wallet status UI configuration */
export const WALLET_STATUS_CONFIG: Record<WalletStatus, StatusConfig> = {
    [WalletStatusEnum.ACTIVE]: {
        label: 'Active',
        variant: 'default',
        color: 'text-green-600',
    },
    [WalletStatusEnum.INACTIVE]: {
        label: 'Inactive',
        variant: 'secondary',
        color: 'text-gray-600',
    },
    [WalletStatusEnum.SUSPENDED]: {
        label: 'Suspended',
        variant: 'outline',
        color: 'text-yellow-600',
    },
} as const;

// ============================================================================
// WALLET HELPER FUNCTIONS
// ============================================================================

/** Check if wallet can perform transactions */
export const canWalletTransact = (status: WalletStatus): boolean => {
    return status === WalletStatusEnum.ACTIVE;
};

/** Get wallet status options for select */
export const getWalletStatusOptions = (): SelectOption<WalletStatus>[] => {
    return Object.values(WalletStatusEnum).map((value) => ({
        value,
        label: WALLET_STATUS_CONFIG[value]?.label ?? value,
    }));
};
