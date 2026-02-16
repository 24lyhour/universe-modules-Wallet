<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Separator } from '@/components/ui/separator';
import type { InertiaForm } from '@inertiajs/vue3';
import type { WalletFormData, CustomerOption } from '../../Types';

interface Props {
    mode?: 'create' | 'edit';
    customers?: CustomerOption[];
}

const props = withDefaults(defineProps<Props>(), {
    mode: 'create',
    customers: () => [],
});

const model = defineModel<InertiaForm<WalletFormData>>({ required: true });

// Computed for customer select
const selectedCustomer = computed({
    get: () => model.value.customer_id?.toString(),
    set: (value: string | undefined) => {
        model.value.customer_id = value ? parseInt(value) : null;
    },
});
</script>

<template>
    <div class="space-y-6">
        <!-- Basic Information Section -->
        <div class="space-y-4">
            <div>
                <h3 class="text-sm font-medium">Wallet Details</h3>
                <p class="text-sm text-muted-foreground">
                    {{ mode === 'create' ? 'Enter the details for the new wallet' : 'Update wallet details' }}
                </p>
            </div>
            <Separator />

            <div class="grid gap-4 py-4">
                <div class="space-y-2">
                    <Label for="customer_id">Customer <span class="text-destructive">*</span></Label>
                    <Select v-model="selectedCustomer">
                        <SelectTrigger id="customer_id">
                            <SelectValue placeholder="Select a customer" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="customer in props.customers"
                                :key="customer.id"
                                :value="customer.id.toString()"
                            >
                                {{ customer.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="model.errors.customer_id" class="text-sm text-destructive">
                        {{ model.errors.customer_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="wallet_number">Wallet Number <span class="text-destructive">*</span></Label>
                    <Input
                        id="wallet_number"
                        v-model="model.wallet_number"
                        placeholder="Enter wallet number (e.g. WAL-12345)"
                    />
                    <p v-if="model.errors.wallet_number" class="text-sm text-destructive">
                        {{ model.errors.wallet_number }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label for="currency">Currency</Label>
                        <Input id="currency" v-model="model.currency" placeholder="USD" />
                        <p v-if="model.errors.currency" class="text-sm text-destructive">
                            {{ model.errors.currency }}
                        </p>
                    </div>
                    <div class="space-y-2">
                        <Label for="balance">Initial Balance</Label>
                        <Input
                            id="balance"
                            v-model.number="model.balance"
                            type="number"
                            placeholder="0.00"
                        />
                        <p v-if="model.errors.balance" class="text-sm text-destructive">
                            {{ model.errors.balance }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Input id="description" v-model="model.description" placeholder="Optional description" />
                    <p v-if="model.errors.description" class="text-sm text-destructive">
                        {{ model.errors.description }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
