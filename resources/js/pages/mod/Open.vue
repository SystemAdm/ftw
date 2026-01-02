<script setup lang="ts">
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Paginator from '@/components/custom/Paginator.vue';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';
import { index as modOpenRoute } from '@/routes/mod/open/index';

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.ui.navigation.mod_menu'),
        href: modOpenRoute.url(),
    },
]);

const page = usePage<any>();
const inside = computed(() => page.props.inside);
const history = computed(() => page.props.history);

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
        <Head :title="trans('pages.ui.navigation.mod_menu')" />

        <div class="space-y-6">
            <h1 class="text-2xl font-bold tracking-tight">{{ trans('pages.mod.open.title') }}</h1>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ trans('pages.mod.open.inside_title') }} ({{ inside.length }})</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                                    <TableHead>{{ trans('pages.mod.open.entered_at') }}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="entry in inside" :key="entry.id">
                                    <TableCell>{{ entry.user.name }}</TableCell>
                                    <TableCell>{{ formatDate(entry.entered_at) }}</TableCell>
                                </TableRow>
                                <TableRow v-if="inside.length === 0">
                                    <TableCell colspan="2" class="text-center text-muted-foreground py-4">
                                        {{ trans('pages.mod.open.no_one_inside') }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ trans('pages.mod.open.history_title') }}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{{ trans('pages.settings.users.fields.name') }}</TableHead>
                                    <TableHead>{{ trans('pages.mod.open.action') }}</TableHead>
                                    <TableHead>{{ trans('pages.mod.open.time') }}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="log in history.data" :key="log.id">
                                    <TableCell>{{ log.user.name }}</TableCell>
                                    <TableCell>
                                        <span :class="log.action === 'in' ? 'text-green-600' : 'text-red-600'">
                                            {{ trans(`pages.mod.open.actions.${log.action}`) }}
                                        </span>
                                    </TableCell>
                                    <TableCell>{{ formatDate(log.created_at) }}</TableCell>
                                </TableRow>
                                <TableRow v-if="history.data.length === 0">
                                    <TableCell colspan="3" class="text-center text-muted-foreground py-4">
                                        {{ trans('pages.mod.open.no_history') }}
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                        <div class="mt-4">
                            <Paginator :collection="history" />
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </SidebarLayout>
</template>
