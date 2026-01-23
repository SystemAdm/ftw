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

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-4 lg:items-start">
            <div class="lg:col-span-3">
                <UpcomingWeekdays :days="days" :week="week" :base-url="dashboard.url()" />
            </div>

            <aside class="space-y-6">
                <Card class="sticky top-6 flex flex-col items-center justify-center space-y-5 p-8 text-center shadow-sm border-primary/10 bg-linear-to-b from-primary/5 to-transparent">
                    <div class="space-y-1">
                        <h2 class="text-xl font-bold tracking-tight text-primary">{{ trans('pages.dashboard.personal_qr') }}</h2>
                        <p class="text-xs text-muted-foreground leading-relaxed">
                            {{ trans('pages.dashboard.qr_description') }}
                        </p>
                    </div>

                    <div v-if="qrCode" class="rounded-xl border border-border/60 bg-white p-3 shadow-md transition-transform hover:scale-105" v-html="qrCode"></div>

                    <div v-if="qrCodeValue" class="max-w-full break-all font-mono text-[9px] text-muted-foreground/40 uppercase tracking-tighter">
                        {{ qrCodeValue }}
                    </div>
                </Card>
            </aside>
        </div>
    </SidebarLayout>
</template>
