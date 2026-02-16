<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Wallet,
    DollarSign,
    Lock,
    TrendingUp,
    TrendingDown,
    RefreshCw,
    CheckCircle,
    XCircle,
} from 'lucide-vue-next';

// Types
export interface WalletMetrics {
    total: number;
    active: number;
    inactive: number;
    totalBalance: number;
    totalLocked: number;
    averageBalance: number;
    growthPercent: number;
}

export interface WalletWidgetProps {
    metrics: WalletMetrics;
    loading?: boolean;
    showStats?: boolean;
}

const props = withDefaults(defineProps<WalletWidgetProps>(), {
    loading: false,
    showStats: true,
});

const emit = defineEmits<{
    (e: 'refresh'): void;
}>();

// Format currency
const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

// Computed stats
const stats = computed(() => [
    {
        label: 'Total Wallets',
        value: props.metrics.total,
        icon: Wallet,
        color: 'text-blue-600',
        bgColor: 'bg-blue-100',
    },
    {
        label: 'Active',
        value: props.metrics.active,
        icon: CheckCircle,
        color: 'text-green-600',
        bgColor: 'bg-green-100',
    },
    {
        label: 'Inactive',
        value: props.metrics.inactive,
        icon: XCircle,
        color: 'text-gray-600',
        bgColor: 'bg-gray-100',
    },
    {
        label: 'Total Balance',
        value: formatCurrency(props.metrics.totalBalance),
        icon: DollarSign,
        color: 'text-emerald-600',
        bgColor: 'bg-emerald-100',
    },
    {
        label: 'Locked Amount',
        value: formatCurrency(props.metrics.totalLocked),
        icon: Lock,
        color: 'text-orange-600',
        bgColor: 'bg-orange-100',
    },
    {
        label: 'Avg Balance',
        value: formatCurrency(props.metrics.averageBalance),
        icon: TrendingUp,
        color: 'text-purple-600',
        bgColor: 'bg-purple-100',
    },
]);

const handleRefresh = () => {
    emit('refresh');
};
</script>

<template>
    <Card class="h-full">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <div>
                <CardTitle class="text-base font-semibold">Wallets Overview</CardTitle>
                <CardDescription>Customer wallet statistics</CardDescription>
            </div>
            <Button variant="ghost" size="icon" @click="handleRefresh" :disabled="loading">
                <RefreshCw :class="['h-4 w-4', loading && 'animate-spin']" />
            </Button>
        </CardHeader>
        <CardContent>
            <div v-if="showStats" class="grid grid-cols-2 gap-4 md:grid-cols-3">
                <div
                    v-for="stat in stats"
                    :key="stat.label"
                    class="flex items-center gap-3 rounded-lg border p-3"
                >
                    <div :class="['rounded-full p-2', stat.bgColor]">
                        <component :is="stat.icon" :class="['h-4 w-4', stat.color]" />
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground">{{ stat.label }}</p>
                        <p class="text-lg font-semibold">{{ stat.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Growth indicator -->
            <div v-if="props.metrics.growthPercent !== undefined" class="mt-4 flex items-center gap-2">
                <Badge :variant="props.metrics.growthPercent >= 0 ? 'default' : 'destructive'">
                    <component
                        :is="props.metrics.growthPercent >= 0 ? TrendingUp : TrendingDown"
                        class="mr-1 h-3 w-3"
                    />
                    {{ Math.abs(props.metrics.growthPercent).toFixed(1) }}%
                </Badge>
                <span class="text-xs text-muted-foreground">
                    {{ props.metrics.growthPercent >= 0 ? 'increase' : 'decrease' }} from last month
                </span>
            </div>
        </CardContent>
    </Card>
</template>
