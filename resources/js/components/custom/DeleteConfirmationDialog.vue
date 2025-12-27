<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { trans } from 'laravel-vue-i18n';

defineProps<{
    open: boolean;
    title: string;
    description: string;
    loading?: boolean;
    variant?: 'destructive' | 'default';
}>();

const emit = defineEmits<{
    (e: 'update:open', value: boolean): void;
    (e: 'confirm'): void;
    (e: 'cancel'): void;
}>();

function onConfirm() {
    emit('confirm');
}

function onCancel() {
    emit('update:open', false);
    emit('cancel');
}
</script>

<template>
    <Dialog :open="open" @update:open="(val) => emit('update:open', val)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>
                    {{ description }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter>
                <Button variant="outline" @click="onCancel" :disabled="loading">
                    {{ trans('pages.settings.events.actions.cancel') }}
                </Button>
                <Button :variant="variant || 'destructive'" @click="onConfirm" :disabled="loading">
                    <span v-if="loading">{{ trans('pages.contact.actions.sending') }}</span>
                    <slot v-else>
                        {{ trans('pages.settings.events.actions.delete') }}
                    </slot>
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
