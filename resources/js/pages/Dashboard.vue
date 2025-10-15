<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { spillexpo } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: spillexpo().url,
    },
];

// Access shared Inertia props to know if user is authenticated
const page: any = usePage();
const user = computed(() => page.props.auth?.user ?? null);
const encrypted = computed<string | null>(() => page.props.auth?.qrEncryptedUserId ?? null);

// Build a QR code image URL pointing to our internal endpoint
const qrUrl = computed<string | null>(() => {
    if (!user.value) return null;
    return '/qr/user';
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border flex items-center justify-center bg-white dark:bg-gray-900">
                    <template v-if="qrUrl">
                        <img :src="qrUrl" alt="User QR Code" class="h-full w-full object-contain p-4" />
                    </template>
                    <template v-else>
                        <PlaceholderPattern />
                    </template>
                    <div v-if="encrypted" class="absolute bottom-2 left-2 right-2 px-2 text-[11px] leading-tight text-gray-600 dark:text-gray-300 break-all select-all text-center">
                        {{ encrypted }}
                    </div>
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
                <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <PlaceholderPattern />
                </div>
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
        </div>
    </AppLayout>
</template>
