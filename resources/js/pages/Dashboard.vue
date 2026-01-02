<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { dashboard } from '@/actions/App/http/controllers/auth/UsersController';
import { BreadcrumbItemType } from '@/types';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';

type Team = {
    id: number;
    name: string;
    slug?: string;
};
type Day = {
    team?: Team;
    date: string;
    weekday: number; // 0 = Sun ... 6 = Sat
    label: string; // e.g., Fri 12 Dec
    has_weekday: boolean;
    is_excluded: boolean;
    weekday_label: string; // e.g., Friday
    name?: string | null;
    description?: string | null;
    start_time?: string | null;
    end_time?: string | null;
};

const page = usePage<{ days: Day[]; week?: number; qr_code?: string; qr_code_value?: string }>();
const days = computed<Day[]>(() => (page.props.days as Day[]) ?? []);
const week = computed<number>(() => (page.props.week as number | undefined) ?? 0);
const qrCode = computed(() => page.props.qr_code);
const qrCodeValue = computed(() => page.props.qr_code_value);

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.dashboard.title'),
        href: dashboard.url(),
    },
]);

function goToWeek(nextWeek: number) {
    if (nextWeek < 0) nextWeek = 0;
    router.get(dashboard.url({ query: { week: nextWeek } }), {}, { preserveScroll: true });
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="trans('pages.dashboard.title')" />

        <div class="grid grid-cols-1 gap-6">
            <div class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <h1 class="text-2xl font-bold tracking-tight">{{ trans('pages.dashboard.upcoming') }}</h1>
                    <div class="flex items-center gap-2">
                        <Button variant="outline" size="sm" :disabled="week <= 0" @click="goToWeek(week - 1)">
                            ‹ {{ trans('pages.dashboard.actions.previous') }}
                        </Button>
                        <Button variant="outline" size="sm" @click="goToWeek(week + 1)"> {{ trans('pages.dashboard.actions.next') }} › </Button>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 md:grid-cols-4 lg:grid-cols-7">
                    <Card v-for="day in days" :key="day.date" class="p-4" :class="day.is_excluded ? 'bg-muted/50 opacity-60' : ''">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="text-xs font-medium tracking-wider text-muted-foreground uppercase">{{ day.weekday_label }}</div>
                                <div class="text-lg font-bold">{{ day.label }}</div>
                            </div>
                        </div>

                        <div v-if="day.name || day.description || day.team" class="mt-3 space-y-1.5">
                            <Badge v-if="day.team" variant="secondary">
                                {{ day.team.slug ?? day.team.name }}
                            </Badge>
                            <div
                                class="text-sm leading-none font-semibold"
                                :class="day.is_excluded ? 'text-red-600 dark:text-red-400' : 'text-primary'"
                            >
                                {{ day.name ?? trans('pages.dashboard.unnamed') }}
                            </div>
                            <div v-if="day.start_time && day.end_time" class="flex items-center gap-1 text-xs text-muted-foreground">
                                <span>{{ day.start_time.slice(0, 5) }}</span>
                                <span>–</span>
                                <span>{{ day.end_time.slice(0, 5) }}</span>
                            </div>
                            <div v-if="day.description" class="mt-1 line-clamp-2 text-xs text-muted-foreground" :title="day.description">
                                {{ day.description }}
                            </div>
                        </div>
                    </Card>
                </div>
            </div>
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
