<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SignupForm from '@/components/SignupForm.vue';
</script>

<template>
    <Head title="Account"></Head>
    <div class="flex min-h-svh flex-col items-center justify-center bg-black p-6 md:p-10">
        <div class="w-full max-w-sm">
            <SignupForm />
        </div>
    </div>
</template>

<style scoped></style>
