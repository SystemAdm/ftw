<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import SignupForm from '@/components/SignupForm.vue';
import { trans } from 'laravel-vue-i18n';
import { Toaster } from '@/components/ui/sonner';
import { useFlashToasts } from '@/composables/useFlashToasts';

const page = usePage();
useFlashToasts(page);
</script>

<template>
    <Toaster />
    <Head :title="trans('pages.auth.login.title')"></Head>
    <div class="flex min-h-svh flex-col items-center justify-center bg-black p-6 md:p-10">
        <div class="w-full max-w-sm">
            <SignupForm />
        </div>
    </div>
</template>

<style scoped></style>
