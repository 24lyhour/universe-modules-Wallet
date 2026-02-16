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
    Wallet,
    TrendingUp,
    TrendingDown,
    Activity,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type {
    TransactionIndexProps,
    Transaction,
    PaginationMeta,
} from '@wallets/Types';

const props = defineProps<TransactionIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Wallets', href: '/dashboard/wallets' },
    { title: props.wallet.wallet_number, href: `/dashboard/wallets/${props.wallet.id}` },
    { title: 'Transactions', href: `/dashboard/wallets/${props.wallet.id}/transactions` },
];

// Filters
const typeFilter = ref(props.filters.type || '');
const statusFilter = ref(props.filters.status || '');

// Table columns
const columns = [
    { key: 'reference', label: 'Reference', sortable: true },
    { key: 'type', label: 'Type', sortable: true },
    { key: 'amount', label: 'Amount', sortable: true, align: 'right' as const },
    { key: 'balance_after', label: 'Balance After', sortable: true, align: 'right' as const },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Date', sortable: true },
];

// Table actions
const actions = [
    {
        icon: Eye,
        label: 'View',
        onClick: (row: Transaction) => router.visit(`/dashboard/wallets/${props.wallet.id}/transactions/${row.id}`),
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
    router.get(`/dashboard/wallets/${props.wallet.id}/transactions`, {
        page,
        per_page: pagination.value.per_page,
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get(`/dashboard/wallets/${props.wallet.id}/transactions`, {
        page: 1,
        per_page: perPage,
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    router.get(`/dashboard/wallets/${props.wallet.id}/transactions`, {
        search,
        type: typeFilter.value || undefined,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleTypeFilter = (type: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const typeStr = String(type || 'all');
    typeFilter.value = typeStr === 'all' ? '' : typeStr;
    router.get(`/dashboard/wallets/${props.wallet.id}/transactions`, {
        type: typeStr === 'all' ? undefined : typeStr,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleStatusFilter = (status: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const statusStr = String(status || 'all');
    statusFilter.value = statusStr === 'all' ? '' : statusStr;
    router.get(`/dashboard/wallets/${props.wallet.id}/transactions`, {
        type: typeFilter.value || undefined,
        status: statusStr === 'all' ? undefined : statusStr,
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
        <Head :title="`Transactions - ${wallet.wallet_number}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Transactions</h1>
                    <p class="text-muted-foreground">
                        {{ wallet.wallet_number }} - {{ wallet.customer?.name || 'N/A' }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/dashboard/wallets/${wallet.id}/transactions/deposit/create`">
                            <ArrowDownCircle class="mr-2 h-4 w-4" />
                            Deposit
                        </Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="`/dashboard/wallets/${wallet.id}/transactions/withdraw/create`">
                            <ArrowUpCircle class="mr-2 h-4 w-4" />
                            Withdraw
                        </Link>
                    </Button>
                    <Button as-child>
                        <Link :href="`/dashboard/wallets/${wallet.id}/transactions/transfer/create`">
                            <ArrowLeftRight class="mr-2 h-4 w-4" />
                            Transfer
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Wallet Summary Card -->
            <div class="rounded-lg border bg-card p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="rounded-full bg-primary/10 p-3">
                            <Wallet class="h-6 w-6 text-primary" />
                        </div>
                        <div>
                            <p class="text-sm text-muted-foreground">Current Balance</p>
                            <p class="text-2xl font-bold">{{ formatCurrency(wallet.balance, wallet.currency) }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-8">
                        <div class="text-right">
                            <p class="text-sm text-muted-foreground">Available</p>
                            <p class="font-semibold text-green-600">
                                {{ formatCurrency(wallet.available_balance, wallet.currency) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-muted-foreground">Locked</p>
                            <p class="font-semibold text-orange-600">
                                {{ formatCurrency(wallet.locked_amount, wallet.currency) }}
                            </p>
                        </div>
                    </div>
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
                    :value="formatCurrency(stats.total_credits, wallet.currency)"
                    :icon="TrendingUp"
                    variant="success"
                />
                <StatsCard
                    title="Total Debits"
                    :value="formatCurrency(stats.total_debits, wallet.currency)"
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
                search-placeholder="Search by reference..."
                @page-change="handlePageChange"
                @per-page-change="handlePerPageChange"
                @search="handleSearch"
            >
                <!-- Toolbar slot for filters -->
                <template #toolbar>
                    <div class="flex flex-wrap items-center gap-2">
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

                <!-- Balance after cell -->
                <template #cell-balance_after="{ item }">
                    <div class="text-right font-medium">
                        {{ formatCurrency(item.balance_after, item.currency) }}
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
