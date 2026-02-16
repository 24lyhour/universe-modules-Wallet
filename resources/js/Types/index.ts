// Wallet Module Types

export interface Customer {
    id: number;
    name: string;
    email: string;
}

export interface Wallet {
    id: number;
    customer_id: number;
    wallet_number: string;
    balance: number;
    locked_amount: number;
    currency: string;
    status: 'active' | 'inactive';
    description: string | null;
    created_at: string;
    updated_at: string;
    customer?: Customer;
}

export interface WalletStats {
    total: number;
    active: number;
    inactive: number;
    total_balance: number;
    total_locked: number;
}

export interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: PaginationMeta;
}

export interface WalletFilters {
    status?: string;
    search?: string;
}

export interface WalletFormData {
    customer_id: number | null;
    wallet_number: string;
    balance: number;
    locked_amount: number;
    currency: string;
    status: 'active' | 'inactive';
    description: string;
}

export interface CustomerOption {
    id: number;
    name: string;
    email: string;
}

export interface WalletIndexProps {
    walletItems: PaginatedResponse<Wallet>;
    filters: WalletFilters;
    stats: WalletStats;
}

export interface WalletCreateProps {
    customers: CustomerOption[];
}

export interface WalletEditProps {
    wallet: Wallet;
    customers: CustomerOption[];
}

export interface WalletDeleteProps {
    wallet: Wallet;
}

export interface WalletShowProps {
    wallet: Wallet;
}
