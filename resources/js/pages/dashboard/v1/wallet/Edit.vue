<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { WalletsForm } from '@wallets/Components';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import type { WalletFormData, WalletEditProps } from '@wallets/Types';

const props = defineProps<WalletEditProps>();

const { show, close, redirect } = useModal();

const isOpen = computed({
    get: () => show.value,
    set: (val: boolean) => {
        if (!val) {
            close();
            redirect();
        }
    },
});

const form = useForm<WalletFormData>({
    customer_id: props.wallet.customer_id,
    wallet_number: props.wallet.wallet_number,
    balance: typeof props.wallet.balance === 'string' ? parseFloat(props.wallet.balance) : props.wallet.balance,
    locked_amount: typeof props.wallet.locked_amount === 'string' ? parseFloat(props.wallet.locked_amount) : props.wallet.locked_amount,
    currency: props.wallet.currency,
    status: props.wallet.status,
    description: props.wallet.description || '',
});

const isFormInvalid = computed(() => {
    return !form.wallet_number || form.customer_id === null;
});

const handleSubmit = () => {
    form.put(`/dashboard/wallets/${props.wallet.id}`, {
        onSuccess: () => {
            toast.success('Wallet updated successfully');
            close();
            redirect();
        },
        onError: (errors) => {
            console.error(errors);
            toast.error('Failed to update wallet');
        },
    });
};

const handleCancel = () => {
    close();
    redirect();
};
</script>

<template>
    <ModalForm
        v-model:open="isOpen"
        title="Edit Wallet"
        description="Update wallet information"
        mode="edit"
        size="lg"
        submit-text="Update Wallet"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <WalletsForm v-model="form" :customers="props.customers" mode="edit" />
    </ModalForm>
</template>
