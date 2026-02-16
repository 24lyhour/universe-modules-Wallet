/**
 * Common/Shared Type Definitions
 *
 * This file contains generic types shared across wallet and transaction modules:
 * - Currency types
 * - UI types (badges, variants)
 * - Pagination types
 * - Customer types
 *
 * @module Wallets/Types/Common
 */

// ============================================================================
// GENERIC TYPES
// ============================================================================

/** Supported currency codes */
export type CurrencyCode = 'USD' | 'EUR' | 'GBP' | 'JPY' | 'CNY' | 'KHR';

/** Badge variant types for UI */
export type BadgeVariant = 'default' | 'secondary' | 'destructive' | 'outline';

/** Pagination metadata from Laravel */
export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

/** Generic paginated response wrapper */
export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
}

/** Generic select option */
export interface SelectOption<T = string> {
    value: T;
    label: string;
}

/** Status configuration for badges and icons */
export interface StatusConfig {
    label: string;
    variant: BadgeVariant;
    color: string;
}

// ============================================================================
// RELATED MODELS
// ============================================================================

/** Customer model (from Customer module) */
export interface Customer {
    id: number;
    name: string;
    email: string;
}

/** Minimal customer for select dropdowns */
export interface CustomerOption {
    id: number;
    name: string;
    email: string;
}

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================

/** Get select options from enum */
export const getSelectOptions = <T extends Record<string, string>>(
    enumObj: T,
    config: Record<string, { label: string }>,
): SelectOption[] => {
    return Object.values(enumObj).map((value) => ({
        value,
        label: config[value]?.label ?? value,
    }));
};
