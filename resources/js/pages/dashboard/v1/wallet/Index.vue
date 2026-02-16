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
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Plus,
    Wallet,
    CheckCircle,
    XCircle,
    AlertTriangle,
    Eye,
    Pencil,
    Trash2,
    DollarSign,
    Lock,
    MoreHorizontal,
    Play,
    Pause,
    Ban,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { WalletIndexProps, Wallet as WalletType, PaginationMeta, WalletStatus } from '@wallets/Types';

const props = defineProps<WalletIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Wallets', href: '/dashboard/wallets' },
];

// Filters
const statusFilter = ref(props.filters.status || '');

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
    router.get('/dashboard/wallets', {
        page,
        per_page: pagination.value.per_page,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/wallets', {
        page: 1,
        per_page: perPage,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    router.get('/dashboard/wallets', {
        search,
        status: statusFilter.value || undefined,
    }, { preserveState: true, preserveScroll: true });
};

const handleStatusFilter = (status: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    const statusStr = String(status || 'all');
    statusFilter.value = statusStr === 'all' ? '' : statusStr;
    router.get('/dashboard/wallets', {
        status: statusStr === 'all' ? undefined : statusStr,
    }, { preserveState: true, preserveScroll: true });
};

// Status change handlers
const activateWallet = (wallet: WalletType) => {
    router.patch(`/dashboard/wallets/${wallet.id}/activate`, {}, {
        preserveScroll: true,
    });
};

const deactivateWallet = (wallet: WalletType) => {
    router.patch(`/dashboard/wallets/${wallet.id}/deactivate`, {}, {
        preserveScroll: true,
    });
};

const suspendWallet = (wallet: WalletType) => {
    router.patch(`/dashboard/wallets/${wallet.id}/suspend`, {}, {
        preserveScroll: true,
    });
};

const unsuspendWallet = (wallet: WalletType) => {
    router.patch(`/dashboard/wallets/${wallet.id}/unsuspend`, {}, {
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

// Get status badge variant
const getStatusVariant = (status: WalletStatus): 'default' | 'secondary' | 'destructive' | 'outline' => {
    switch (status) {
        case 'active':
            return 'default';
        case 'inactive':
            return 'secondary';
        case 'suspended':
            return 'outline';
        default:
            return 'secondary';
    }
};

// Get status icon
const getStatusIcon = (status: WalletStatus) => {
    switch (status) {
        case 'active':
            return CheckCircle;
        case 'inactive':
            return XCircle;
        case 'suspended':
            return AlertTriangle;
        default:
            return XCircle;
    }
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
            <div class="grid gap-4 md:grid-cols-6">
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
                    title="Suspended"
                    :value="props.stats.suspended"
                    :icon="AlertTriangle"
                    variant="warning"
                />
                <StatsCard
                    title="Inactive"
                    :value="props.stats.inactive"
                    :icon="XCircle"
                    variant="secondary"
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
                    variant="destructive"
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
                <!-- Toolbar slot for filters -->
                <template #toolbar>
                    <div class="flex flex-wrap items-center gap-2">
                        <Select :model-value="statusFilter || 'all'" @update:model-value="handleStatusFilter">
                            <SelectTrigger class="w-[150px]">
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem value="active">Active</SelectItem>
                                <SelectItem value="suspended">Suspended</SelectItem>
                                <SelectItem value="inactive">Inactive</SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <!-- Wallet number with balance badge -->
                <template #cell-wallet_number="{ item }">
                    <div class="flex flex-col gap-1">
                        <span class="font-medium">{{ item.wallet_number }}</span>
                        <Badge variant="outline" class="w-fit text-xs">
                            {{ item.balance }}
                        </Badge>
                    </div>
                </template>

                <!-- Status column slot with actions -->
                <template #cell-status="{ item }">
                    <div class="flex items-center gap-2">
                        <Badge :variant="getStatusVariant(item.status)" class="capitalize">
                            <component :is="getStatusIcon(item.status)" class="mr-1 h-3 w-3" />
                            {{ item.status }}
                        </Badge>

                        <!-- Status change dropdown -->
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-6 w-6">
                                    <MoreHorizontal class="h-3 w-3" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem
                                    v-if="item.status !== 'active'"
                                    @click="activateWallet(item)"
                                    class="text-green-600"
                                >
                                    <Play class="mr-2 h-4 w-4" />
                                    Activate
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="item.status === 'active'"
                                    @click="suspendWallet(item)"
                                    class="text-yellow-600"
                                >
                                    <Pause class="mr-2 h-4 w-4" />
                                    Suspend
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="item.status === 'suspended'"
                                    @click="unsuspendWallet(item)"
                                    class="text-green-600"
                                >
                                    <Play class="mr-2 h-4 w-4" />
                                    Unsuspend
                                </DropdownMenuItem>
                                <DropdownMenuSeparator v-if="item.status !== 'inactive'" />
                                <DropdownMenuItem
                                    v-if="item.status !== 'inactive'"
                                    @click="deactivateWallet(item)"
                                    class="text-red-600"
                                >
                                    <Ban class="mr-2 h-4 w-4" />
                                    Deactivate
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </template>
            </TableReusable>
        </div>
    </AppLayout>
</template>
