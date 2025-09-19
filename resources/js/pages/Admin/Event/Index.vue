<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes/admin';
import type { BreadcrumbItem } from '@/types';
import { faArrowDownAZ, faArrowDownZA, faPencil, faPlus, faTrash, faRotateLeft, faSkull } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface EventItem {
    id: number;
    title: string;
    slug: string;
    status: 'draft' | 'active' | null;
    event_start?: string | null;
    event_end?: string | null;
    signup_needed?: boolean;
    deleted_at?: string | null;
}

type PageProps = { events: { data: EventItem[]; current_page: number; last_page: number }; filters?: any; sort?: any };
const page = usePage<PageProps>();

const events = computed(() => page.props.events?.data ?? []);
const currentFilters = computed(() => page.props.filters ?? {});
const currentSort = computed(() => page.props.sort ?? { by: 'event_start', dir: 'desc' });

const showFilters = ref(false);
if (page.props.filters) {
    const f = page.props.filters as Record<string, any>;
    showFilters.value = Object.keys(f).some((k) => f[k] !== undefined && f[k] !== null && f[k] !== '' && !(k === 'trashed' && f[k] === ''));
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: dashboard().url },
    { title: 'Events', href: '/admin/events' },
];

function apply(params: Record<string, any>) {
    const pruned = Object.fromEntries(
        Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined),
    );
    router.get('/admin/events', pruned, { preserveScroll: true, preserveState: false, replace: true });
}

function toggleSort(column: string) {
    const by = currentSort.value.by === column ? column : column;
    const dir = currentSort.value.by === column ? (currentSort.value.dir === 'asc' ? 'desc' : 'asc') : 'asc';
    apply({ ...currentFilters.value, sort_by: by, sort_dir: dir });
}

function goToPage(p: number) {
    apply({ ...currentFilters.value, sort_by: currentSort.value.by, sort_dir: currentSort.value.dir, page: p });
}

function destroyItem(id: number) {
    if (!confirm('Delete this event?')) return;
    router.delete(`/admin/events/${id}`, { preserveScroll: true });
}

function restoreItem(id: number) {
    if (!confirm('Restore this event?')) return;
    router.post(`/admin/events/${id}/restore`, {}, { preserveScroll: true });
}

function forceDestroyItem(id: number) {
    if (!confirm('Permanently delete this event? This cannot be undone.')) return;
    router.delete(`/admin/events/${id}/force`, { preserveScroll: true });
}
</script>

<template>
    <Head title="Events" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="overflow-auto rounded-xl">
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h1 class="text-xl font-semibold">Events</h1>
                        <Button as-child>
                            <a href="/admin/events/create"><FontAwesomeIcon :icon="faPlus" class="mr-2" /> New Event</a>
                        </Button>
                    </div>

                    <div class="border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <button class="text-sm text-muted-foreground underline" @click="showFilters = !showFilters">{{ showFilters ? 'Hide' : 'Show' }} filters</button>
                        <div v-if="showFilters" class="mt-3 grid grid-cols-1 gap-3 md:grid-cols-5">
                            <div>
                                <Label for="search">Search</Label>
                                <Input id="search" :value="currentFilters.search ?? ''" @input="(e: any) => apply({ ...currentFilters, search: e.target.value })" />
                            </div>
                            <div>
                                <Label>Status</Label>
                                <Select :model-value="currentFilters.status ?? ''" @update:model-value="(v: any) => apply({ ...currentFilters, status: v })">
                                    <SelectTrigger><SelectValue placeholder="All" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">All</SelectItem>
                                        <SelectItem value="draft">Draft</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label>Signup needed</Label>
                                <Select :model-value="currentFilters.signup_needed ?? ''" @update:model-value="(v: any) => apply({ ...currentFilters, signup_needed: v })">
                                    <SelectTrigger><SelectValue placeholder="Any" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Any</SelectItem>
                                        <SelectItem value="1">Yes</SelectItem>
                                        <SelectItem value="0">No</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div>
                                <Label>Trashed</Label>
                                <Select :model-value="currentFilters.trashed ?? 'without'" @update:model-value="(v: any) => apply({ ...currentFilters, trashed: v })">
                                    <SelectTrigger><SelectValue placeholder="without" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="without">Without</SelectItem>
                                        <SelectItem value="only">Only</SelectItem>
                                        <SelectItem value="all">With</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="flex items-end"><Button variant="secondary" @click="apply({})"><FontAwesomeIcon :icon="faRotateLeft" class="mr-2" />Reset</Button></div>
                        </div>
                    </div>

                    <div class="p-4">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead class="w-[30%]">
                                            <button class="flex items-center gap-1" @click="toggleSort('title')">
                                                Title
                                                <FontAwesomeIcon :icon="currentSort.dir === 'asc' ? faArrowDownAZ : faArrowDownZA" class="text-xs" />
                                            </button>
                                        </TableHead>
                                        <TableHead>
                                            <button class="flex items-center gap-1" @click="toggleSort('event_start')">Start</button>
                                        </TableHead>
                                        <TableHead>
                                            <button class="flex items-center gap-1" @click="toggleSort('event_end')">End</button>
                                        </TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Signup</TableHead>
                                        <TableHead class="w-[160px] text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="e in events" :key="e.id">
                                        <TableCell class="font-medium">
                                            <div class="flex items-center gap-2" :class="{ 'opacity-60': !!e.deleted_at }">
                                                <span>{{ e.title }}</span>
                                                <span v-if="e.deleted_at" class="rounded bg-red-100 px-2 py-0.5 text-xs text-red-700">deleted</span>
                                            </div>
                                            <div class="text-xs text-muted-foreground">/{{ e.slug }}</div>
                                        </TableCell>
                                        <TableCell>{{ e.event_start ? new Date(e.event_start).toLocaleString() : '—' }}</TableCell>
                                        <TableCell>{{ e.event_end ? new Date(e.event_end).toLocaleString() : '—' }}</TableCell>
                                        <TableCell>
                                            <span v-if="e.status === 'draft'" class="rounded bg-yellow-100 px-2 py-0.5 text-xs text-yellow-700">draft</span>
                                            <span v-else-if="e.status === 'active'" class="rounded bg-green-100 px-2 py-0.5 text-xs text-green-700">active</span>
                                            <span v-else class="rounded bg-slate-100 px-2 py-0.5 text-xs text-slate-700">—</span>
                                        </TableCell>
                                        <TableCell>{{ e.signup_needed ? 'yes' : 'no' }}</TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex justify-end gap-2">
                                                <Button as-child size="sm" variant="secondary"><a :href="`/admin/events/${e.id}/edit`"><FontAwesomeIcon :icon="faPencil" /></a></Button>
                                                <Button v-if="!e.deleted_at" size="sm" variant="destructive" @click="destroyItem(e.id)"><FontAwesomeIcon :icon="faTrash" /></Button>
                                                <Button v-else size="sm" variant="secondary" @click="restoreItem(e.id)">Restore</Button>
                                                <Button v-if="e.deleted_at" size="sm" variant="destructive" @click="forceDestroyItem(e.id)"><FontAwesomeIcon :icon="faSkull" /></Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-muted-foreground">Page {{ page.props.events?.current_page ?? 1 }} / {{ page.props.events?.last_page ?? 1 }}</div>
                            <Pagination>
                                <PaginationContent>
                                    <PaginationItem>
                                        <PaginationFirst href="#" :disabled="(page.props.events?.current_page ?? 1) <= 1" @click.prevent="goToPage(1)" />
                                    </PaginationItem>
                                    <PaginationItem>
                                        <PaginationPrevious href="#" :disabled="(page.props.events?.current_page ?? 1) <= 1" @click.prevent="goToPage((page.props.events?.current_page ?? 1) - 1)" />
                                    </PaginationItem>
                                    <PaginationItem>
                                        <PaginationNext href="#" :disabled="(page.props.events?.current_page ?? 1) >= (page.props.events?.last_page ?? 1)" @click.prevent="goToPage((page.props.events?.current_page ?? 1) + 1)" />
                                    </PaginationItem>
                                    <PaginationItem>
                                        <PaginationLast href="#" :disabled="(page.props.events?.current_page ?? 1) >= (page.props.events?.last_page ?? 1)" @click.prevent="goToPage(page.props.events?.last_page ?? 1)" />
                                    </PaginationItem>
                                    <PaginationEllipsis />
                                </PaginationContent>
                            </Pagination>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
