<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    ChartContainer,
    ChartTooltip,
    ChartCrosshair,
} from '@/components/ui/chart';
import { Donut } from '@unovis/ts';
import {
    VisSingleContainer,
    VisDonut,
    VisXYContainer,
    VisGroupedBar,
    VisAxis,
    VisArea,
    VisLine,
} from '@unovis/vue';
import {
    Wallet,
    DollarSign,
    Lock,
    TrendingUp,
    TrendingDown,
    RefreshCw,
    CheckCircle,
    XCircle,
    AlertTriangle,
    PieChart,
    BarChart3,
    Activity,
} from 'lucide-vue-next';
import type { ChartConfig } from '@/components/ui/chart';

// Types
export interface WalletMetrics {
    total: number;
    active: number;
    inactive: number;
    suspended: number;
    totalBalance: number;
    totalLocked: number;
    averageBalance: number;
    growthPercent: number;
}

export interface BalanceTrendPoint {
    label: string;
    value: number;
    balance: number;
}

export interface BalanceDistribution {
    range: string;
    count: number;
}

export interface WalletWidgetProps {
    metrics: WalletMetrics;
    balanceTrend?: BalanceTrendPoint[];
    balanceDistribution?: BalanceDistribution[];
    loading?: boolean;
    showStats?: boolean;
    showArea?: boolean;
    showDonut?: boolean;
    showBar?: boolean;
}

const props = withDefaults(defineProps<WalletWidgetProps>(), {
    loading: false,
    showStats: true,
    showArea: true,
    showDonut: true,
    showBar: true,
    balanceTrend: () => [],
    balanceDistribution: () => [],
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

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(num);
};

const formatPercent = (num: number) => {
    return `${num.toFixed(1)}%`;
};

// Chart configs
const statusChartConfig: ChartConfig = {
    active: { label: 'Active', color: 'var(--chart-2)' },
    inactive: { label: 'Inactive', color: 'var(--chart-4)' },
    suspended: { label: 'Suspended', color: 'var(--chart-3)' },
};

const balanceChartConfig: ChartConfig = {
    balance: { label: 'Total Balance', color: 'var(--chart-1)' },
};

const barChartConfig: ChartConfig = {
    available: { label: 'Available', color: 'var(--chart-2)' },
    locked: { label: 'Locked', color: 'var(--chart-4)' },
};

// Generate mock balance trend data if not provided
const balanceTrendData = computed<BalanceTrendPoint[]>(() => {
    if (props.balanceTrend && props.balanceTrend.length > 0) {
        return props.balanceTrend;
    }
    // Generate mock data based on current balance
    const baseBalance = props.metrics.totalBalance;
    const data: BalanceTrendPoint[] = [];
    for (let i = 5; i >= 0; i--) {
        const date = new Date();
        date.setMonth(date.getMonth() - i);
        const variation = 1 - (i * 0.05) + (Math.random() * 0.1 - 0.05);
        data.push({
            label: date.toLocaleDateString('en-US', { month: 'short' }),
            value: i,
            balance: Math.round(baseBalance * variation),
        });
    }
    return data;
});

// Donut chart data for status distribution
const statusDonutData = computed(() => [
    { status: 'active', label: 'Active', value: props.metrics.active, fill: 'var(--color-active)' },
    { status: 'suspended', label: 'Suspended', value: props.metrics.suspended, fill: 'var(--color-suspended)' },
    { status: 'inactive', label: 'Inactive', value: props.metrics.inactive, fill: 'var(--color-inactive)' },
]);

// Bar chart data for balance vs locked
const balanceBarData = computed(() => [
    { label: 'Available', value: props.metrics.totalBalance - props.metrics.totalLocked },
    { label: 'Locked', value: props.metrics.totalLocked },
]);

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
        label: 'Suspended',
        value: props.metrics.suspended,
        icon: AlertTriangle,
        color: 'text-yellow-600',
        bgColor: 'bg-yellow-100',
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
]);

const handleRefresh = () => {
    emit('refresh');
};
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold tracking-tight">Wallet Overview</h2>
                <p class="text-sm text-muted-foreground">Customer wallet statistics and balances</p>
            </div>
            <Button variant="outline" size="icon" @click="handleRefresh" :disabled="loading">
                <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
            </Button>
        </div>

        <!-- Stats Grid -->
        <div v-if="showStats" class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            <Card v-for="stat in stats" :key="stat.label">
                <CardContent class="flex items-center gap-4 pt-6">
                    <div :class="['rounded-full p-3', stat.bgColor]">
                        <component :is="stat.icon" :class="['h-5 w-5', stat.color]" />
                    </div>
                    <div>
                        <p class="text-sm text-muted-foreground">{{ stat.label }}</p>
                        <p class="text-2xl font-bold">{{ stat.value }}</p>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Growth indicator -->
        <Card v-if="showStats && props.metrics.growthPercent !== undefined">
            <CardContent class="flex items-center gap-4 pt-6">
                <Badge :variant="props.metrics.growthPercent >= 0 ? 'default' : 'destructive'" class="px-3 py-1">
                    <component
                        :is="props.metrics.growthPercent >= 0 ? TrendingUp : TrendingDown"
                        class="mr-1 h-4 w-4"
                    />
                    {{ Math.abs(props.metrics.growthPercent).toFixed(1) }}%
                </Badge>
                <span class="text-sm text-muted-foreground">
                    {{ props.metrics.growthPercent >= 0 ? 'Growth' : 'Decrease' }} compared to last month
                </span>
            </CardContent>
        </Card>

        <!-- Balance Trend Area Chart -->
        <Card v-if="showArea">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Activity class="h-5 w-5" />
                    Balance Trend
                </CardTitle>
                <CardDescription>Total wallet balance over time</CardDescription>
            </CardHeader>
            <CardContent>
                <ChartContainer :config="balanceChartConfig" class="h-[280px]" cursor>
                    <VisXYContainer :data="balanceTrendData" :margin="{ top: 10, bottom: 10 }">
                        <VisArea
                            :x="(_: BalanceTrendPoint, i: number) => i"
                            :y="(d: BalanceTrendPoint) => d.balance"
                            :color="balanceChartConfig.balance.color"
                            :opacity="0.4"
                        />
                        <VisLine
                            :x="(_: BalanceTrendPoint, i: number) => i"
                            :y="(d: BalanceTrendPoint) => d.balance"
                            :color="balanceChartConfig.balance.color"
                            :line-width="2"
                        />
                        <VisAxis
                            type="x"
                            :tick-line="false"
                            :domain-line="false"
                            :grid-line="false"
                            :tick-format="(i: number) => balanceTrendData[i]?.label || ''"
                        />
                        <VisAxis
                            type="y"
                            :num-ticks="5"
                            :tick-line="false"
                            :domain-line="false"
                            :tick-format="(v: number) => formatCurrency(v)"
                        />
                        <ChartTooltip />
                        <ChartCrosshair
                            :template="(d: BalanceTrendPoint) => `<div class='border-border/50 bg-background min-w-32 rounded-lg border px-2.5 py-1.5 text-xs shadow-xl'><div class='font-medium'>${d.label}</div><div class='text-muted-foreground'>${formatCurrency(d.balance)}</div></div>`"
                            :color="balanceChartConfig.balance.color"
                        />
                    </VisXYContainer>
                </ChartContainer>
            </CardContent>
        </Card>

        <!-- Charts Row -->
        <div v-if="showDonut || showBar" class="grid gap-6 lg:grid-cols-2">
            <!-- Status Distribution Donut Chart -->
            <Card v-if="showDonut">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <PieChart class="h-5 w-5" />
                        Wallet Status Distribution
                    </CardTitle>
                    <CardDescription>Active, Suspended, and Inactive wallets</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-6 lg:grid-cols-2">
                        <ChartContainer
                            :config="statusChartConfig"
                            class="h-[200px]"
                            :style="{
                                '--vis-donut-central-label-font-size': 'var(--text-2xl)',
                                '--vis-donut-central-label-font-weight': 'var(--font-weight-bold)',
                                '--vis-donut-central-label-text-color': 'var(--foreground)',
                                '--vis-donut-central-sub-label-text-color': 'var(--muted-foreground)',
                            }"
                        >
                            <VisSingleContainer :data="statusDonutData" :margin="{ top: 10, bottom: 10 }">
                                <VisDonut
                                    :value="(d: any) => d.value"
                                    :color="(d: any) => statusChartConfig[d.status as keyof typeof statusChartConfig]?.color"
                                    :arc-width="40"
                                    :pad-angle="0.02"
                                    :corner-radius="4"
                                    :central-label="props.metrics.total.toLocaleString()"
                                    central-sub-label="Total"
                                />
                                <ChartTooltip
                                    :triggers="{
                                        [Donut.selectors.segment]: (d: any) => `<div class='border-border/50 bg-background min-w-32 rounded-lg border px-2.5 py-1.5 text-xs shadow-xl'><div class='flex items-center gap-2'><span class='h-2 w-2 rounded-full' style='background-color: ${statusChartConfig[d.status as keyof typeof statusChartConfig]?.color}'></span><span class='font-medium'>${d.label}</span></div><div class='text-muted-foreground'>${d.value.toLocaleString()} wallets</div></div>`,
                                    }"
                                />
                            </VisSingleContainer>
                        </ChartContainer>

                        <!-- Legend -->
                        <div class="flex flex-col justify-center space-y-4">
                            <div
                                v-for="item in statusDonutData"
                                :key="item.label"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-2">
                                    <span
                                        class="h-3 w-3 rounded-full"
                                        :class="{
                                            'bg-chart-2': item.status === 'active',
                                            'bg-chart-3': item.status === 'suspended',
                                            'bg-chart-4': item.status === 'inactive',
                                        }"
                                    ></span>
                                    <span class="text-sm">{{ item.label }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ formatNumber(item.value) }}</span>
                                    <Badge
                                        :variant="item.status === 'active' ? 'default' : item.status === 'suspended' ? 'outline' : 'secondary'"
                                        class="text-xs"
                                    >
                                        {{ formatPercent((item.value / metrics.total) * 100) }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Balance Distribution Bar Chart -->
            <Card v-if="showBar">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <BarChart3 class="h-5 w-5" />
                        Balance Overview
                    </CardTitle>
                    <CardDescription>Available vs Locked balance</CardDescription>
                </CardHeader>
                <CardContent>
                    <ChartContainer :config="barChartConfig" class="h-[200px]" cursor>
                        <VisXYContainer :data="balanceBarData" :margin="{ left: -24 }" :y-domain="[0, undefined]">
                            <VisGroupedBar
                                :x="(_: any, i: number) => i"
                                :y="(d: any) => d.value"
                                :color="(_: any, i: number) => i === 0 ? 'var(--chart-2)' : 'var(--chart-4)'"
                                :bar-padding="0.3"
                                :rounded-corners="4"
                            />
                            <VisAxis
                                type="x"
                                :tick-line="false"
                                :domain-line="false"
                                :grid-line="false"
                                :tick-format="(i: number) => balanceBarData[i]?.label || ''"
                            />
                            <VisAxis
                                type="y"
                                :num-ticks="4"
                                :tick-line="false"
                                :domain-line="false"
                                :tick-format="(v: number) => formatCurrency(v)"
                            />
                            <ChartTooltip />
                            <ChartCrosshair
                                :template="(d: any) => `<div class='border-border/50 bg-background min-w-32 rounded-lg border px-2.5 py-1.5 text-xs shadow-xl'><div class='font-medium'>${d.label}</div><div class='text-muted-foreground'>${formatCurrency(d.value)}</div></div>`"
                                color="#0000"
                            />
                        </VisXYContainer>
                    </ChartContainer>

                    <!-- Balance Summary -->
                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="rounded-lg border p-3">
                            <p class="text-xs text-muted-foreground">Available Balance</p>
                            <p class="text-lg font-bold text-green-600">
                                {{ formatCurrency(metrics.totalBalance - metrics.totalLocked) }}
                            </p>
                        </div>
                        <div class="rounded-lg border p-3">
                            <p class="text-xs text-muted-foreground">Locked Balance</p>
                            <p class="text-lg font-bold text-orange-600">
                                {{ formatCurrency(metrics.totalLocked) }}
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
