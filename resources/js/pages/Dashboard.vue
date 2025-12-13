<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { computed } from 'vue';

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

const page = usePage<{ days: Day[]; week?: number }>();
const days = computed<Day[]>(() => (page.props.days as Day[]) ?? []);
const week = computed<number>(() => ((page.props.week as number | undefined) ?? 0));

function goToWeek(nextWeek: number) {
    if (nextWeek < 0) nextWeek = 0;
    router.get('/dashboard', { week: nextWeek }, { preserveScroll: true });
}
</script>

<template>
    <SidebarLayout>
        <Head title="Dashboard" />
        <div class="space-y-4">
            <div class="flex items-center justify-between gap-3">
                <h1 class="text-2xl font-bold tracking-tight">Upcoming week</h1>
                <div class="flex items-center gap-2">
                    <button
                        class="rounded border px-3 py-1 text-sm disabled:opacity-50"
                        :disabled="week <= 0"
                        @click="goToWeek(week - 1)"
                    >
                        ‹ Previous
                    </button>
                    <button
                        class="rounded border px-3 py-1 text-sm"
                        @click="goToWeek(week + 1)"
                    >
                        Next ›
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-7">
                <Card v-for="day in days" :key="day.date" class="p-4" :class="day.is_excluded ? 'opacity-60' : ''">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-muted-foreground">{{ day.weekday_label }}</div>
                            <div class="text-lg font-semibold">{{ day.label }}</div>
                        </div>
                    </div>

                    <div v-if="day.name || day.description || day.team" class="mt-3 space-y-1">
                        <Badge v-if="day.team">
                            {{ day.team.slug ?? day.team.name }}
                        </Badge>
                        <div class="text-sm font-medium" :class="day.is_excluded ? 'text-red-600' : 'text-green-600'">
                            {{ day.name ?? 'Unnamed assignment' }}
                        </div>
                        <div v-if="day.start_time && day.end_time" class="text-sm text-muted-foreground">
                            {{ day.start_time }}–{{ day.end_time }}
                        </div>
                        <div v-if="day.description" class="mt-1 text-sm text-muted-foreground">{{ day.description }}</div>
                    </div>
                </Card>
            </div>
        </div>
    </SidebarLayout>
</template>
