<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { SearchableSelect, type SearchableSelectOption } from '@/components/shared/SearchableSelect';
import type { InertiaForm } from '@inertiajs/vue3';
import type { TopUpFormData, TopUpWalletOption } from '../../Types';
import { PAYMENT_METHOD_OPTIONS } from '../../Types';

interface Props {
    mode?: 'create' | 'edit';
    wallets?: TopUpWalletOption[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    wallets: () => [],
});

const model = defineModel<InertiaForm<TopUpFormData>>({ required: true });

const walletOptions = computed<SearchableSelectOption[]>(() =>
    props.wallets.map((w) => ({
        value: w.id,
        label: w.wallet_number,
        description: `${w.customer_name} • ${w.currency}`,
    })),
);

const selectedWallet = computed(() => props.wallets.find((w) => w.id === model.value.wallet_id));

const isManual = computed(() => model.value.payment_method === 'manual');
</script>

<template>
    <div class="space-y-6">
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Top-up Details</h3>
                <p class="text-sm text-muted-foreground">
                    {{ mode === 'create' ? 'Choose a wallet and enter the top-up amount.' : 'Update top-up details.' }}
                </p>
            </div>
            <Separator />

            <div class="grid gap-4 py-4">
                <div class="space-y-2">
                    <Label>Wallet <span class="text-destructive">*</span></Label>
                    <SearchableSelect
                        v-model="model.wallet_id"
                        :options="walletOptions"
                        placeholder="Select a wallet..."
                        search-placeholder="Search wallets..."
                        empty-message="No active wallets found."
                    />
                    <p v-if="model.errors.wallet_id" class="text-sm text-destructive">{{ model.errors.wallet_id }}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="amount">Amount <span class="text-destructive">*</span></Label>
                        <Input
                            id="amount"
                            v-model.number="model.amount"
                            type="number"
                            min="1"
                            step="0.01"
                            placeholder="0.00"
                        />
                        <p v-if="selectedWallet" class="text-xs text-muted-foreground">
                            Currency: {{ selectedWallet.currency }}
                        </p>
                        <p v-if="model.errors.amount" class="text-sm text-destructive">{{ model.errors.amount }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="payment_method">Payment Method <span class="text-destructive">*</span></Label>
                        <Select v-model="model.payment_method">
                            <SelectTrigger>
                                <SelectValue placeholder="Select method" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="opt in PAYMENT_METHOD_OPTIONS"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="model.errors.payment_method" class="text-sm text-destructive">
                            {{ model.errors.payment_method }}
                        </p>
                    </div>
                </div>

                <div v-if="!isManual" class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="provider">Provider</Label>
                        <Input id="provider" v-model="model.provider" placeholder="e.g. stripe, wing" />
                        <p v-if="model.errors.provider" class="text-sm text-destructive">{{ model.errors.provider }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="gateway_reference">Gateway Reference</Label>
                        <Input
                            id="gateway_reference"
                            v-model="model.gateway_reference"
                            placeholder="External transaction ID"
                        />
                        <p v-if="model.errors.gateway_reference" class="text-sm text-destructive">
                            {{ model.errors.gateway_reference }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="model.description"
                        placeholder="Optional note"
                        rows="2"
                    />
                    <p v-if="model.errors.description" class="text-sm text-destructive">{{ model.errors.description }}</p>
                </div>

                <div
                    class="rounded-md border border-dashed p-3 text-sm"
                    :class="isManual ? 'bg-green-50 text-green-900' : 'bg-yellow-50 text-yellow-900'"
                >
                    <strong>{{ isManual ? 'Manual top-up:' : 'Gateway top-up:' }}</strong>
                    {{
                        isManual
                            ? 'Wallet will be credited immediately.'
                            : 'Top-up will stay pending until the gateway callback completes it.'
                    }}
                </div>
            </div>
        </div>
    </div>
</template>
