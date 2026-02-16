<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import type { Wallet } from '@wallets/Types';

const props = defineProps<{
    wallet: Wallet;
}>();

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

const form = useForm({});

const handleSubmit = () => {
    form.delete(`/dashboard/wallets/${props.wallet.id}`, {
        onSuccess: () => {
            toast.success('Wallet deleted successfully');
            close();
            redirect();
        },
        onError: (errors) => {
            console.error(errors);
            toast.error('Failed to delete wallet');
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
        title="Delete Wallet"
        description="Are you sure you want to delete this wallet? This action cannot be undone."
        mode="delete"
        size="sm"
        submit-text="Delete Wallet"
        variant="destructive"
        :loading="form.processing"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="py-4">
            <p class="text-sm text-muted-foreground">
                Wallet Number: <span class="font-medium text-foreground">{{ props.wallet.wallet_number }}</span>
            </p>
            <p class="text-sm text-muted-foreground">
                Owner: <span class="font-medium text-foreground">{{ props.wallet.customer?.name }}</span>
            </p>
        </div>
    </ModalForm>
</template>
