<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import { BreadcrumbItemType } from '@/types';
import { Card } from '@/components/ui/card';
import UpcomingWeekdays from '@/components/custom/UpcomingWeekdays.vue';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';

const page = usePage<{ days: any[]; week?: number; qr_code?: string; qr_code_value?: string }>();
const days = computed<any[]>(() => (page.props.days as any[]) ?? []);
const week = computed<number>(() => (page.props.week as number | undefined) ?? 0);
const qrCode = computed(() => page.props.qr_code);
const qrCodeValue = computed(() => page.props.qr_code_value);

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('ui.navigation.home'),
        href: '/',
    },
    {
        title: trans('pages.dashboard.title'),
        href: dashboard.url(),
    },
]);
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="trans('pages.dashboard.title')" />

        <div class="grid grid-cols-1 gap-6">
            <UpcomingWeekdays :days="days" :week="week" :base-url="dashboard.url()" />

            <div class="">
                <Card class="sticky top-6 flex flex-col items-center justify-center space-y-4 p-6 text-center">
                    <h2 class="text-lg font-bold">{{ trans('pages.dashboard.personal_qr') }}</h2>
                    <div v-if="qrCode" class="rounded-lg border bg-white p-2 shadow-sm" v-html="qrCode"></div>
                    <div v-if="qrCodeValue" class="max-w-full break-all text-[10px] text-muted-foreground opacity-50">
                        {{ qrCodeValue }}
                    </div>
                    <p class="text-sm text-muted-foreground">
                        {{ trans('pages.dashboard.qr_description') }}
                    </p>
                </Card>
            </div>
        </div>
    </SidebarLayout>
</template>
