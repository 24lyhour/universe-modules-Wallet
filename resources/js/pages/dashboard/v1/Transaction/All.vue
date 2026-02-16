<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableReusable, StatsCard } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    ArrowDownCircle,
    ArrowUpCircle,
    ArrowLeftRight,
    Eye,
    RotateCcw,
    XCircle,
    Clock,
    CheckCircle,
    AlertTriangle,
    TrendingUp,
    TrendingDown,
    Activity,
    Wallet,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type {
    Transaction,
    TransactionStats,
    TransactionFilters,
    PaginationMeta,
    SelectOption,
} from '@wallets/Types';

interface AllTransactionsProps {
    transactions: {
        data: (Transaction & { wallet_id: number; wallet_number: string; customer_name: string })[];
        meta: PaginationMeta;
    };
    filters: TransactionFilters & { wallet_id?: string; search?: string };
    stats: TransactionStats;
    wallets: { id: number; wallet_number: string; customer_name: string }[];
    transactionTypes: SelectOption[];
    transactionStatuses: SelectOption[];
}

const props = defineProps<AllTransactionsProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Wallets', href: '/dashboard/wallets' },
    { title: 'All Transactions', href: '/dashboard/transactions' },
];

// Filters
const typeFilter = ref(props.filters.type || '');
const statusFilter = ref(props.filters.status || '');
const walletFilter = ref(props.filters.wallet_id || '');

// Table columns
const columns = [
    { key: 'reference', label: 'Reference', sortable: true },
    { key: 'wallet', label: 'Wallet', sortable: false },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'amount', label: 'Amount', sortable: true, align: 'right' as const },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Date', sortable: true },
];

// Table actions
const actions = [
    {
        icon: Eye,
        label: 'View',
        onClick: (row: Transaction & { wallet_id: number; wallet_number: string; customer_name: string }) =>
            router.visit(`/dashboard/wallets/${row.wallet_id}/transactions/${row.id}`),
    },
];

// Pagination
const pagination = computed<PaginationMeta>(() => ({
    current_page: props.transactions.meta.current_page,
    last_page: props.transactions.meta.last_page,
    per_page: props.transactions.meta.per_page,
    total: props.transactions.meta.total,
}));

// Handlers
const handlePageChange = (page: number) => {
    router.get('/dashboard/transactions', {
        page,
        per_page: pagination.value.per_page,
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
        wallet_id: walletFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/transactions', {
        page: 1,
        per_page: perPage,
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
        wallet_id: walletFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    router.get('/dashboard/transactions', {
        search,
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
        wallet_id: walletFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleTypeFilter = (type: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const typeStr = String(type || 'all');
    typeFilter.value = typeStr === 'all' ? '' : typeStr;
    router.get('/dashboard/transactions', {
        type: typeStr === 'all' ? undefined : typeStr,
        status: statusFilter.value || undefined,
        wallet_id: walletFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleStatusFilter = (status: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const statusStr = String(status || 'all');
    statusFilter.value = statusStr === 'all' ? '' : statusStr;
    router.get('/dashboard/transactions', {
        type: typeFilter.value || undefined,
        status: statusStr === 'all' ? undefined : statusStr,
        wallet_id: walletFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleWalletFilter = (walletId: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const walletStr = String(walletId || 'all');
    walletFilter.value = walletStr === 'all' ? '' : walletStr;
    router.get('/dashboard/transactions', {
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
        wallet_id: walletStr === 'all' ? undefined : walletStr,
    }, { preserveState: true, preserveScroll: true });
};

// Format currency
const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount);
};

// Format date
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Get status badge variant
const getStatusVariant = (status: string): 'default' | 'secondary' | 'destructive' | 'outline' => {
    switch (status) {
        case 'completed':
            return 'default';
        case 'pending':
        case 'processing':
            return 'outline';
        case 'failed':
        case 'cancelled':
            return 'destructive';
        case 'reversed':
            return 'secondary';
        default:
            return 'secondary';
    }
};

// Get status icon
const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed':
            return CheckCircle;
        case 'pending':
        case 'processing':
            return Clock;
        case 'failed':
        case 'cancelled':
            return XCircle;
        case 'reversed':
            return RotateCcw;
        default:
            return AlertTriangle;
    }
};

// Get type icon
const getTypeIcon = (type: string) => {
    switch (type) {
        case 'deposit':
        case 'transfer_in':
        case 'refund':
            return ArrowDownCircle;
        case 'withdrawal':
        case 'transfer_out':
        case 'payment':
        case 'fee':
            return ArrowUpCircle;
        default:
            return ArrowLeftRight;
    }
};

// Transform data for table
const tableData = computed(() => {
    return props.transactions.data.map((txn) => ({
        ...txn,
        created_at_formatted: formatDate(txn.created_at),
    }));
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="All Transactions" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">All Transactions</h1>
                    <p class="text-muted-foreground">
                        View all transactions across all wallets
                    </p>
                </div>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-6">
                <StatsCard
                    title="Total Transactions"
                    :value="stats.total_transactions"
                    :icon="Activity"
                />
                <StatsCard
                    title="Completed"
                    :value="stats.completed"
                    :icon="CheckCircle"
                    variant="success"
                />
                <StatsCard
                    title="Pending"
                    :value="stats.pending"
                    :icon="Clock"
                    variant="warning"
                />
                <StatsCard
                    title="Failed"
                    :value="stats.failed"
                    :icon="XCircle"
                    variant="destructive"
                />
                <StatsCard
                    title="Total Credits"
                    :value="formatCurrency(stats.total_credits)"
                    :icon="TrendingUp"
                    variant="success"
                />
                <StatsCard
                    title="Total Debits"
                    :value="formatCurrency(stats.total_debits)"
                    :icon="TrendingDown"
                    variant="info"
                />
            </div>

            <!-- Table -->
            <TableReusable
                :data="tableData"
                :columns="columns"
                :actions="actions"
                :pagination="pagination"
                :searchable="true"
                search-placeholder="Search by reference or wallet..."
                @page-change="handlePageChange"
                @per-page-change="handlePerPageChange"
                @search="handleSearch"
            >
                <!-- Toolbar slot for filters -->
                <template #toolbar>
                    <div class="flex flex-wrap items-center gap-2">
                        <Select :model-value="walletFilter || 'all'" @update:model-value="handleWalletFilter">
                            <SelectTrigger class="w-[200px]">
                                <SelectValue placeholder="All Wallets" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Wallets</SelectItem>
                                <SelectItem
                                    v-for="wallet in wallets"
                                    :key="wallet.id"
                                    :value="wallet.id.toString()"
                                >
                                    {{ wallet.wallet_number }} - {{ wallet.customer_name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select :model-value="typeFilter || 'all'" @update:model-value="handleTypeFilter">
                            <SelectTrigger class="w-[150px]">
                                <SelectValue placeholder="All Types" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Types</SelectItem>
                                <SelectItem
                                    v-for="type in transactionTypes"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>

                        <Select :model-value="statusFilter || 'all'" @update:model-value="handleStatusFilter">
                            <SelectTrigger class="w-[150px]">
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem
                                    v-for="status in transactionStatuses"
                                    :key="status.value"
                                    :value="status.value"
                                >
                                    {{ status.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <!-- Reference cell -->
                <template #cell-reference="{ item }">
                    <code class="rounded bg-muted px-2 py-1 text-xs font-mono">
                        {{ item.reference }}
                    </code>
                </template>

                <!-- Wallet cell -->
                <template #cell-wallet="{ item }">
                    <div class="flex flex-col">
                        <Link
                            :href="`/dashboard/wallets/${item.wallet_id}`"
                            class="font-medium text-primary hover:underline"
                        >
                            {{ item.wallet_number }}
                        </Link>
                        <span class="text-xs text-muted-foreground">{{ item.customer_name }}</span>
                    </div>
                </template>

                <!-- Type cell with icon -->
                <template #cell-type="{ item }">
                    <div class="flex items-center gap-2">
                        <component
                            :is="getTypeIcon(item.type)"
                            :class="[
                                'h-4 w-4',
                                item.is_credit ? 'text-green-600' : 'text-red-600',
                            ]"
                        />
                        <span>{{ item.type_label }}</span>
                    </div>
                </template>

                <!-- Amount cell with color -->
                <template #cell-amount="{ item }">
                    <div class="text-right">
                        <span
                            :class="[
                                'font-medium',
                                item.is_credit ? 'text-green-600' : 'text-red-600',
                            ]"
                        >
                            {{ item.is_credit ? '+' : '-' }}{{ formatCurrency(item.amount, item.currency) }}
                        </span>
                    </div>
                </template>

                <!-- Status cell with icon -->
                <template #cell-status="{ item }">
                    <Badge :variant="getStatusVariant(item.status)" class="capitalize">
                        <component :is="getStatusIcon(item.status)" class="mr-1 h-3 w-3" />
                        {{ item.status_label }}
                    </Badge>
                </template>

                <!-- Date cell -->
                <template #cell-created_at="{ item }">
                    <span class="text-sm text-muted-foreground">
                        {{ formatDate(item.created_at) }}
                    </span>
                </template>
            </TableReusable>
        </div>
    </AppLayout>
</template>
