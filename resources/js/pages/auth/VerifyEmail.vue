<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SignupForm from '@/components/SignupForm.vue';
import { trans } from 'laravel-vue-i18n';
</script>

<template>
    <Head :title="trans('pages.auth.login.verify_email_title')"></Head>
    <div class="flex min-h-svh flex-col items-center justify-center bg-black p-6 md:p-10">
        <div class="w-full max-w-sm">
            <SignupForm :initialStep="6" />
        </div>
    </div>
</template>

<style scoped></style>
