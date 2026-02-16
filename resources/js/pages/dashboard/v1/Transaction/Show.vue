<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import {
    ArrowDownCircle,
    ArrowUpCircle,
    ArrowLeftCircle,
    ArrowRightCircle,
    ArrowLeft,
    CreditCard,
    RotateCcw,
    Percent,
    Settings,
    Clock,
    CheckCircle,
    XCircle,
    AlertTriangle,
    ExternalLink,
    Copy,
    Wallet,
    Calendar,
    DollarSign,
    Hash,
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { TransactionShowProps } from '@wallets/Types';

const props = defineProps<TransactionShowProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Wallets', href: '/dashboard/wallets' },
    { title: props.wallet.wallet_number, href: `/dashboard/wallets/${props.wallet.id}` },
    { title: 'Transactions', href: `/dashboard/wallets/${props.wallet.id}/transactions` },
    { title: props.transaction.reference, href: `/dashboard/wallets/${props.wallet.id}/transactions/${props.transaction.id}` },
];

const showReverseDialog = ref(false);
const showCancelDialog = ref(false);

// Format currency
const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount);
};

// Format date
const formatDate = (date: string | null) => {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
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
            return ArrowDownCircle;
        case 'withdrawal':
            return ArrowUpCircle;
        case 'transfer_in':
            return ArrowLeftCircle;
        case 'transfer_out':
            return ArrowRightCircle;
        case 'payment':
            return CreditCard;
        case 'refund':
            return RotateCcw;
        case 'fee':
            return Percent;
        case 'adjustment':
            return Settings;
        default:
            return DollarSign;
    }
};

// Copy to clipboard
const copyToClipboard = async (text: string) => {
    await navigator.clipboard.writeText(text);
};

// Reverse transaction
const handleReverse = () => {
    router.post(`/dashboard/wallets/${props.wallet.id}/transactions/${props.transaction.id}/reverse`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            showReverseDialog.value = false;
        },
    });
};

// Cancel transaction
const handleCancel = () => {
    router.post(`/dashboard/wallets/${props.wallet.id}/transactions/${props.transaction.id}/cancel`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            showCancelDialog.value = false;
        },
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Transaction ${transaction.reference}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="ghost" size="icon" as-child>
                        <Link :href="`/dashboard/wallets/${wallet.id}/transactions`">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">Transaction Details</h1>
                            <Badge :variant="getStatusVariant(transaction.status)">
                                <component :is="getStatusIcon(transaction.status)" class="mr-1 h-3 w-3" />
                                {{ transaction.status_label }}
                            </Badge>
                        </div>
                        <p class="text-muted-foreground">{{ transaction.reference }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <!-- Reverse button -->
                    <AlertDialog v-model:open="showReverseDialog">
                        <AlertDialogTrigger as-child>
                            <Button
                                v-if="transaction.can_reverse"
                                variant="outline"
                            >
                                <RotateCcw class="mr-2 h-4 w-4" />
                                Reverse
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Reverse Transaction</AlertDialogTitle>
                                <AlertDialogDescription>
                                    Are you sure you want to reverse this transaction? This will create a new transaction
                                    to undo the original transaction and cannot be undone.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction @click="handleReverse">Reverse</AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>

                    <!-- Cancel button -->
                    <AlertDialog v-model:open="showCancelDialog">
                        <AlertDialogTrigger as-child>
                            <Button
                                v-if="transaction.can_cancel"
                                variant="destructive"
                            >
                                <XCircle class="mr-2 h-4 w-4" />
                                Cancel
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>Cancel Transaction</AlertDialogTitle>
                                <AlertDialogDescription>
                                    Are you sure you want to cancel this pending transaction? This action cannot be undone.
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Keep</AlertDialogCancel>
                                <AlertDialogAction variant="destructive" @click="handleCancel">Cancel Transaction</AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <!-- Main Info Card -->
                <Card class="md:col-span-2">
                    <CardHeader>
                        <div class="flex items-center gap-4">
                            <div
                                :class="[
                                    'rounded-full p-3',
                                    transaction.is_credit ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600',
                                ]"
                            >
                                <component :is="getTypeIcon(transaction.type)" class="h-6 w-6" />
                            </div>
                            <div>
                                <CardTitle class="text-lg">{{ transaction.type_label }}</CardTitle>
                                <CardDescription>{{ transaction.description || 'No description' }}</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Amount Section -->
                        <div class="rounded-lg border p-4">
                            <div class="grid gap-4 sm:grid-cols-3">
                                <div>
                                    <p class="text-sm text-muted-foreground">Amount</p>
                                    <p
                                        :class="[
                                            'text-2xl font-bold',
                                            transaction.is_credit ? 'text-green-600' : 'text-red-600',
                                        ]"
                                    >
                                        {{ transaction.is_credit ? '+' : '-' }}{{ formatCurrency(transaction.amount, transaction.currency) }}
                                    </p>
                                </div>
                                <div v-if="transaction.fee > 0">
                                    <p class="text-sm text-muted-foreground">Fee</p>
                                    <p class="text-lg font-medium text-orange-600">
                                        -{{ formatCurrency(transaction.fee, transaction.currency) }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Net Amount</p>
                                    <p
                                        :class="[
                                            'text-lg font-medium',
                                            transaction.signed_amount >= 0 ? 'text-green-600' : 'text-red-600',
                                        ]"
                                    >
                                        {{ transaction.signed_amount >= 0 ? '+' : '' }}{{ formatCurrency(transaction.net_amount, transaction.currency) }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Balance Change -->
                        <div class="rounded-lg border p-4">
                            <h4 class="mb-3 font-medium">Balance Change</h4>
                            <div class="flex items-center justify-between">
                                <div class="text-center">
                                    <p class="text-sm text-muted-foreground">Before</p>
                                    <p class="text-lg font-medium">{{ formatCurrency(transaction.balance_before, transaction.currency) }}</p>
                                </div>
                                <div class="flex items-center px-4">
                                    <div class="h-px flex-1 bg-border"></div>
                                    <span
                                        :class="[
                                            'mx-2 rounded-full px-3 py-1 text-sm font-medium',
                                            transaction.is_credit ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600',
                                        ]"
                                    >
                                        {{ transaction.is_credit ? '+' : '-' }}{{ formatCurrency(Math.abs(transaction.signed_amount), transaction.currency) }}
                                    </span>
                                    <div class="h-px flex-1 bg-border"></div>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-muted-foreground">After</p>
                                    <p class="text-lg font-medium">{{ formatCurrency(transaction.balance_after, transaction.currency) }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Details Grid -->
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-sm text-muted-foreground">Reference</p>
                                <div class="flex items-center gap-2">
                                    <code class="rounded bg-muted px-2 py-1 text-sm font-mono">{{ transaction.reference }}</code>
                                    <Button variant="ghost" size="icon" class="h-6 w-6" @click="copyToClipboard(transaction.reference)">
                                        <Copy class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                            <div v-if="transaction.external_reference">
                                <p class="text-sm text-muted-foreground">External Reference</p>
                                <div class="flex items-center gap-2">
                                    <code class="rounded bg-muted px-2 py-1 text-sm font-mono">{{ transaction.external_reference }}</code>
                                    <Button variant="ghost" size="icon" class="h-6 w-6" @click="copyToClipboard(transaction.external_reference!)">
                                        <Copy class="h-3 w-3" />
                                    </Button>
                                </div>
                            </div>
                            <div v-if="transaction.payment_method">
                                <p class="text-sm text-muted-foreground">Payment Method</p>
                                <p class="font-medium capitalize">{{ transaction.payment_method }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-muted-foreground">Currency</p>
                                <p class="font-medium">{{ transaction.currency }}</p>
                            </div>
                        </div>

                        <!-- Related Wallet (for transfers) -->
                        <div v-if="transaction.related_wallet" class="rounded-lg border p-4">
                            <h4 class="mb-3 font-medium">
                                {{ transaction.is_credit ? 'From Wallet' : 'To Wallet' }}
                            </h4>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full bg-primary/10 p-2">
                                        <Wallet class="h-4 w-4 text-primary" />
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ transaction.related_wallet.wallet_number }}</p>
                                    </div>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/dashboard/wallets/${transaction.related_wallet.id}`">
                                        <ExternalLink class="mr-2 h-3 w-3" />
                                        View Wallet
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <!-- Reversal Info -->
                        <div v-if="transaction.is_reversed && transaction.reversal_transaction" class="rounded-lg border border-purple-200 bg-purple-50 p-4">
                            <h4 class="mb-2 font-medium text-purple-700">This transaction was reversed</h4>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-purple-600">Reversal Reference</p>
                                    <code class="rounded bg-purple-100 px-2 py-1 text-sm font-mono text-purple-700">
                                        {{ transaction.reversal_transaction.reference }}
                                    </code>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/dashboard/wallets/${wallet.id}/transactions/${transaction.reversal_transaction.id}`">
                                        <ExternalLink class="mr-2 h-3 w-3" />
                                        View Reversal
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <!-- If this is a reversal transaction -->
                        <div v-if="transaction.reversed_transaction" class="rounded-lg border border-purple-200 bg-purple-50 p-4">
                            <h4 class="mb-2 font-medium text-purple-700">This is a reversal of</h4>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-purple-600">Original Reference</p>
                                    <code class="rounded bg-purple-100 px-2 py-1 text-sm font-mono text-purple-700">
                                        {{ transaction.reversed_transaction.reference }}
                                    </code>
                                </div>
                                <Button variant="outline" size="sm" as-child>
                                    <Link :href="`/dashboard/wallets/${wallet.id}/transactions/${transaction.reversed_transaction.id}`">
                                        <ExternalLink class="mr-2 h-3 w-3" />
                                        View Original
                                    </Link>
                                </Button>
                            </div>
                        </div>

                        <!-- Failure Info -->
                        <div v-if="transaction.failure_reason" class="rounded-lg border border-red-200 bg-red-50 p-4">
                            <h4 class="mb-2 font-medium text-red-700">Failure Reason</h4>
                            <p class="text-sm text-red-600">{{ transaction.failure_reason }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Timestamps Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Timeline</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="rounded-full bg-blue-100 p-2">
                                    <Calendar class="h-4 w-4 text-blue-600" />
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Created</p>
                                    <p class="text-sm font-medium">{{ formatDate(transaction.created_at) }}</p>
                                </div>
                            </div>
                            <div v-if="transaction.processed_at" class="flex items-start gap-3">
                                <div class="rounded-full bg-yellow-100 p-2">
                                    <Clock class="h-4 w-4 text-yellow-600" />
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Processed</p>
                                    <p class="text-sm font-medium">{{ formatDate(transaction.processed_at) }}</p>
                                </div>
                            </div>
                            <div v-if="transaction.completed_at" class="flex items-start gap-3">
                                <div class="rounded-full bg-green-100 p-2">
                                    <CheckCircle class="h-4 w-4 text-green-600" />
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Completed</p>
                                    <p class="text-sm font-medium">{{ formatDate(transaction.completed_at) }}</p>
                                </div>
                            </div>
                            <div v-if="transaction.failed_at" class="flex items-start gap-3">
                                <div class="rounded-full bg-red-100 p-2">
                                    <XCircle class="h-4 w-4 text-red-600" />
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Failed</p>
                                    <p class="text-sm font-medium">{{ formatDate(transaction.failed_at) }}</p>
                                </div>
                            </div>
                            <div v-if="transaction.reversed_at" class="flex items-start gap-3">
                                <div class="rounded-full bg-purple-100 p-2">
                                    <RotateCcw class="h-4 w-4 text-purple-600" />
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Reversed</p>
                                    <p class="text-sm font-medium">{{ formatDate(transaction.reversed_at) }}</p>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Quick Info Card -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="text-lg">Quick Info</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Type</span>
                                <Badge :variant="transaction.is_credit ? 'default' : 'destructive'">
                                    {{ transaction.is_credit ? 'Credit' : 'Debit' }}
                                </Badge>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Final</span>
                                <Badge :variant="transaction.is_final ? 'secondary' : 'outline'">
                                    {{ transaction.is_final ? 'Yes' : 'No' }}
                                </Badge>
                            </div>
                            <Separator />
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Reversed</span>
                                <Badge :variant="transaction.is_reversed ? 'destructive' : 'outline'">
                                    {{ transaction.is_reversed ? 'Yes' : 'No' }}
                                </Badge>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Metadata Card -->
                    <Card v-if="transaction.metadata && Object.keys(transaction.metadata).length > 0">
                        <CardHeader>
                            <CardTitle class="text-lg">Metadata</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <pre class="overflow-auto rounded bg-muted p-3 text-xs">{{ JSON.stringify(transaction.metadata, null, 2) }}</pre>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
