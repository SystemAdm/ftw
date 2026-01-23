<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { computed } from 'vue';
import { trans } from 'laravel-vue-i18n';
import {
    UserIcon,
    ShieldAlert,
    Users,
    Gavel,
    Mail,
    MapPinIcon,
    CalendarDays,
    DoorOpen,
    Phone,
    Plus,
    List
} from 'lucide-vue-next';

import { index as adminUsersRoute } from '@/routes/admin/users/index';
import { index as adminRolesRoute } from '@/routes/admin/roles/index';
import { index as adminPermissionsRoute } from '@/routes/admin/permissions/index';
import { index as adminTeamsRoute } from '@/routes/admin/teams/index';
import { index as adminPostcodesRoute } from '@/routes/admin/postcodes/index';
import { index as adminLocationsRoute } from '@/routes/admin/locations/index';
import { index as adminWeekdaysRoute } from '@/routes/admin/weekdays/index';
import { index as adminEventsRoute } from '@/routes/admin/events/index';
import { index as adminPhoneRoute } from '@/routes/admin/phone/index';
import { index as adminOpenRoute } from '@/routes/admin/open/index';
import { index as adminRelationsRoute } from '@/routes/admin/relations/index';
import { dashboard as adminDashboardRoute } from '@/routes/admin/index';

const props = defineProps<{
    counts: {
        users: number;
        roles: number;
        permissions: number;
        teams: number;
        postcodes: number;
        locations: number;
        weekdays: number;
        phone_numbers: number;
        events: number;
        relations?: number;
    }
}>();

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('ui.navigation.home'),
        href: '/',
    },
    {
        title: trans('ui.navigation.admin'),
        href: adminDashboardRoute.url(),
    },
]);

const cards = computed(() => [
    {
        title: trans('pages.ui.navigation.users'),
        icon: UserIcon,
        count: props.counts.users,
        url: adminUsersRoute.url(),
        createUrl: null, // Typically users are not "created" manually here but via registration/invite
    },
    {
        title: trans('pages.ui.navigation.roles'),
        icon: ShieldAlert,
        count: props.counts.roles,
        url: adminRolesRoute.url(),
        createUrl: '/admin/roles/create',
    },
    {
        title: trans('pages.ui.navigation.permissions'),
        icon: Gavel,
        count: props.counts.permissions,
        url: adminPermissionsRoute.url(),
        createUrl: '/admin/permissions/create',
    },
    {
        title: trans('pages.ui.navigation.teams'),
        icon: Users,
        count: props.counts.teams,
        url: adminTeamsRoute.url(),
        createUrl: '/admin/teams/create',
    },
    {
        title: trans('pages.ui.navigation.postcodes'),
        icon: Mail,
        count: props.counts.postcodes,
        url: adminPostcodesRoute.url(),
        createUrl: '/admin/postcodes/create',
    },
    {
        title: trans('pages.ui.navigation.locations'),
        icon: MapPinIcon,
        count: props.counts.locations,
        url: adminLocationsRoute.url(),
        createUrl: '/admin/locations/create',
    },
    {
        title: trans('pages.ui.navigation.weekdays'),
        icon: CalendarDays,
        count: props.counts.weekdays,
        url: adminWeekdaysRoute.url(),
        createUrl: '/admin/weekdays/create',
    },
    {
        title: trans('pages.ui.navigation.phone'),
        icon: Phone,
        count: props.counts.phone_numbers,
        url: adminPhoneRoute.url(),
        createUrl: null,
    },
    {
        title: trans('pages.ui.navigation.events'),
        icon: CalendarDays,
        count: props.counts.events,
        url: adminEventsRoute.url(),
        createUrl: '/admin/events/create',
    },
    {
        title: trans('pages.ui.navigation.admin_open'),
        icon: DoorOpen,
        count: null,
        url: adminOpenRoute.url(),
        createUrl: null,
    },
    {
        title: trans('pages.ui.navigation.relations'),
        icon: Users,
        count: props.counts.relations ?? 0,
        url: adminRelationsRoute.url(),
        createUrl: '/admin/relations/create',
    }
]);
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <Head :title="trans('pages.ui.navigation.admin_menu')" />

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <Card v-for="card in cards" :key="card.title" class="flex flex-col">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <CardTitle class="text-sm font-medium">
                        {{ card.title }}
                    </CardTitle>
                    <component :is="card.icon" class="h-4 w-4 text-muted-foreground" />
                </CardHeader>
                <CardContent class="flex-1">
                    <div v-if="card.count !== null" class="text-2xl font-bold">{{ card.count }}</div>
                    <div v-else class="text-2xl font-bold">&nbsp;</div>
                </CardContent>
                <CardFooter class="flex gap-2">
                    <Button variant="outline" size="sm" class="flex-1" as-child>
                        <Link :href="card.url">
                            <List class="mr-2 h-4 w-4" />
                            {{ trans('pages.ui.buttons.list') || 'List' }}
                        </Link>
                    </Button>
                    <Button v-if="card.createUrl" variant="default" size="sm" class="flex-1" as-child>
                        <Link :href="card.createUrl">
                            <Plus class="mr-2 h-4 w-4" />
                            {{ trans('pages.ui.buttons.new') || 'New' }}
                        </Link>
                    </Button>
                </CardFooter>
            </Card>
        </div>
    </SidebarLayout>
</template>
