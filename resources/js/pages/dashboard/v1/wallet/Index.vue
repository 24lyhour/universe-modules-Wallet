<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { TableReusable, StatsCard } from '@/components/shared';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Plus, Wallet, CheckCircle, XCircle, Eye, Pencil, Trash2, DollarSign, Lock } from 'lucide-vue-next';
import { computed } from 'vue';
import type { BreadcrumbItem } from '@/types';
import type { WalletIndexProps, Wallet as WalletType, PaginationMeta } from '@wallets/Types';

const props = defineProps<WalletIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Wallets', href: '/dashboard/wallets' },
];

// Table columns
const columns = [
    { key: 'wallet_number', label: 'Wallet Number', sortable: true },
    { key: 'customer', label: 'Owner', sortable: false },
    { key: 'balance', label: 'Balance', sortable: true },
    { key: 'locked_amount', label: 'Locked', sortable: true },
    { key: 'currency', label: 'Currency', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Created', sortable: true },
];

// Table actions
const actions = [
    {
        icon: Eye,
        label: 'View',
        onClick: (row: WalletType) => router.visit(`/dashboard/wallets/${row.id}`),
    },
    {
        icon: Pencil,
        label: 'Edit',
        onClick: (row: WalletType) => router.visit(`/dashboard/wallets/${row.id}/edit`),
    },
    {
        icon: Trash2,
        label: 'Delete',
        variant: 'destructive' as const,
        onClick: (row: WalletType) => router.visit(`/dashboard/wallets/${row.id}/delete`),
    },
];

// Pagination
const pagination = computed<PaginationMeta>(() => ({
    current_page: props.walletItems.meta.current_page,
    last_page: props.walletItems.meta.last_page,
    per_page: props.walletItems.meta.per_page,
    total: props.walletItems.meta.total,
}));

// Handlers
const handlePageChange = (page: number) => {
    router.get('/dashboard/wallets', { page, per_page: pagination.value.per_page }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/wallets', { page: 1, per_page: perPage }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSearch = (search: string) => {
    router.get('/dashboard/wallets', { search }, {
        preserveState: true,
        preserveScroll: true,
    });
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
    });
};

// Transform data for table (keep raw values for slots)
const tableData = computed<any[]>(() => {
    return props.walletItems.data.map((wallet) => ({
        ...wallet,
        customer: wallet.customer?.name || 'N/A',
        balance_raw: wallet.balance,
        balance: formatCurrency(wallet.balance, wallet.currency),
        locked_amount: formatCurrency(wallet.locked_amount, wallet.currency),
        created_at: formatDate(wallet.created_at),
    }));
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Wallets" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Wallets</h1>
                    <p class="text-muted-foreground">Manage customer wallets and balances</p>
                </div>
                <Button as-child>
                    <Link href="/dashboard/wallets/create">
                        <Plus class="mr-2 h-4 w-4" />
                        Add Wallet
                    </Link>
                </Button>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-5">
                <StatsCard
                    title="Total Wallets"
                    :value="props.stats.total"
                    :icon="Wallet"
                />
                <StatsCard
                    title="Active"
                    :value="props.stats.active"
                    :icon="CheckCircle"
                    variant="success"
                />
                <StatsCard
                    title="Inactive"
                    :value="props.stats.inactive"
                    :icon="XCircle"
                    variant="warning"
                />
                <StatsCard
                    title="Total Balance"
                    :value="formatCurrency(props.stats.total_balance)"
                    :icon="DollarSign"
                    variant="info"
                />
                <StatsCard
                    title="Total Locked"
                    :value="formatCurrency(props.stats.total_locked)"
                    :icon="Lock"
                    variant="secondary"
                />
            </div>

            <!-- Table -->
            <TableReusable
                :data="tableData"
                :columns="columns"
                :actions="actions"
                :pagination="pagination"
                :searchable="true"
                search-placeholder="Search by wallet number or owner..."
                @page-change="handlePageChange"
                @per-page-change="handlePerPageChange"
                @search="handleSearch"
            >
                <!-- Wallet number with balance badge -->
                <template #cell-wallet_number="{ item }">
                    <div class="flex flex-col gap-1">
                        <span class="font-medium">{{ item.wallet_number }}</span>
                        <Badge variant="outline" class="w-fit text-xs">
                            {{ item.balance }}
                        </Badge>
                    </div>
                </template>

                <!-- Status column slot -->
                <template #cell-status="{ item }">
                    <Badge :variant="item.status === 'active' ? 'default' : 'secondary'">
                        {{ item.status }}
                    </Badge>
                </template>
            </TableReusable>
        </div>
    </AppLayout>
</template>
