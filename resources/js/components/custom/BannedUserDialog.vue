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
import { Ban } from 'lucide-vue-next';
import { onMounted, ref, watch } from 'vue';

const props = defineProps<{
    message: string | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const open = ref(false);
const countdown = ref(10);
let timer: any = null;

const close = () => {
    open.value = false;
    if (timer) clearInterval(timer);
    emit('close');
};

watch(() => props.message, (newVal) => {
    if (newVal) {
        open.value = true;
        countdown.value = 10;
        if (timer) clearInterval(timer);
        timer = setInterval(() => {
            countdown.value--;
            if (countdown.value <= 0) {
                close();
            }
        }, 1000);
    } else {
        open.value = false;
    }
}, { immediate: true });

onMounted(() => {
    if (props.message) {
        open.value = true;
    }
});
</script>

<template>
    <Dialog :open="open" @update:open="(val) => !val && close()">
        <DialogContent class="sm:max-w-md text-center py-10">
            <DialogHeader class="flex flex-col items-center gap-4">
                <div class="rounded-full bg-destructive/10 p-4">
                    <Ban class="h-12 w-12 text-destructive" />
                </div>
                <DialogTitle class="text-3xl font-bold text-destructive">
                    {{ trans('pages.settings.users.status.banned') }}
                </DialogTitle>
                <DialogDescription class="text-xl text-foreground mt-4 whitespace-pre-wrap">
                    {{ message }}
                </DialogDescription>
            </DialogHeader>
            <DialogFooter class="sm:justify-center mt-8">
                <Button variant="outline" size="lg" @click="close" class="px-8">
                    {{ trans('pages.auth.login.back_button') }} ({{ countdown }}s)
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
