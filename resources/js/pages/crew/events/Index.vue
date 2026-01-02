<script setup lang="ts">
import Paginator from '@/components/custom/Paginator.vue';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { index, show } from '@/actions/App/http/controllers/Crew/EventsController';
import { router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Eye } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage<PageProps>();

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.ui.navigation.events'),
        href: index.url(),
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

function getStatusColor(status: string) {
    if (status === 'published') return 'default';
    if (status === 'draft') return 'secondary';
    if (status === 'cancelled') return 'destructive';
    return 'outline';
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.ui.navigation.events') }}</h1>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ trans('pages.settings.events.fields.title') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.events.fields.location') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.events.fields.event_start') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.events.fields.status') }}</TableHead>
                    <TableHead class="w-12"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="e in (page.props as any).events.data" :key="e.id" class="group">
                    <TableCell class="cursor-pointer font-medium" @click="router.visit(show.url(e.id))">
                        {{ e.title }}
                    </TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(e.id))">{{ e.location?.name ?? '—' }}</TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(e.id))">{{ formatDate(e.event_start) }}</TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(e.id))">
                        <Badge :variant="getStatusColor(e.status)">
                            {{ trans(`pages.settings.events.status.${e.status}`) }}
                        </Badge>
                    </TableCell>
                    <TableCell>
                        <Button variant="ghost" size="icon" @click="router.visit(show.url(e.id))">
                            <Eye class="h-4 w-4" />
                        </Button>
                    </TableCell>
                </TableRow>
                <TableRow v-if="(page.props as any).events.data.length === 0">
                    <TableCell colspan="5" class="py-8 text-center text-muted-foreground">
                        {{ trans('pages.settings.events.none') }}
                    </TableCell>
                </TableRow>
            </TableBody>
            <TableFooter v-if="(page.props as any).events.last_page > 1">
                <TableRow>
                    <TableCell colspan="5">
                        <Paginator :collection="(page.props as any).events" />
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>
    </SidebarLayout>
</template>
