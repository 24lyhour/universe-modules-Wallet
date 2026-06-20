<script setup lang="ts">
import { ModalForm } from '@/components/shared';
import { useForm } from '@inertiajs/vue3';
import { useModal } from 'momentum-modal';
import { computed } from 'vue';
import { toast } from 'vue-sonner';
import type { TopUpDeleteProps } from '@wallets/Types';

const props = defineProps<TopUpDeleteProps>();

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
    form.delete(`/dashboard/topups/${props.topup.id}`, {
        onSuccess: () => {
            toast.success('Top-up deleted.');
            close();
            redirect();
        },
        onError: () => {
            toast.error('Failed to delete top-up.');
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
        title="Delete Top-up"
        description="Are you sure you want to delete this top-up? This action cannot be undone."
        mode="delete"
        size="sm"
        submit-text="Delete Top-up"
        variant="destructive"
        :loading="form.processing"
        @submit="handleSubmit"
        @cancel="handleCancel"
    >
        <div class="py-4 space-y-2">
            <p class="text-sm text-muted-foreground">
                Reference: <span class="font-mono font-medium text-foreground">{{ props.topup.reference }}</span>
            </p>
            <p class="text-sm text-muted-foreground">
                Wallet: <span class="font-medium text-foreground">{{ props.wallet_number }}</span>
            </p>
            <p class="text-sm text-muted-foreground">
                Status: <span class="font-medium text-foreground capitalize">{{ props.topup.status }}</span>
            </p>
        </div>
    </ModalForm>
</template>
