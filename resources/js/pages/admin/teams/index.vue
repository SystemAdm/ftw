<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import Paginator from '@/components/custom/Paginator.vue';
import { Button } from '@/components/ui/button';
import { show, create, restore as restoreRoute, forceDestroy as deleteRoute } from '@/routes/admin/teams';
import { trans } from 'laravel-vue-i18n';
import { Edit, Eye, MoreHorizontal, RotateCcw, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const inertiaPage = usePage<PageProps>();

const deleteDialogOpen = ref(false);
const forceDeleteDialogOpen = ref(false);
const selectedTeamId = ref<number | null>(null);

function goCreate() {
    router.visit(create.url());
}

function handleRestore(id: number) {
    router.post(restoreRoute.url(id));
}

function confirmDelete(id: number) {
    selectedTeamId.value = id;
    deleteDialogOpen.value = true;
}

function confirmForceDelete(id: number) {
    selectedTeamId.value = id;
    forceDeleteDialogOpen.value = true;
}

function handleDelete() {
    if (selectedTeamId.value) {
        router.delete(`/admin/teams/${selectedTeamId.value}`, {
            onFinish: () => {
                deleteDialogOpen.value = false;
                selectedTeamId.value = null;
            },
        });
    }
}

function handleForceDelete() {
    if (selectedTeamId.value) {
        router.delete(deleteRoute.url(selectedTeamId.value), {
            onFinish: () => {
                forceDeleteDialogOpen.value = false;
                selectedTeamId.value = null;
            },
        });
    }
}

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.settings.teams.title'),
        href: '/admin/teams',
    },
]);
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.settings.teams.title') }}</h1>
            <Button @click="goCreate">{{ trans('pages.settings.teams.new') }}</Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead class="w-12"></TableHead>
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
                    class="group"
                    :class="{ 'opacity-50': team.deleted_at }"
                >
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(team.id))">
                        <div class="flex size-8 items-center justify-center overflow-hidden rounded border bg-muted">
                            <img v-if="team.logo" :src="team.logo" :alt="team.name" class="h-full w-full object-contain" />
                            <div v-else class="text-[10px] font-bold text-muted-foreground uppercase">{{ team.slug?.substring(0, 2) || team.name.substring(0, 2) }}</div>
                        </div>
                    </TableCell>
                    <TableCell class="cursor-pointer font-medium" @click="router.visit(show.url(team.id))">{{ team.name }}</TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(team.id))">{{ team.slug }}</TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(team.id))">
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
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon">
                                    <MoreHorizontal class="h-4 w-4" />
                                    <span class="sr-only">Open menu</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="router.visit(show.url(team.id))">
                                    <Eye class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.locations.actions.view') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="router.visit(`/admin/teams/${team.id}/edit`)">
                                    <Edit class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.locations.actions.edit') }}
                                </DropdownMenuItem>

                                <template v-if="team.deleted_at">
                                    <DropdownMenuItem class="text-green-600 focus:text-green-600" @click="handleRestore(team.id)">
                                        <RotateCcw class="mr-2 h-4 w-4" />
                                        {{ trans('pages.settings.teams.actions.restore') }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem class="text-destructive focus:text-destructive" @click="confirmForceDelete(team.id)">
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        {{ trans('pages.settings.teams.actions.force_delete') }}
                                    </DropdownMenuItem>
                                </template>
                                <DropdownMenuItem v-else class="text-destructive focus:text-destructive" @click="confirmDelete(team.id)">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.locations.actions.delete') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="5">
                        <Paginator :collection="inertiaPage.props.teams" />
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>

        <DeleteConfirmationDialog
            v-model:open="deleteDialogOpen"
            :title="trans('pages.settings.teams.delete.title')"
            :description="trans('pages.settings.teams.delete.description')"
            @confirm="handleDelete"
        />

        <DeleteConfirmationDialog
            v-model:open="forceDeleteDialogOpen"
            :title="trans('pages.settings.teams.force_delete.title')"
            :description="trans('pages.settings.teams.force_delete.description')"
            @confirm="handleForceDelete"
        />
    </SidebarLayout>
</template>
