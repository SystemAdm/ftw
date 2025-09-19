<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import { index } from '@/routes/admin/locations';
import { type BreadcrumbItem } from '@/types';
import { faDumpster, faPencil, faPlus, faRecycle, faTrash } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Pagination, PaginationContent, PaginationFirst, PaginationPrevious, PaginationItem, PaginationNext, PaginationLast, PaginationEllipsis } from '@/components/ui/pagination';

// Types

type Location = {
    id: number;
    postal_code: number;
    name: string;
    active: boolean;
    description?: string | null;
    latitude?: string | null;
    longitude?: string | null;
    google_maps_url?: string | null;
    images?: string | null;
    street_address?: string | null;
    street_number?: string | null;
    link?: string | null;
    deleted_at?: string | null;
    created_at?: string;
    updated_at?: string;
};

const page = usePage<{ locations: { data: Location[] }; filters: any; sort: any }>();
const rows = computed(() => (page.props.locations as any)?.data ?? []);
const currentFilters = computed(() => page.props.filters ?? {});
const currentSort = computed(() => page.props.sort ?? { by: 'name', dir: 'asc' });

function goToPage(p: number) {
    apply({
        ...currentFilters.value,
        sort_by: currentSort.value.by,
        sort_dir: currentSort.value.dir,
        page: p,
    });
}

// Toggle filters visibility; show by default if any filter active
const showFilters = ref(false);
if (page.props.filters) {
    const f = page.props.filters as Record<string, any>;
    showFilters.value = Object.keys(f).some((k) => f[k] !== undefined && f[k] !== null && f[k] !== '');
}

function apply(params: Record<string, any>) {
    const pruned = Object.fromEntries(
        Object.entries(params).filter((entry) => {
            const v = entry[1];
            return v !== '' && v !== null && v !== undefined;
        }),
    );
    router.get('/admin/locations', pruned, { preserveScroll: true, preserveState: true, replace: true });
}

function toggleSort(column: string) {
    const by = currentSort.value.by === column ? column : column;
    const dir = currentSort.value.by === column && currentSort.value.dir === 'asc' ? 'desc' : 'asc';
    apply({
        ...currentFilters.value,
        sort_by: by,
        sort_dir: dir,
    });
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: dashboard().url },
    { title: 'Locations', href: index().url },
];

function restoreRow(id: number) {
    router.post(`/admin/locations/${id}/restore`, {}, { preserveScroll: true });
}

function destroyRow(id: number) {
    router.delete(`/admin/locations/${id}`, { preserveScroll: true });
}
function editRow(id: number) {
    router.get(`/admin/locations/${id}/edit`, { preserveScroll: true });
}
function createRow() {
    router.get(`/admin/locations/create`, { preserveScroll: true });
}
function forceDestroyRow(id: number) {
    router.delete(`/admin/locations/${id}/force`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Locations" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="overflow-auto rounded-xl">
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h1 class="text-xl font-semibold">Locations</h1>
                        <div class="flex items-center gap-2">
                            <Button variant="secondary" @click="showFilters = !showFilters" :aria-pressed="showFilters">
                                {{ showFilters ? 'Hide filters' : 'Show filters' }}
                            </Button>
                            <Button variant="default" @click="createRow()"> <font-awesome-icon :icon="faPlus" />New Location </Button>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-end gap-3 p-4" v-show="showFilters">
                        <div class="min-w-[160px]">
                            <Label class="mb-1 block text-xs font-medium" for="active">Active</Label>
                            <Select
                                :model-value="currentFilters.active === true ? '1' : currentFilters.active === false ? '0' : 'any'"
                                @update:model-value="(v) => apply({ ...currentFilters.value, active: v === '1' ? true : v === '0' ? false : undefined })"
                            >
                                <SelectTrigger id="active">
                                    <SelectValue placeholder="Any" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="any">Any</SelectItem>
                                    <SelectItem value="1">Active</SelectItem>
                                    <SelectItem value="0">Inactive</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="min-w-[160px]">
                            <Label class="mb-1 block text-xs font-medium" for="trashed">Trash filter</Label>
                            <Select :model-value="currentFilters.trashed ?? ''" @update:model-value="(v) => apply({ ...currentFilters.value, trashed: v })">
                                <SelectTrigger id="trashed">
                                    <SelectValue placeholder="Without trashed" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="without">Without trashed</SelectItem>
                                    <SelectItem value="all">With trashed</SelectItem>
                                    <SelectItem value="only">Only trashed</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="min-w-[200px]">
                            <Label class="mb-1 block text-xs font-medium" for="sort_by">Sort by</Label>
                            <div class="flex gap-2">
                                <Select
                                    :model-value="currentSort.by"
                                    @update:model-value="(v) => apply({ ...currentFilters.value, sort_by: v, sort_dir: currentSort.dir })"
                                >
                                    <SelectTrigger id="sort_by">
                                        <SelectValue placeholder="Column" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="name">Name</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="postal_code">Postal Code</SelectItem>
                                        <SelectItem value="created_at">Created</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select
                                    :model-value="currentSort.dir"
                                    @update:model-value="(v) => apply({ ...currentFilters.value, sort_by: currentSort.by, sort_dir: v })"
                                    class="w-[160px]"
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder="Direction" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="asc">Asc</SelectItem>
                                        <SelectItem value="desc">Desc</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div class="min-w-[120px]">
                            <Button variant="secondary" @click="apply({})">Reset</Button>
                        </div>
                    </div>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead><button class="underline" @click="toggleSort('name')">Name</button></TableHead>
                                <TableHead><button class="underline" @click="toggleSort('postal_code')">Postal Code</button></TableHead>
                                <TableHead><button class="underline" @click="toggleSort('active')">Active</button></TableHead>
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="l in rows" :key="l.id">
                                <TableCell>{{ l.name }}</TableCell>
                                <TableCell>{{ l.postal_code }}</TableCell>
                                <TableCell>
                                    <span
                                        :class="['rounded px-2 py-0.5 text-xs', l.active ? 'bg-green-600 text-white' : 'bg-gray-300 text-gray-800']"
                                    >
                                        {{ l.active ? 'Yes' : 'No' }}
                                    </span>
                                </TableCell>
                                <TableCell class="space-x-2 ">
                                    <div v-if="l.deleted_at" class="inline-flex gap-2">
                                        <Button variant="secondary" size="sm" @click="restoreRow(l.id)"
                                            ><fontAwesomeIcon :icon="faRecycle" />Restore</Button
                                        >
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm"
                                                    ><font-awesome-icon :icon="faDumpster" />Delete permanently</Button
                                                >
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Delete this location permanently?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This action cannot be undone. This will permanently delete location '{{ l.name }}' (ID
                                                        {{ l.id }}).
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction as-child>
                                                        <Button variant="destructive" size="sm" class="text-white" @click="forceDestroyRow(l.id)"
                                                            >Yes, delete</Button
                                                        >
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                    <div v-else class="inline-flex gap-2">
                                        <Button variant="secondary" size="sm" @click="editRow(l.id)"
                                            ><font-awesome-icon :icon="faPencil" />Edit</Button
                                        >
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm"><font-awesome-icon :icon="faTrash" />Delete</Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Delete this location?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This will soft-delete '{{ l.name }}' (ID {{ l.id }}). You can restore it later.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction as-child>
                                                        <Button variant="destructive" size="sm" class="text-white" @click="destroyRow(l.id)"
                                                            >Yes, delete</Button
                                                        >
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="rows.length === 0">
                                <TableCell class="p-6 text-center text-gray-500" colspan="8">No locations found.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <div class="p-4">
                        <Pagination
                            v-if="page.props.locations && (page.props.locations as any).total > (page.props.locations as any).per_page"
                            v-slot="{ page: currentPage }"
                            :total="(page.props.locations as any).total"
                            :page="(page.props.locations as any).current_page"
                            :items-per-page="(page.props.locations as any).per_page"
                            show-edges
                            @update:page="goToPage"
                        >
                            <PaginationContent v-slot="{ items }">
                                <PaginationFirst />
                                <PaginationPrevious />
                                <PaginationItem
                                    v-for="(item, idx) in items.filter(i => i.type === 'page')"
                                    :key="`p-${idx}`"
                                    v-bind="item"
                                    :is-active="item.value === currentPage"
                                >
                                    {{ item.value }}
                                </PaginationItem>
                                <PaginationEllipsis
                                    v-for="(item, idx) in items.filter(i => i.type === 'ellipsis')"
                                    :key="`e-${idx}`"
                                    :index="idx"
                                />
                                <PaginationNext />
                                <PaginationLast />
                            </PaginationContent>
                        </Pagination>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Using Tailwind utility classes for styling */
</style>
