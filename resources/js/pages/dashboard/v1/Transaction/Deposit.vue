<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Modal } from 'momentum-modal';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ArrowDownCircle, AlertCircle } from 'lucide-vue-next';
import type { DepositProps } from '@wallets/Types';

const props = defineProps<DepositProps>();

const form = useForm({
    amount: '',
    description: '',
    external_reference: '',
    payment_method: '',
});

const paymentMethods = [
    { value: 'cash', label: 'Cash' },
    { value: 'bank_transfer', label: 'Bank Transfer' },
    { value: 'card', label: 'Card' },
    { value: 'crypto', label: 'Crypto' },
    { value: 'other', label: 'Other' },
];

const formatCurrency = (amount: number, currency: string = 'USD') => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency,
    }).format(amount);
};

const isFormValid = computed(() => {
    return form.amount && parseFloat(form.amount) > 0;
});

const handleSubmit = () => {
    form.post(`/dashboard/wallets/${props.wallet.id}/transactions/deposit`);
};

const handlePaymentMethodChange = (value: string | number | boolean | bigint | Record<string, unknown> | null | undefined) => {
    form.payment_method = String(value || '');
};
</script>

<template>
    <Modal>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-green-100 p-2">
                    <ArrowDownCircle class="h-5 w-5 text-green-600" />
                </div>
                <div>
                    <h2 class="text-lg font-semibold">Deposit Funds</h2>
                    <p class="text-sm text-muted-foreground">
                        Add funds to wallet {{ wallet.wallet_number }}
                    </p>
                </div>
            </div>

            <!-- Wallet Info -->
            <div class="rounded-lg border bg-muted/50 p-4">
                <div class="flex justify-between">
                    <span class="text-sm text-muted-foreground">Current Balance</span>
                    <span class="font-semibold">{{ formatCurrency(wallet.balance, wallet.currency) }}</span>
                </div>
            </div>

            <!-- Warning if wallet cannot transact -->
            <div v-if="!wallet.can_transact" class="flex items-center gap-2 rounded-lg border border-yellow-200 bg-yellow-50 p-4">
                <AlertCircle class="h-5 w-5 text-yellow-600" />
                <p class="text-sm text-yellow-800">
                    This wallet is currently {{ wallet.status }} and cannot accept deposits.
                </p>
            </div>

            <!-- Form -->
            <form v-else @submit.prevent="handleSubmit" class="space-y-4">
                <div class="space-y-2">
                    <Label for="amount">Amount *</Label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground">
                            {{ wallet.currency }}
                        </span>
                        <Input
                            id="amount"
                            v-model="form.amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            placeholder="0.00"
                            class="pl-14"
                            :disabled="form.processing"
                        />
                    </div>
                    <p v-if="form.errors.amount" class="text-sm text-destructive">
                        {{ form.errors.amount }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="payment_method">Payment Method</Label>
                    <Select
                        :model-value="form.payment_method"
                        @update:model-value="handlePaymentMethodChange"
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Select payment method" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="method in paymentMethods"
                                :key="method.value"
                                :value="method.value"
                            >
                                {{ method.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div class="space-y-2">
                    <Label for="external_reference">External Reference</Label>
                    <Input
                        id="external_reference"
                        v-model="form.external_reference"
                        placeholder="e.g., Bank transaction ID"
                        :disabled="form.processing"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="description">Description</Label>
                    <Textarea
                        id="description"
                        v-model="form.description"
                        placeholder="Optional description..."
                        rows="2"
                        :disabled="form.processing"
                    />
                </div>

                <!-- New Balance Preview -->
                <div v-if="form.amount && parseFloat(form.amount) > 0" class="rounded-lg border bg-green-50 p-4">
                    <div class="flex justify-between">
                        <span class="text-sm text-muted-foreground">New Balance</span>
                        <span class="font-semibold text-green-600">
                            {{ formatCurrency(wallet.balance + parseFloat(form.amount), wallet.currency) }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-2 pt-4">
                    <Button type="button" variant="outline" @click="$emit('close')">
                        Cancel
                    </Button>
                    <Button
                        type="submit"
                        :disabled="!isFormValid || form.processing"
                    >
                        <ArrowDownCircle class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Processing...' : 'Deposit' }}
                    </Button>
                </div>
            </form>
        </div>
    </Modal>
</template>
