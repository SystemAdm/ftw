<script setup lang="ts">
import { Card } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { trans } from 'laravel-vue-i18n';
import { router, Link } from '@inertiajs/vue3';
import TeamHoverCard from '@/components/custom/TeamHoverCard.vue';
import { show as showTeam } from '@/routes/teams/index';

type Team = {
    id: number;
    name: string;
    slug?: string;
    description?: string | null;
    logo?: string | null;
    created_at?: string | null;
};

type Entry = {
    id: number;
    team?: Team;
    name?: string | null;
    description?: string | null;
    start_time?: string | null;
    end_time?: string | null;
    is_excluded: boolean;
};

type Day = {
    date: string;
    weekday: number; // 0 = Sun ... 6 = Sat
    label: string; // e.g., Fri 12 Dec
    has_weekday: boolean;
    is_excluded: boolean;
    weekday_label: string; // e.g., Friday
    entries: Entry[];
};

const props = defineProps<{
    days: Day[];
    week: number;
    baseUrl: string;
}>();

function goToWeek(nextWeek: number) {
    if (nextWeek < 0) nextWeek = 0;
    router.get(props.baseUrl, { week: nextWeek }, { preserveScroll: true });
}
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between gap-3">
            <h2 class="text-2xl font-bold tracking-tight">{{ trans('pages.dashboard.upcoming') }}</h2>
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

                <div v-if="day.entries && day.entries.length > 0" class="mt-3 space-y-4">
                    <div v-for="entry in day.entries" :key="entry.id" class="space-y-1.5">
                        <TeamHoverCard v-if="entry.team" :team="entry.team" />

                        <div
                            class="text-sm leading-none font-semibold"
                            :class="entry.is_excluded ? 'text-red-600 dark:text-red-400' : 'text-primary'"
                        >
                            <Link v-if="entry.team" :href="showTeam.url(entry.team.id)" class="hover:underline">
                                {{ entry.name ?? trans('pages.dashboard.unnamed') }}
                            </Link>
                            <span v-else>{{ entry.name ?? trans('pages.dashboard.unnamed') }}</span>
                        </div>
                        <div v-if="entry.start_time && entry.end_time" class="flex items-center gap-1 text-xs text-muted-foreground">
                            <span>{{ entry.start_time.slice(0, 5) }}</span>
                            <span>–</span>
                            <span>{{ entry.end_time.slice(0, 5) }}</span>
                        </div>
                        <div v-if="entry.description" class="mt-1 line-clamp-2 text-xs text-muted-foreground" :title="entry.description">
                            {{ entry.description }}
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </div>
</template>
