<script setup lang="ts">
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import Paginator from '@/components/custom/Paginator.vue';
import { usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { show, create as createRoute, destroy } from '@/routes/crew/weekdays';
import { trans } from 'laravel-vue-i18n';
import { Edit, Eye, MoreHorizontal, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { BreadcrumbItemType } from '@/types';

const page = usePage<PageProps>();

const deleteDialogOpen = ref(false);
const selectedWeekdayId = ref<number | null>(null);

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('ui.navigation.home'),
        href: '/',
    },
    {
        title: trans('ui.navigation.crew'),
        href: '/crew',
    },
    {
        title: trans('pages.settings.weekdays.title'),
        href: '/crew/weekdays',
    },
]);

function goCreate() {
    router.visit(createRoute.url());
}

function confirmDelete(id: number) {
    selectedWeekdayId.value = id;
    deleteDialogOpen.value = true;
}

function handleDelete() {
    if (selectedWeekdayId.value) {
        router.delete(destroy.url(selectedWeekdayId.value), {
            onFinish: () => {
                deleteDialogOpen.value = false;
                selectedWeekdayId.value = null;
            },
        });
    }
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.settings.weekdays.title') }}</h1>
            <Button @click="goCreate">{{ trans('pages.settings.weekdays.new') }}</Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ trans('pages.settings.weekdays.fields.weekday') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.weekdays.fields.team') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.teams.fields.status') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.weekdays.fields.location') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.weekdays.fields.time') }}</TableHead>
                    <TableHead class="w-12"></TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="w in (page.props as any).weekdays.data" :key="w.id" class="group">
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(w.id))">{{ trans(`pages.settings.weekdays.days.${w.weekday}`) }}</TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(w.id))">{{ w.team?.name ?? '—' }}</TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(w.id))">
                        <span :class="w.is_ended ? 'text-red-600' : (w.active ? 'text-green-600' : 'text-gray-500')">
                            {{ w.active ? trans('pages.settings.teams.status.active') : trans('pages.settings.teams.status.inactive') }}
                        </span>
                    </TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(w.id))">{{ w.location?.name ?? '—' }}</TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(w.id))">{{ w.start_time }} - {{ w.end_time }}</TableCell>
                    <TableCell>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon">
                                    <MoreHorizontal class="h-4 w-4" />
                                    <span class="sr-only">Open menu</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="router.visit(show.url(w.id))">
                                    <Eye class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.locations.actions.view') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="router.visit(show.url(w.id, { query: { edit: 1 } }))">
                                    <Edit class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.locations.actions.edit') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-destructive focus:text-destructive" @click="confirmDelete(w.id)">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.locations.actions.delete') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
            </TableBody>
            <TableFooter v-if="(page.props as any).weekdays.last_page > 1">
                <TableRow>
                    <TableCell colspan="6">
                        <Paginator :collection="(page.props as any).weekdays" />
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>

        <DeleteConfirmationDialog
            v-model:open="deleteDialogOpen"
            :title="trans('pages.settings.weekdays.delete.title')"
            :description="trans('pages.settings.weekdays.delete.description')"
            @confirm="handleDelete"
        />
    </SidebarLayout>
</template>
