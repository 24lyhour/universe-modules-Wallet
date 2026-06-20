<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Trash2, Play, Ban, AlertTriangle, ExternalLink } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { TopUpShowProps } from '@wallets/Types';
import { getTopUpStatusVariant } from '@wallets/Types';

const props = defineProps<TopUpShowProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Top-ups', href: '/dashboard/topups' },
    { title: props.topup.reference, href: `/dashboard/topups/${props.topup.id}` },
];

const formatCurrency = (amount: number, currency: string = 'USD') =>
    new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(amount);

const formatDate = (date: string | null) =>
    date
        ? new Date(date).toLocaleDateString('en-US', {
              year: 'numeric',
              month: 'long',
              day: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
          })
        : 'N/A';

const canComplete = props.topup.status === 'pending' || props.topup.status === 'processing';
const canCancel = canComplete;
const canFail = canComplete;
const canDelete = props.topup.status !== 'completed';

const complete = () => router.patch(`/dashboard/topups/${props.topup.id}/complete`, {}, { preserveScroll: true });
const cancel = () => router.patch(`/dashboard/topups/${props.topup.id}/cancel`, {}, { preserveScroll: true });
const fail = () => router.patch(`/dashboard/topups/${props.topup.id}/fail`, {}, { preserveScroll: true });
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Top-up — ${props.topup.reference}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link href="/dashboard/topups">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="font-mono text-2xl font-bold tracking-tight">{{ props.topup.reference }}</h1>
                            <Badge :variant="getTopUpStatusVariant(props.topup.status)" class="capitalize">
                                {{ props.topup.status_label }}
                            </Badge>
                        </div>
                        <p class="text-muted-foreground">Top-up details</p>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button v-if="canComplete" variant="default" @click="complete">
                        <Play class="mr-2 h-4 w-4" />
                        Complete
                    </Button>
                    <Button v-if="canFail" variant="outline" @click="fail">
                        <AlertTriangle class="mr-2 h-4 w-4" />
                        Mark Failed
                    </Button>
                    <Button v-if="canCancel" variant="outline" @click="cancel">
                        <Ban class="mr-2 h-4 w-4" />
                        Cancel
                    </Button>
                    <Button v-if="canDelete" variant="destructive" as-child>
                        <Link :href="`/dashboard/topups/${props.topup.id}/delete`">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Content -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Top-up Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Top-up Information</CardTitle>
                        <CardDescription>Top-up request details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Reference</span>
                            <span class="font-mono font-medium">{{ props.topup.reference }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Amount</span>
                            <span class="font-bold text-lg">
                                {{ formatCurrency(props.topup.amount, props.topup.currency) }}
                            </span>
                        </div>
                        <div v-if="props.topup.fee > 0" class="flex justify-between">
                            <span class="text-muted-foreground">Fee</span>
                            <span>{{ formatCurrency(props.topup.fee, props.topup.currency) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Net Amount</span>
                            <span class="font-medium">
                                {{ formatCurrency(props.topup.net_amount, props.topup.currency) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Payment Method</span>
                            <span class="capitalize">{{ props.topup.payment_method.replace('_', ' ') }}</span>
                        </div>
                        <div v-if="props.topup.provider" class="flex justify-between">
                            <span class="text-muted-foreground">Provider</span>
                            <span>{{ props.topup.provider }}</span>
                        </div>
                        <div v-if="props.topup.gateway_reference" class="flex justify-between">
                            <span class="text-muted-foreground">Gateway Reference</span>
                            <span class="font-mono text-sm">{{ props.topup.gateway_reference }}</span>
                        </div>
                        <div v-if="props.topup.description" class="flex justify-between">
                            <span class="text-muted-foreground">Description</span>
                            <span>{{ props.topup.description }}</span>
                        </div>
                        <div v-if="props.topup.failure_reason" class="flex justify-between">
                            <span class="text-muted-foreground">Failure Reason</span>
                            <span class="text-red-600">{{ props.topup.failure_reason }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Wallet Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Wallet</CardTitle>
                        <CardDescription>Destination wallet</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Wallet Number</span>
                            <Link :href="`/dashboard/wallets/${props.wallet.id}`" class="font-medium hover:underline">
                                {{ props.wallet.wallet_number }}
                            </Link>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Owner</span>
                            <span>{{ props.wallet.customer_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Current Balance</span>
                            <span class="font-bold text-green-600">
                                {{ formatCurrency(props.wallet.balance, props.wallet.currency) }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Linked Transaction -->
                <Card v-if="props.transaction">
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            Linked Transaction
                            <ExternalLink class="h-4 w-4 text-muted-foreground" />
                        </CardTitle>
                        <CardDescription>Created when the top-up was completed</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Reference</span>
                            <Link
                                :href="`/dashboard/wallets/${props.wallet.id}/transactions/${props.transaction.id}`"
                                class="font-mono font-medium hover:underline"
                            >
                                {{ props.transaction.reference }}
                            </Link>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Amount</span>
                            <span>{{ formatCurrency(props.transaction.amount, props.topup.currency) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Balance After</span>
                            <span class="font-medium">
                                {{ formatCurrency(props.transaction.balance_after, props.topup.currency) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Completed At</span>
                            <span>{{ formatDate(props.transaction.completed_at) }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Timestamps -->
                <Card>
                    <CardHeader>
                        <CardTitle>History</CardTitle>
                        <CardDescription>Lifecycle timestamps</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Created</span>
                            <span>{{ formatDate(props.topup.created_at) }}</span>
                        </div>
                        <div v-if="props.topup.completed_at" class="flex justify-between">
                            <span class="text-muted-foreground">Completed</span>
                            <span class="text-green-600">{{ formatDate(props.topup.completed_at) }}</span>
                        </div>
                        <div v-if="props.topup.failed_at" class="flex justify-between">
                            <span class="text-muted-foreground">Failed</span>
                            <span class="text-red-600">{{ formatDate(props.topup.failed_at) }}</span>
                        </div>
                        <div v-if="props.topup.cancelled_at" class="flex justify-between">
                            <span class="text-muted-foreground">Cancelled</span>
                            <span class="text-gray-600">{{ formatDate(props.topup.cancelled_at) }}</span>
                        </div>
                        <div v-if="props.creator" class="flex justify-between">
                            <span class="text-muted-foreground">Created By</span>
                            <span>{{ props.creator.name }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
