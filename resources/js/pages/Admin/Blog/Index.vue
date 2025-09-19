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
import { faArrowDown19, faArrowDown91, faArrowDownAZ, faArrowDownZA, faPencil, faPlus, faSort, faTrash } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head, router, usePage } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed, ref, watch } from 'vue';

// simple debounce helper local to this file
function debounce<F extends (...args: any[]) => void>(fn: F, wait = 400) {
    let t: any;
    return (...args: any[]) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...args), wait);
    };
}

interface Blog {
    id: number;
    title: string;
    slug: string;
    published_at?: string | null;
    published?: string | null;
    // Spatie tags can be strings or localized objects; keep typing lax here
    tags?: Array<{ id?: number | string; name?: string | Record<string, string> } | any> | null;
}

type PageProps = { blogs: { data: Blog[] }; filters?: any; sort?: any };
const page = usePage<PageProps>();

function tagLabel(tag: any): string {
    const n = tag?.name;
    if (!n) return '';
    if (typeof n === 'string') return n;
    if (typeof n === 'object') {
        const vals = Object.values(n as Record<string, string>);
        if (vals.length > 0 && typeof vals[0] === 'string') return vals[0] as string;
    }
    try {
        return String(n);
    } catch {
        return '';
    }
}

const blogs = computed(() => page.props.blogs?.data ?? []);
const currentFilters = computed(() => page.props.filters ?? {});
const currentSort = computed(() => page.props.sort ?? { by: 'created_at', dir: 'desc' });

// Local state for tag input to avoid binding directly to server-provided props
const tagQuery = ref<string>(currentFilters.value.tag ?? '');
// Keep tagQuery in sync if filters change due to navigation
watch(currentFilters, (nf) => {
    tagQuery.value = nf?.tag ?? '';
});

// debounced applier for tag input typing
const debouncedTag = debounce((val: string) => {
    apply({ ...currentFilters.value, tag: val });
}, 400);

function goToPage(p: number) {
    apply({
        ...currentFilters.value,
        sort_by: currentSort.value.by,
        sort_dir: currentSort.value.dir,
        page: p,
    });
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Admin', href: dashboard().url },
    { title: 'Blogs', href: '/admin/blogs' },
];

// Toggle filters visibility; show by default if any filter active
const showFilters = ref(false);
if (page.props.filters) {
    const f = page.props.filters as Record<string, any>;
    showFilters.value = Object.keys(f).some((k) => f[k] !== undefined && f[k] !== null && f[k] !== '' && !(k === 'trashed' && f[k] === ''));
}

function apply(params: Record<string, any>) {
    const pruned = Object.fromEntries(
        Object.entries(params).filter((entry) => {
            const v = entry[1];
            return v !== '' && v !== null && v !== undefined;
        }),
    );
    router.get('/admin/blogs', pruned, { preserveScroll: true, preserveState: false, replace: true });
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

function createBlog() {
    router.get('/admin/blogs/create');
}
function editBlog(id: number) {
    router.get(`/admin/blogs/${id}/edit`);
}
function deleteBlog(id: number) {
    router.delete(`/admin/blogs/${id}`);
}
</script>

<template>
    <Head title="Blogs" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="overflow-auto rounded-xl">
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h1 class="text-xl font-semibold">Blogs</h1>
                        <div class="flex items-center gap-2">
                            <Button variant="secondary" @click="showFilters = !showFilters" :aria-pressed="showFilters">
                                {{ showFilters ? 'Hide filters' : 'Show filters' }}
                            </Button>
                            <Button variant="default" @click="createBlog"><font-awesome-icon :icon="faPlus" />New Blog</Button>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-end gap-3 p-4" v-show="showFilters">
                        <div class="min-w-[160px]">
                            <Label for="published">Published</Label>
                            <Select
                                :model-value="currentFilters.published_at === true ? '1' : currentFilters.published_at === false ? '0' : 'any'"
                                @update:model-value="(v) => apply({ ...currentFilters, published_at: v === '1' ? '1' : v === '0' ? '0' : undefined })"
                            >
                                <SelectTrigger id="published">
                                    <SelectValue placeholder="Any" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="any">Any</SelectItem>
                                    <SelectItem value="1">Published</SelectItem>
                                    <SelectItem value="0">Unpublished</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        <div class="min-w-[160px]">
                            <Label for="trashed">Trash filter</Label>
                            <Select :model-value="currentFilters.trashed ?? ''" @update:model-value="(v) => apply({ ...currentFilters, trashed: v })">
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
                        <div class="min-w-[220px] flex-1">
                            <Label for="tag">Tag</Label>
                            <Input
                                id="tag"
                                type="text"
                                v-model="tagQuery"
                                @keyup.enter="apply({ ...currentFilters, tag: tagQuery })"
                                placeholder="Tag name"
                            />
                        </div>
                        <div class="min-w-[200px]">
                            <Label for="sort_by">Sort by</Label>
                            <div class="flex gap-2">
                                <Select
                                    :model-value="currentSort.by"
                                    @update:model-value="(v) => apply({ ...currentFilters, sort_by: v, sort_dir: currentSort.dir })"
                                >
                                    <SelectTrigger id="sort_by">
                                        <SelectValue placeholder="Column" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="created_at">Created</SelectItem>
                                        <SelectItem value="title">Title</SelectItem>
                                        <SelectItem value="published_at">Published At</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Select
                                    :model-value="currentSort.dir"
                                    @update:model-value="(v) => apply({ ...currentFilters, sort_by: currentSort.by, sort_dir: v })"
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

                    <Table class="min-w-full text-sm">
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    <button class="mr-2 text-foreground" @click="toggleSort('title')">
                                        <font-awesome-icon :icon="faArrowDownAZ" v-if="currentSort.by === 'title' && currentSort.dir === 'asc'" />
                                        <font-awesome-icon
                                            :icon="faArrowDownZA"
                                            v-else-if="currentSort.by === 'title' && currentSort.dir === 'desc'"
                                        />
                                        <font-awesome-icon :icon="faSort" v-else />
                                    </button>
                                    Title
                                </TableHead>
                                <TableHead>Tags</TableHead>
                                <TableHead
                                    ><button class="mr-2 text-foreground" @click="toggleSort('published_at')">
                                        <font-awesome-icon
                                            :icon="faArrowDown91"
                                            v-if="currentSort.by === 'published_at' && currentSort.dir === 'asc'"
                                        />
                                        <font-awesome-icon
                                            :icon="faArrowDown19"
                                            v-else-if="currentSort.by === 'published_at' && currentSort.dir === 'desc'"
                                        />
                                        <font-awesome-icon :icon="faSort" v-else />
                                    </button>
                                    Published</TableHead
                                >
                                <TableHead>Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="b in blogs" :key="b.id" class="border-t">
                                <TableCell>{{ b.title }}</TableCell>
                                <TableCell>
                                    <template v-if="b.tags && b.tags.length">
                                        <span
                                            v-for="tag in b.tags"
                                            :key="tag?.id ?? tag?.slug ?? tagLabel(tag)"
                                            class="mr-2 inline-block rounded bg-accent px-2 py-0.5 text-xs"
                                        >
                                            {{ tagLabel(tag) }}
                                        </span>
                                    </template>
                                    <template v-else> - </template>
                                </TableCell>
                                <TableCell>{{ b.published ?? '-' }}</TableCell>
                                <TableCell class="space-x-2">
                                    <Button size="sm" variant="secondary" @click="editBlog(b.id)"
                                        ><font-awesome-icon :icon="faPencil" />{{ trans('Edit') }}</Button
                                    >
                                    <Button size="sm" variant="destructive" @click="deleteBlog(b.id)"
                                        ><font-awesome-icon :icon="faTrash" />Delete</Button
                                    >
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="blogs.length === 0">
                                <TableCell colspan="4" class="p-6 text-center text-muted-foreground">No blogs.</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <div class="p-4">
                        <Pagination
                            v-if="page.props.blogs && (page.props.blogs as any).total > (page.props.blogs as any).per_page"
                            v-slot="{ page: currentPage }"
                            :total="(page.props.blogs as any).total"
                            :page="(page.props.blogs as any).current_page"
                            :items-per-page="(page.props.blogs as any).per_page"
                            show-edges
                            @update:page="goToPage"
                        >
                            <PaginationContent v-slot="{ items }">
                                <PaginationFirst />
                                <PaginationPrevious />
                                <PaginationItem
                                    v-for="(item, idx) in items.filter((i) => i.type === 'page')"
                                    :key="`p-${idx}`"
                                    v-bind="item"
                                    :is-active="item.value === currentPage"
                                    :value="item.value"
                                >
                                    {{ item.value }}
                                </PaginationItem>
                                <PaginationEllipsis
                                    v-for="(item, idx) in items.filter((i) => i.type === 'ellipsis')"
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
