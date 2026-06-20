/**
 * TopUp Type Definitions
 *
 * @module Wallets/Types/TopUp
 */

import type { BadgeVariant, PaginatedResponse, SelectOption, StatusConfig } from './common';

// ============================================================================
// TOP-UP ENUMS & TYPES
// ============================================================================

export const TopUpStatusEnum = {
    PENDING: 'pending',
    PROCESSING: 'processing',
    COMPLETED: 'completed',
    FAILED: 'failed',
    CANCELLED: 'cancelled',
} as const;

export type TopUpStatus = (typeof TopUpStatusEnum)[keyof typeof TopUpStatusEnum];

// ============================================================================
// TOP-UP MODEL
// ============================================================================

export interface TopUp {
    id: number;
    reference: string;
    wallet_id: number;
    transaction_id: number | null;
    amount: number;
    fee: number;
    net_amount: number;
    currency: string;
    payment_method: string;
    provider: string | null;
    gateway_reference: string | null;
    status: TopUpStatus;
    status_label: string;
    description: string | null;
    failure_reason: string | null;
    is_final: boolean;
    completed_at: string | null;
    failed_at: string | null;
    cancelled_at: string | null;
    created_at: string;
    // Eager-loaded
    wallet?: {
        id: number;
        wallet_number: string;
        customer?: { id: number; name: string } | null;
    };
}

export interface TopUpWalletOption {
    id: number;
    wallet_number: string;
    customer_name: string;
    currency: string;
}

export interface TopUpStats {
    total: number;
    pending: number;
    completed: number;
    failed: number;
    total_completed_amount: number;
    total_pending_amount: number;
}

export interface TopUpFilters {
    search?: string;
    status?: TopUpStatus | '';
    wallet_id?: number | '';
    payment_method?: string | '';
    date_from?: string;
    date_to?: string;
}

export interface TopUpFormData {
    wallet_id: number | null;
    amount: number;
    payment_method: string;
    provider: string;
    gateway_reference: string;
    description: string;
}

// ============================================================================
// PAGE PROPS
// ============================================================================

export interface TopUpIndexProps {
    topups: PaginatedResponse<TopUp>;
    filters: TopUpFilters;
    stats: TopUpStats;
    wallets: TopUpWalletOption[];
    topupStatuses: SelectOption<TopUpStatus>[];
}

export interface TopUpCreateProps {
    wallets: TopUpWalletOption[];
    topupStatuses: SelectOption<TopUpStatus>[];
}

export interface TopUpShowProps {
    topup: TopUp;
    wallet: {
        id: number;
        wallet_number: string;
        balance: number;
        currency: string;
        customer_name: string;
    };
    transaction: {
        id: number;
        reference: string;
        amount: number;
        balance_after: number;
        completed_at: string | null;
    } | null;
    creator: { id: number; name: string } | null;
}

export interface TopUpDeleteProps {
    topup: TopUp;
    wallet_number: string;
}

// ============================================================================
// UI CONFIG
// ============================================================================

export const TOPUP_STATUS_CONFIG: Record<TopUpStatus, StatusConfig> = {
    [TopUpStatusEnum.PENDING]: { label: 'Pending', variant: 'outline', color: 'text-yellow-600' },
    [TopUpStatusEnum.PROCESSING]: { label: 'Processing', variant: 'secondary', color: 'text-blue-600' },
    [TopUpStatusEnum.COMPLETED]: { label: 'Completed', variant: 'default', color: 'text-green-600' },
    [TopUpStatusEnum.FAILED]: { label: 'Failed', variant: 'destructive', color: 'text-red-600' },
    [TopUpStatusEnum.CANCELLED]: { label: 'Cancelled', variant: 'secondary', color: 'text-gray-600' },
} as const;

export const PAYMENT_METHOD_OPTIONS: SelectOption[] = [
    { value: 'manual', label: 'Manual (admin credit)' },
    { value: 'card', label: 'Card' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'mobile_money', label: 'Mobile Money' },
    { value: 'cash', label: 'Cash' },
];

export const getTopUpStatusVariant = (status: TopUpStatus): BadgeVariant => {
    return TOPUP_STATUS_CONFIG[status]?.variant ?? 'secondary';
};
