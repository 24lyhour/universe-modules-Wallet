<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { TopUpForm } from '@wallets/Components';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import type { TopUpCreateProps, TopUpFormData } from '@wallets/Types';

const props = defineProps<TopUpCreateProps>();

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

const form = useForm<TopUpFormData>({
    wallet_id: null,
    amount: 0,
    payment_method: 'manual',
    provider: '',
    gateway_reference: '',
    description: '',
});

const isFormInvalid = computed(() => {
    return form.wallet_id === null || form.amount <= 0 || !form.payment_method;
});

const isManual = computed(() => form.payment_method === 'manual');

const handleSubmit = () => {
    form.post('/dashboard/topups', {
        onSuccess: () => {
            toast.success(isManual.value ? 'Top-up completed.' : 'Top-up created (pending).');
            setTimeout(() => {
                close();
                redirect();
            }, 100);
        },
        onError: () => {
            toast.error('Failed to create top-up.');
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
        title="New Top-up"
        description="Credit a wallet via a manual or gateway top-up"
        mode="create"
        size="lg"
        submit-text="Create Top-up"
        :loading="form.processing"
        :disabled="isFormInvalid"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <TopUpForm v-model="form" :wallets="props.wallets" mode="create" />
    </ModalForm>
</template>
