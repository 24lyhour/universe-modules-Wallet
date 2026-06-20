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
    CreditCard,
    CheckCircle,
    XCircle,
    Clock,
    DollarSign,
    Eye,
    Trash2,
    MoreHorizontal,
    Play,
    Ban,
    AlertTriangle,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { TopUpIndexProps, TopUp, TopUpStatus, PaginationMeta } from '@wallets/Types';
import { getTopUpStatusVariant } from '@wallets/Types';

const props = defineProps<TopUpIndexProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Top-ups', href: '/dashboard/topups' },
];

const statusFilter = ref(props.filters.status || '');
const walletFilter = ref(props.filters.wallet_id ? String(props.filters.wallet_id) : '');

const columns = [
    { key: 'reference', label: 'Reference', sortable: true },
    { key: 'wallet', label: 'Wallet', sortable: false },
    { key: 'amount', label: 'Amount', sortable: true },
    { key: 'payment_method', label: 'Method', sortable: true },
    { key: 'status', label: 'Status', sortable: true },
    { key: 'created_at', label: 'Created', sortable: true },
];

const actions = [
    {
        icon: Eye,
        label: 'View',
        onClick: (row: TopUp) => router.visit(`/dashboard/topups/${row.id}`),
    },
    {
        icon: Trash2,
        label: 'Delete',
        variant: 'destructive' as const,
        onClick: (row: TopUp) => router.visit(`/dashboard/topups/${row.id}/delete`),
    },
];

const pagination = computed<PaginationMeta>(() => ({
    current_page: props.topups.meta.current_page,
    last_page: props.topups.meta.last_page,
    per_page: props.topups.meta.per_page,
    total: props.topups.meta.total,
}));

const buildQuery = (overrides: Record<string, string | number | undefined> = {}) => ({
    page: pagination.value.current_page,
    per_page: pagination.value.per_page,
    status: statusFilter.value || undefined,
    wallet_id: walletFilter.value || undefined,
    ...overrides,
});

const handlePageChange = (page: number) => {
    router.get('/dashboard/topups', buildQuery({ page }), { preserveState: true, preserveScroll: true });
};

const handlePerPageChange = (perPage: number) => {
    router.get('/dashboard/topups', buildQuery({ page: 1, per_page: perPage }), { preserveState: true, preserveScroll: true });
};

const handleSearch = (search: string) => {
    router.get('/dashboard/topups', buildQuery({ search, page: 1 }), { preserveState: true, preserveScroll: true });
};

const handleStatusFilter = (status: unknown) => {
    const v = String(status || 'all');
    statusFilter.value = v === 'all' ? '' : v;
    router.get('/dashboard/topups', buildQuery({ page: 1 }), { preserveState: true, preserveScroll: true });
};

const handleWalletFilter = (walletId: unknown) => {
    const v = String(walletId || 'all');
    walletFilter.value = v === 'all' ? '' : v;
    router.get('/dashboard/topups', buildQuery({ page: 1 }), { preserveState: true, preserveScroll: true });
};

const completeTopUp = (topup: TopUp) => {
    router.patch(`/dashboard/topups/${topup.id}/complete`, {}, { preserveScroll: true });
};

const cancelTopUp = (topup: TopUp) => {
    router.patch(`/dashboard/topups/${topup.id}/cancel`, {}, { preserveScroll: true });
};

const failTopUp = (topup: TopUp) => {
    router.patch(`/dashboard/topups/${topup.id}/fail`, {}, { preserveScroll: true });
};

const formatCurrency = (amount: number, currency: string = 'USD') =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(amount);

const formatDate = (date: string) =>
    new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

const tableData = computed(() =>
    props.topups.data.map((t) => ({
        ...t,
        amount_raw: t.amount,
        amount: formatCurrency(t.amount, t.currency),
        wallet_number: t.wallet?.wallet_number ?? '-',
        customer_name: t.wallet?.customer?.name ?? '-',
        created_at: formatDate(t.created_at),
    })),
);

const canComplete = (status: TopUpStatus) => status === 'pending' || status === 'processing';
const canCancel = (status: TopUpStatus) => status === 'pending' || status === 'processing';
const canFail = (status: TopUpStatus) => status === 'pending' || status === 'processing';
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Top-ups" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Top-ups</h1>
                    <p class="text-muted-foreground">Track and manage wallet top-up requests</p>
                </div>
                <Button as-child>
                    <Link href="/dashboard/topups/create">
                        <Plus class="mr-2 h-4 w-4" />
                        New Top-up
                    </Link>
                </Button>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-3 lg:grid-cols-6">
                <StatsCard title="Total" :value="props.stats.total" :icon="CreditCard" />
                <StatsCard title="Pending" :value="props.stats.pending" :icon="Clock" variant="warning" />
                <StatsCard title="Completed" :value="props.stats.completed" :icon="CheckCircle" variant="success" />
                <StatsCard title="Failed" :value="props.stats.failed" :icon="XCircle" variant="destructive" />
                <StatsCard
                    title="Completed Amount"
                    :value="formatCurrency(props.stats.total_completed_amount)"
                    :icon="DollarSign"
                    variant="success"
                />
                <StatsCard
                    title="Pending Amount"
                    :value="formatCurrency(props.stats.total_pending_amount)"
                    :icon="DollarSign"
                    variant="warning"
                />
            </div>

            <!-- Table -->
            <TableReusable
                :data="tableData"
                :columns="columns"
                :actions="actions"
                :pagination="pagination"
                :searchable="true"
                search-placeholder="Search by reference, gateway ref, or wallet number..."
                @page-change="handlePageChange"
                @per-page-change="handlePerPageChange"
                @search="handleSearch"
            >
                <template #toolbar>
                    <div class="flex flex-wrap items-center gap-2">
                        <Select :model-value="statusFilter || 'all'" @update:model-value="handleStatusFilter">
                            <SelectTrigger class="w-[160px]">
                                <SelectValue placeholder="All Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Status</SelectItem>
                                <SelectItem
                                    v-for="opt in props.topupStatuses"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <Select :model-value="walletFilter || 'all'" @update:model-value="handleWalletFilter">
                            <SelectTrigger class="w-[200px]">
                                <SelectValue placeholder="All Wallets" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All Wallets</SelectItem>
                                <SelectItem
                                    v-for="w in props.wallets"
                                    :key="w.id"
                                    :value="String(w.id)"
                                >
                                    {{ w.wallet_number }} — {{ w.customer_name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                </template>

                <template #cell-reference="{ item }">
                    <div class="flex flex-col">
                        <Link :href="`/dashboard/topups/${item.id}`" class="font-mono text-sm font-medium hover:underline">
                            {{ item.reference }}
                        </Link>
                        <span v-if="item.gateway_reference" class="text-xs text-muted-foreground">
                            gw: {{ item.gateway_reference }}
                        </span>
                    </div>
                </template>

                <template #cell-wallet="{ item }">
                    <div class="flex flex-col">
                        <span class="font-medium">{{ item.wallet_number }}</span>
                        <span class="text-xs text-muted-foreground">{{ item.customer_name }}</span>
                    </div>
                </template>

                <template #cell-payment_method="{ item }">
                    <div class="flex flex-col">
                        <span class="capitalize">{{ item.payment_method.replace('_', ' ') }}</span>
                        <span v-if="item.provider" class="text-xs text-muted-foreground">{{ item.provider }}</span>
                    </div>
                </template>

                <template #cell-status="{ item }">
                    <div class="flex items-center gap-2">
                        <Badge :variant="getTopUpStatusVariant(item.status)" class="capitalize">
                            {{ item.status_label }}
                        </Badge>
                        <DropdownMenu v-if="canComplete(item.status) || canCancel(item.status) || canFail(item.status)">
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon" class="h-6 w-6">
                                    <MoreHorizontal class="h-3 w-3" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem
                                    v-if="canComplete(item.status)"
                                    class="text-green-600"
                                    @click="completeTopUp(item)"
                                >
                                    <Play class="mr-2 h-4 w-4" />
                                    Complete
                                </DropdownMenuItem>
                                <DropdownMenuItem
                                    v-if="canFail(item.status)"
                                    class="text-red-600"
                                    @click="failTopUp(item)"
                                >
                                    <AlertTriangle class="mr-2 h-4 w-4" />
                                    Mark Failed
                                </DropdownMenuItem>
                                <DropdownMenuSeparator v-if="canCancel(item.status)" />
                                <DropdownMenuItem
                                    v-if="canCancel(item.status)"
                                    class="text-gray-600"
                                    @click="cancelTopUp(item)"
                                >
                                    <Ban class="mr-2 h-4 w-4" />
                                    Cancel
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>
                </template>
            </TableReusable>
        </div>
    </AppLayout>
</template>
