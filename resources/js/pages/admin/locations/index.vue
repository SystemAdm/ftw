<script setup lang="ts">
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { router, usePage } from '@inertiajs/vue3';
import { create as createRoute, show as showRoute, edit as editRoute, destroy as destroyRoute } from '@/routes/admin/locations';
import { restore as restoreRoute, forceDestroy as forceDestroyRoute } from '@/routes/admin/locations';
import { Table, TableBody, TableEmpty, TableHead, TableHeader, TableRow, TableCell, TableFooter } from '@/components/ui/table';
import Paginator from '@/components/custom/Paginator.vue';
import { trans } from 'laravel-vue-i18n';
import { Edit, Eye, MoreHorizontal, RotateCcw, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage<PageProps>();

const deleteDialogOpen = ref(false);
const forceDeleteDialogOpen = ref(false);
const selectedLocation = ref<any>(null);

function handleRestore(loc: any) {
    router.post(restoreRoute.url(loc.id));
}

function confirmDelete(loc: any) {
    selectedLocation.value = loc;
    deleteDialogOpen.value = true;
}

function confirmForceDelete(loc: any) {
    selectedLocation.value = loc;
    forceDeleteDialogOpen.value = true;
}

function handleDelete() {
    if (selectedLocation.value) {
        router.delete(destroyRoute.url(selectedLocation.value.id), {
            onFinish: () => {
                deleteDialogOpen.value = false;
                selectedLocation.value = null;
            },
        });
    }
}

function handleForceDelete() {
    if (selectedLocation.value) {
        router.delete(forceDestroyRoute.url(selectedLocation.value.id), {
            onFinish: () => {
                forceDeleteDialogOpen.value = false;
                selectedLocation.value = null;
            },
        });
    }
}

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.settings.locations.title'),
        href: '/admin/locations',
    },
]);
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.settings.locations.title') }}</h1>
            <Button @click.prevent="router.visit(createRoute.url())">{{ trans('pages.settings.locations.actions.create') }}</Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ trans('pages.settings.locations.fields.name') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.locations.fields.postal_code') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.locations.fields.active') }}</TableHead>
                    <TableHead class="w-0 text-right"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableEmpty v-if="!page.props.locations || page.props.locations.data.length === 0" :colspan="4"> {{ trans('pages.settings.locations.none') }} </TableEmpty>
                <template v-else>
                    <TableRow v-for="loc in page.props.locations.data" :key="loc.id" class="group" :class="{ 'opacity-50': loc.deleted_at }">
                        <TableCell class="cursor-pointer" @click="router.visit(showRoute.url(loc.id))">{{ loc.name }}</TableCell>
                        <TableCell class="cursor-pointer" @click="router.visit(showRoute.url(loc.id))">{{ loc.postal }}</TableCell>
                        <TableCell class="cursor-pointer" @click="router.visit(showRoute.url(loc.id))">
                            <span :class="loc.active ? 'text-green-600' : 'text-gray-500'">{{ loc.active ? trans('pages.settings.locations.fields.yes') : trans('pages.settings.locations.fields.no') }}</span>
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
                                    <DropdownMenuItem @click="router.visit(showRoute.url(loc.id))">
                                        <Eye class="mr-2 h-4 w-4" />
                                        {{ trans('pages.settings.locations.actions.view') }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="router.visit(editRoute.url(loc.id))">
                                        <Edit class="mr-2 h-4 w-4" />
                                        {{ trans('pages.settings.locations.actions.edit') }}
                                    </DropdownMenuItem>

                                    <template v-if="loc.deleted_at">
                                        <DropdownMenuItem class="text-green-600 focus:text-green-600" @click="handleRestore(loc)">
                                            <RotateCcw class="mr-2 h-4 w-4" />
                                            {{ trans('pages.settings.locations.actions.restore') }}
                                        </DropdownMenuItem>
                                        <DropdownMenuItem class="text-destructive focus:text-destructive" @click="confirmForceDelete(loc)">
                                            <Trash2 class="mr-2 h-4 w-4" />
                                            {{ trans('pages.settings.locations.actions.force_delete') }}
                                        </DropdownMenuItem>
                                    </template>
                                    <DropdownMenuItem v-else class="text-destructive focus:text-destructive" @click="confirmDelete(loc)">
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        {{ trans('pages.settings.locations.actions.delete') }}
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </TableCell>
                    </TableRow>
                </template>
            </TableBody>
            <TableFooter>
                <TableRow>
                    <TableCell colspan="4">
                        <Paginator :collection="(page.props as any).locations" />
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>

        <DeleteConfirmationDialog
            v-model:open="deleteDialogOpen"
            :title="trans('pages.settings.locations.delete.title', { name: selectedLocation?.name })"
            :description="trans('pages.settings.locations.delete.description')"
            @confirm="handleDelete"
        />

        <DeleteConfirmationDialog
            v-model:open="forceDeleteDialogOpen"
            :title="trans('pages.settings.locations.force_delete.title', { name: selectedLocation?.name })"
            :description="trans('pages.settings.locations.force_delete.description')"
            @confirm="handleForceDelete"
        />
    </SidebarLayout>
</template>
