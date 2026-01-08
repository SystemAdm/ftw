<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { index, show } from '@/routes/crew/events';
import { usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';

import { dashboard as crewDashboardRoute } from '@/routes/crew';

const page = usePage<PageProps>();
const event = computed(() => (page.props as any).event);

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('ui.navigation.home'),
        href: '/',
    },
    {
        title: trans('ui.navigation.crew'),
        href: crewDashboardRoute.url(),
    },
    {
        title: trans('pages.ui.navigation.events'),
        href: index.url(),
    },
    {
        title: event.value.title,
        href: show.url(event.value.id),
    },
]);

function formatDate(date: string) {
    return new Date(date).toLocaleString(page.props.i18n.locale, {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
    });
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">{{ event.title }}</h1>
                <p class="text-muted-foreground">{{ event.location?.name }} | {{ formatDate(event.event_start) }}</p>
            </div>
            <Badge :variant="event.status === 'published' ? 'default' : 'secondary'">
                {{ trans(`pages.settings.events.status.${event.status}`) }}
            </Badge>
        </div>

        <div class="space-y-6">
            <Card>
                <CardHeader>
                    <CardTitle>{{ trans('pages.events.tabs.attendees') }} ({{ event.attendees.length }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                                <TableHead>{{ trans('pages.settings.users.fields.email') }}</TableHead>
                                <TableHead>{{ trans('pages.events.fields.checked_in_at') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in event.attendees" :key="user.id">
                                <TableCell>{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>{{ user.pivot?.created_at ? formatDate(user.pivot.created_at) : '—' }}</TableCell>
                            </TableRow>
                            <TableRow v-if="event.attendees.length === 0">
                                <TableCell colspan="3" class="text-center">{{ trans('pages.events.none') }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ trans('pages.events.tabs.inside') }} ({{ event.inside.length }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                                <TableHead>{{ trans('pages.settings.users.fields.email') }}</TableHead>
                                <TableHead>{{ trans('pages.events.fields.entered_at') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in event.inside" :key="user.id">
                                <TableCell>{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>{{ user.pivot?.created_at ? formatDate(user.pivot.created_at) : '—' }}</TableCell>
                            </TableRow>
                            <TableRow v-if="event.inside.length === 0">
                                <TableCell colspan="3" class="text-center">{{ trans('pages.events.none') }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ trans('pages.events.tabs.reservations') }} ({{ event.reservations.length }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                                <TableHead>{{ trans('pages.settings.users.fields.email') }}</TableHead>
                                <TableHead>{{ trans('pages.events.fields.reserved_at') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in event.reservations" :key="user.id">
                                <TableCell>{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>{{ user.pivot?.created_at ? formatDate(user.pivot.created_at) : '—' }}</TableCell>
                            </TableRow>
                            <TableRow v-if="event.reservations.length === 0">
                                <TableCell colspan="3" class="text-center">{{ trans('pages.events.none') }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ trans('pages.events.tabs.logs') }} ({{ event.logs.length }})</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ trans('pages.events.fields.log_time') }}</TableHead>
                                <TableHead>{{ trans('pages.events.fields.log_user') }}</TableHead>
                                <TableHead>{{ trans('pages.events.fields.log_message') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="log in event.logs" :key="log.id">
                                <TableCell>{{ formatDate(log.created_at) }}</TableCell>
                                <TableCell>{{ log.user?.name ?? 'System' }}</TableCell>
                                <TableCell>{{ log.message }}</TableCell>
                            </TableRow>
                            <TableRow v-if="event.logs.length === 0">
                                <TableCell colspan="3" class="text-center">{{ trans('pages.events.none') }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </SidebarLayout>
</template>
