<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ArrowLeft, Pencil, Trash2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';
import type { WalletShowProps } from '@wallets/Types';

const props = defineProps<WalletShowProps>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Wallets', href: '/dashboard/wallets' },
    { title: props.wallet.wallet_number, href: `/dashboard/wallets/${props.wallet.id}` },
];

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
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="`Wallet - ${props.wallet.wallet_number}`" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="icon" as-child>
                        <Link href="/dashboard/wallets">
                            <ArrowLeft class="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight">{{ props.wallet.wallet_number }}</h1>
                        <p class="text-muted-foreground">Wallet details and information</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="`/dashboard/wallets/${props.wallet.id}/edit`">
                            <Pencil class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="destructive" as-child>
                        <Link :href="`/dashboard/wallets/${props.wallet.id}/delete`">
                            <Trash2 class="mr-2 h-4 w-4" />
                            Delete
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Content -->
            <div class="grid gap-6 md:grid-cols-2">
                <!-- Wallet Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Wallet Information</CardTitle>
                        <CardDescription>Basic wallet details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Wallet Number</span>
                            <span class="font-medium">{{ props.wallet.wallet_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Status</span>
                            <Badge :variant="props.wallet.status === 'active' ? 'default' : 'secondary'">
                                {{ props.wallet.status }}
                            </Badge>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Currency</span>
                            <span class="font-medium">{{ props.wallet.currency }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Description</span>
                            <span class="font-medium">{{ props.wallet.description || 'N/A' }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Balance Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Balance Information</CardTitle>
                        <CardDescription>Current wallet balance</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Balance</span>
                            <span class="font-medium text-green-600">
                                {{ formatCurrency(props.wallet.balance, props.wallet.currency) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Locked Amount</span>
                            <span class="font-medium text-orange-600">
                                {{ formatCurrency(props.wallet.locked_amount, props.wallet.currency) }}
                            </span>
                        </div>
                        <div class="flex justify-between border-t pt-4">
                            <span class="text-muted-foreground">Available Balance</span>
                            <span class="font-bold text-lg">
                                {{ formatCurrency(props.wallet.balance - props.wallet.locked_amount, props.wallet.currency) }}
                            </span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Owner Info -->
                <Card>
                    <CardHeader>
                        <CardTitle>Owner Information</CardTitle>
                        <CardDescription>Customer details</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Name</span>
                            <span class="font-medium">{{ props.wallet.customer?.name || 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Email</span>
                            <span class="font-medium">{{ props.wallet.customer?.email || 'N/A' }}</span>
                        </div>
                    </CardContent>
                </Card>

                <!-- Timestamps -->
                <Card>
                    <CardHeader>
                        <CardTitle>Timestamps</CardTitle>
                        <CardDescription>Record history</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Created At</span>
                            <span class="font-medium">{{ formatDate(props.wallet.created_at) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-muted-foreground">Updated At</span>
                            <span class="font-medium">{{ formatDate(props.wallet.updated_at) }}</span>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
