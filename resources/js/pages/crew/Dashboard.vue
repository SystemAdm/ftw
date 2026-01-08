<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import {
    Users,
    CalendarDays,
    List
} from 'lucide-vue-next';

import { dashboard as crewDashboardRoute } from '@/routes/crew/index';
import { index as crewEventsRoute } from '@/routes/crew/events/index';
import { index as crewTeamsRoute } from '@/routes/crew/teams/index';

const props = defineProps<{
    myTeamsCount: number;
    pendingApplicationsCount: number;
}>();

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('ui.navigation.home'),
        href: '/',
    },
    {
        title: trans('ui.navigation.crew'),
        href: crewDashboardRoute.url(),
    },
]);

const cards = computed(() => [
    {
        title: trans('pages.ui.navigation.teams'),
        icon: Users,
        count: props.myTeamsCount,
        description: trans('pages.crew.dashboard.my_teams'),
        url: crewTeamsRoute.url(),
    },
    {
        title: trans('pages.crew.dashboard.pending_applications'),
        icon: Users,
        count: props.pendingApplicationsCount,
        description: trans('pages.crew.dashboard.applications_description'),
        url: crewTeamsRoute.url(),
    },
    {
        title: trans('pages.ui.navigation.events'),
        icon: CalendarDays,
        count: null,
        description: trans('pages.crew.dashboard.view_events'),
        url: crewEventsRoute.url(),
    },
]);
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="trans('pages.ui.navigation.crew_menu')" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <Card v-for="card in cards" :key="card.title" class="flex flex-col">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">
                        {{ card.title }}
                    </CardTitle>
                    <component :is="card.icon" class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent class="flex-1">
                    <div v-if="card.count !== null" class="text-2xl font-bold">{{ card.count }}</div>
                    <p class="text-xs text-muted-foreground">{{ card.description }}</p>
                </CardContent>
                <CardFooter>
                    <Button variant="outline" size="sm" class="w-full" as-child>
                        <Link :href="card.url">
                            <List class="mr-2 h-4 w-4" />
                            {{ trans('pages.ui.buttons.view') || 'View' }}
                        </Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </SidebarLayout>
</template>
