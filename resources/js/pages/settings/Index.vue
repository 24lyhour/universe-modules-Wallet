<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Wallet, Settings2, Save, Hash, Calendar, Shuffle, DollarSign } from 'lucide-vue-next';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import type { BreadcrumbItem } from '@/types';

interface WalletSettings {
    id_prefix: string;
    id_padding: number;
    number_prefix: string;
    number_separator: string;
    number_date_format: string;
    number_random_length: number;
    default_currency: string;
}

interface Props {
    walletSettings: WalletSettings;
}

const props = defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/dashboard/settings' },
    { title: 'Wallet Settings', href: '/dashboard/settings/wallet' },
];

// Wallet settings form
const form = useForm({
    id_prefix: props.walletSettings?.id_prefix || 'W',
    id_padding: props.walletSettings?.id_padding || 8,
    number_prefix: props.walletSettings?.number_prefix || 'WLT',
    number_separator: props.walletSettings?.number_separator || '-',
    number_date_format: props.walletSettings?.number_date_format || 'Ymd',
    number_random_length: props.walletSettings?.number_random_length || 5,
    default_currency: props.walletSettings?.default_currency || 'USD',
});

// Generate preview wallet ID and number
const previewWalletId = computed(() => {
    const padding = Math.max(4, Math.min(12, form.id_padding || 8));
    return form.id_prefix + '1'.padStart(padding, '0');
});

const previewWalletNumber = computed(() => {
    const date = new Date();
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    let dateStr = '';
    switch (form.number_date_format) {
        case 'Ymd':
            dateStr = `${year}${month}${day}`;
            break;
        case 'Y-m-d':
            dateStr = `${year}-${month}-${day}`;
            break;
        case 'dmy':
            dateStr = `${day}${month}${year}`;
            break;
        case 'd-m-Y':
            dateStr = `${day}-${month}-${year}`;
            break;
        case 'mdy':
            dateStr = `${month}${day}${year}`;
            break;
        default:
            dateStr = `${year}${month}${day}`;
    }

    const randomLength = Math.max(3, Math.min(10, form.number_random_length || 5));
    const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let randomStr = '';
    for (let i = 0; i < randomLength; i++) {
        randomStr += chars.charAt(Math.floor(Math.random() * chars.length));
    }

    return `${form.number_prefix}${form.number_separator}${dateStr}${form.number_separator}${randomStr}`;
});

const handleSave = () => {
    form.patch('/dashboard/settings/wallet', {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('Wallet settings saved successfully.');
        },
        onError: () => {
            toast.error('Failed to save wallet settings.');
        },
    });
};

const dateFormatOptions = [
    { value: 'Ymd', label: 'YYYYMMDD', example: '20260216' },
    { value: 'Y-m-d', label: 'YYYY-MM-DD', example: '2026-02-16' },
    { value: 'dmy', label: 'DDMMYYYY', example: '16022026' },
    { value: 'd-m-Y', label: 'DD-MM-YYYY', example: '16-02-2026' },
    { value: 'mdy', label: 'MMDDYYYY', example: '02162026' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Wallet Settings" />

        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-bold tracking-tight flex items-center gap-2">
                    <Wallet class="h-6 w-6" />
                    Wallet Settings
                </h1>
                <p class="text-muted-foreground">
                    Configure wallet ID and number generation formats
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Wallet ID Configuration -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Hash class="h-5 w-5" />
                            Wallet ID Format
                        </CardTitle>
                        <CardDescription>
                            Configure how wallet IDs are generated (e.g., W00000001)
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="id_prefix">ID Prefix</Label>
                            <Input
                                id="id_prefix"
                                v-model="form.id_prefix"
                                placeholder="W"
                                maxlength="5"
                            />
                            <p class="text-xs text-muted-foreground">
                                Prefix for wallet IDs (e.g., W, WID, WAL)
                            </p>
                            <p v-if="form.errors.id_prefix" class="text-sm text-destructive">
                                {{ form.errors.id_prefix }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="id_padding">ID Padding (Number of Digits)</Label>
                            <Input
                                id="id_padding"
                                v-model.number="form.id_padding"
                                type="number"
                                min="4"
                                max="12"
                            />
                            <p class="text-xs text-muted-foreground">
                                Number of digits after prefix (4-12)
                            </p>
                            <p v-if="form.errors.id_padding" class="text-sm text-destructive">
                                {{ form.errors.id_padding }}
                            </p>
                        </div>

                        <Separator />

                        <div class="rounded-md bg-muted p-4">
                            <p class="text-sm font-medium mb-2">Preview</p>
                            <code class="text-xl font-mono text-primary">{{ previewWalletId }}</code>
                        </div>
                    </CardContent>
                </Card>

                <!-- Wallet Number Configuration -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Settings2 class="h-5 w-5" />
                            Wallet Number Format
                        </CardTitle>
                        <CardDescription>
                            Configure how wallet numbers are generated (e.g., WLT-20260216-A3B5C)
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="number_prefix">Number Prefix</Label>
                                <Input
                                    id="number_prefix"
                                    v-model="form.number_prefix"
                                    placeholder="WLT"
                                    maxlength="10"
                                />
                                <p v-if="form.errors.number_prefix" class="text-sm text-destructive">
                                    {{ form.errors.number_prefix }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="number_separator">Separator</Label>
                                <Input
                                    id="number_separator"
                                    v-model="form.number_separator"
                                    placeholder="-"
                                    maxlength="1"
                                />
                                <p v-if="form.errors.number_separator" class="text-sm text-destructive">
                                    {{ form.errors.number_separator }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="number_date_format">
                                <span class="flex items-center gap-1">
                                    <Calendar class="h-4 w-4" />
                                    Date Format
                                </span>
                            </Label>
                            <select
                                id="number_date_format"
                                v-model="form.number_date_format"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                            >
                                <option
                                    v-for="option in dateFormatOptions"
                                    :key="option.value"
                                    :value="option.value"
                                >
                                    {{ option.label }} ({{ option.example }})
                                </option>
                            </select>
                            <p v-if="form.errors.number_date_format" class="text-sm text-destructive">
                                {{ form.errors.number_date_format }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="number_random_length">
                                <span class="flex items-center gap-1">
                                    <Shuffle class="h-4 w-4" />
                                    Random String Length
                                </span>
                            </Label>
                            <Input
                                id="number_random_length"
                                v-model.number="form.number_random_length"
                                type="number"
                                min="3"
                                max="10"
                            />
                            <p class="text-xs text-muted-foreground">
                                Length of random alphanumeric string (3-10)
                            </p>
                            <p v-if="form.errors.number_random_length" class="text-sm text-destructive">
                                {{ form.errors.number_random_length }}
                            </p>
                        </div>

                        <Separator />

                        <div class="rounded-md bg-muted p-4">
                            <p class="text-sm font-medium mb-2">Preview</p>
                            <code class="text-xl font-mono text-primary">{{ previewWalletNumber }}</code>
                        </div>
                    </CardContent>
                </Card>

                <!-- Default Currency -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <DollarSign class="h-5 w-5" />
                            Default Currency
                        </CardTitle>
                        <CardDescription>
                            Set the default currency for new wallets
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <Label for="default_currency">Currency Code</Label>
                            <Input
                                id="default_currency"
                                v-model="form.default_currency"
                                placeholder="USD"
                                maxlength="3"
                                class="uppercase"
                            />
                            <p class="text-xs text-muted-foreground">
                                3-letter ISO currency code (e.g., USD, EUR, KHR, GBP)
                            </p>
                            <p v-if="form.errors.default_currency" class="text-sm text-destructive">
                                {{ form.errors.default_currency }}
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Save Button -->
            <div class="flex justify-end">
                <Button
                    size="lg"
                    @click="handleSave"
                    :disabled="form.processing"
                >
                    <Save class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Saving...' : 'Save Settings' }}
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
