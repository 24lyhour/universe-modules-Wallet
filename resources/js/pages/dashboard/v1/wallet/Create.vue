<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { WalletsForm } from '@wallets/Components';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import type { WalletFormData, WalletCreateProps } from '@wallets/Types';

const props = defineProps<WalletCreateProps>();

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
    customer_id: null,
    balance: 0,
    locked_amount: 0,
    currency: 'USD',
    status: 'active',
    description: '',
});

const isFormInvalid = computed(() => {
    return form.customer_id === null;
});

const handleSubmit = () => {
    form.post('/dashboard/wallets', {
        onSuccess: () => {
            toast.success('Wallet created successfully.');
            setTimeout(() => {
                close();
                redirect();
            }, 100);
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
        title="Create Wallet"
        description="Add a new wallet to the system"
        mode="create"
        size="lg"
        submit-text="Create Wallet"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <WalletsForm v-model="form" :customers="props.customers" mode="create" />
    </ModalForm>
</template>
