<script setup lang="ts">
import { computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { SearchableSelect, type SearchableSelectOption } from '@/components/shared/SearchableSelect';
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

// Transform customers to SearchableSelect options
const customerOptions = computed<SearchableSelectOption[]>(() => {
    return props.customers.map((customer) => ({
        value: customer.id,
        label: customer.name,
        description: customer.email || undefined,
    }));
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
                    <SearchableSelect
                        v-model="model.customer_id"
                        :options="customerOptions"
                        placeholder="Select a customer..."
                        search-placeholder="Search customers..."
                        empty-message="No customers found."
                    />
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
