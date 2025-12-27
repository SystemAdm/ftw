<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import Paginator from '@/components/custom/Paginator.vue';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { index, show, create, restore as restoreRoute, forceDestroy as deleteRoute } from '@/routes/admin/teams';
import { trans } from 'laravel-vue-i18n';

const inertiaPage = usePage<PageProps>();

function goCreate() {
    router.visit(create.url());
}

function restoreTeam(id: number, e?: Event) {
    e?.stopPropagation();
    router.post(restoreRoute.url(id));
}

function forceDeleteTeam(id: number, e?: Event) {
    e?.stopPropagation();
    router.delete(deleteRoute.url(id), {
        onFinish: () => router.visit(index.url()),
    });
}
</script>

<template>
    <SidebarLayout>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.settings.teams.title') }}</h1>
            <Button @click="goCreate">{{ trans('pages.settings.teams.new') }}</Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ trans('pages.settings.teams.fields.name') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.teams.fields.slug') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.teams.fields.status') }}</TableHead>
                    <TableHead class="text-right"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="team in inertiaPage.props.teams.data"
                    :key="team.id"
                    class="cursor-pointer"
                    @click="router.visit(show.url(team.id))"
                >
                    <TableCell class="font-medium">{{ team.name }}</TableCell>
                    <TableCell>{{ team.slug }}</TableCell>
                    <TableCell>
                        <span :class="team.deleted_at ? 'text-red-600' : team.active ? 'text-green-600' : 'text-gray-500'">
                            {{
                                team.deleted_at
                                    ? trans('pages.settings.teams.status.deleted')
                                    : team.active
                                      ? trans('pages.settings.teams.status.active')
                                      : trans('pages.settings.teams.status.inactive')
                            }}
                        </span>
                    </TableCell>
                    <TableCell class="text-right">
                        <div v-if="team.deleted_at" class="flex justify-end gap-2">
                            <Button size="sm" variant="outline" @click="(e) => restoreTeam(team.id, e)">
                                {{ trans('pages.settings.teams.actions.restore') }}
                            </Button>

                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <Button size="sm" variant="destructive" @click.stop>
                                        {{ trans('pages.settings.teams.actions.force_delete') }}
                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>{{ trans('pages.settings.teams.force_delete.title') }}</AlertDialogTitle>
                                        <AlertDialogDescription>
                                            {{ trans('pages.settings.teams.force_delete.description') }}
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>{{ trans('pages.settings.locations.actions.cancel') }}</AlertDialogCancel>
                                        <AlertDialogAction @click="(e) => forceDeleteTeam(team.id, e)">
                                            {{ trans('pages.settings.teams.actions.force_delete') }}
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                        <div v-else class="text-sm text-gray-400">—</div>
                    </TableCell>
                </TableRow>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="4">
                        <Paginator :collection="inertiaPage.props.teams" />
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>
    </SidebarLayout>
</template>
