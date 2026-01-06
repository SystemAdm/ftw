<script setup lang="ts">
import DeleteConfirmationDialog from '@/components/custom/DeleteConfirmationDialog.vue';
import Paginator from '@/components/custom/Paginator.vue';
import SidebarLayout from '@/components/layouts/SidebarLayout.vue';
import { BreadcrumbItemType } from '@/types';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Table, TableBody, TableCell, TableFooter, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { create, destroy, index, show } from '@/routes/crew/events';
import { router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { Edit, Eye, MoreHorizontal, Trash2 } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage<PageProps>();

const deleteDialogOpen = ref(false);
const selectedEventId = ref<number | null>(null);

const breadcrumbs = computed<BreadcrumbItemType[]>(() => [
    {
        title: trans('pages.ui.navigation.events'),
        href: index.url(),
    },
]);

function goCreate() {
    router.visit(create.url());
}

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

function confirmDelete(id: number) {
    selectedEventId.value = id;
    deleteDialogOpen.value = true;
}

function handleDelete() {
    if (selectedEventId.value) {
        router.delete(destroy.url(selectedEventId.value), {
            onFinish: () => {
                deleteDialogOpen.value = false;
                selectedEventId.value = null;
            },
        });
    }
}
</script>

<template>
    <SidebarLayout :breadcrumbs="breadcrumbs">
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ trans('pages.ui.navigation.events') }}</h1>
            <Button @click="goCreate">{{ trans('pages.settings.events.new') }}</Button>
        </div>

        <Table>
            <TableHeader>
                <TableRow>
                    <TableHead>{{ trans('pages.settings.events.fields.title') }}</TableHead>
                    <TableHead>{{ trans('pages.settings.events.fields.team') }}</TableHead>
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
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(e.id))">{{ e.team?.name ?? '—' }}</TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(e.id))">{{ e.location?.name ?? '—' }}</TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(e.id))">{{ formatDate(e.event_start) }}</TableCell>
                    <TableCell class="cursor-pointer" @click="router.visit(show.url(e.id))">
                        <Badge :variant="getStatusColor(e.status)">
                            {{ trans(`pages.settings.events.status.${e.status}`) }}
                        </Badge>
                    </TableCell>
                    <TableCell>
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button variant="ghost" size="icon">
                                    <MoreHorizontal class="h-4 w-4" />
                                    <span class="sr-only">Open menu</span>
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end">
                                <DropdownMenuItem @click="router.visit(show.url(e.id))">
                                    <Eye class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.events.actions.view') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="router.visit(show.url(e.id, { query: { edit: 1 } }))">
                                    <Edit class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.events.actions.edit') }}
                                </DropdownMenuItem>
                                <DropdownMenuItem class="text-destructive focus:text-destructive" @click="confirmDelete(e.id)">
                                    <Trash2 class="mr-2 h-4 w-4" />
                                    {{ trans('pages.settings.events.actions.delete') }}
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </TableCell>
                </TableRow>
                <TableRow v-if="(page.props as any).events.data.length === 0">
                    <TableCell colspan="6" class="py-8 text-center text-muted-foreground">
                        {{ trans('pages.settings.events.none') }}
                    </TableCell>
                </TableRow>
            </TableBody>
            <TableFooter v-if="(page.props as any).events.last_page > 1">
                <TableRow>
                    <TableCell colspan="6">
                        <Paginator :collection="(page.props as any).events" />
                    </TableCell>
                </TableRow>
            </TableFooter>
        </Table>

        <DeleteConfirmationDialog
            v-model:open="deleteDialogOpen"
            :title="trans('pages.settings.events.delete.title')"
            :description="trans('pages.settings.events.delete.description')"
            @confirm="handleDelete"
        />
    </SidebarLayout>
</template>
