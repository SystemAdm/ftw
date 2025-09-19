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
import { index } from '@/routes/admin/postcodes';
import { type BreadcrumbItem } from '@/types';
import { faDumpster, faPencil, faPlus, faRecycle, faTrash } from '@fortawesome/free-solid-svg-icons';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { Head, router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

// No generated route helper for admin postcodes detected; breadcrumb will be title-only like Dashboard's pattern.

type PostalCode = {
    id: number;
    postal_code: string;
    city: string;
    state?: string | null;
    country?: string | null;
    county?: string | null;
    deleted_at?: string | null;
    created_at?: string;
    updated_at?: string;
};

const page = usePage<{ codes: PostalCode[]; filters: any; sort: any }>();
const codes = computed(() => page.props.codes ?? []);
const currentFilters = computed(() => page.props.filters ?? {});
const currentSort = computed(() => page.props.sort ?? { by: 'postal_code', dir: 'asc' });

// Toggle filters visibility; default to visible if any filter is active
const showFilters = ref(false);
if (page.props.filters) {
    const f = page.props.filters as Record<string, any>;
    showFilters.value = Object.keys(f).some((k) => f[k] !== undefined && f[k] !== null && f[k] !== '');
}

function apply(params: Record<string, any>) {
    router.get('/admin/postcodes', params, { preserveScroll: true, preserveState: true, replace: true });
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
    { title: 'Post Codes', href: index().url },
];

function restore(id: number) {
    router.post(
        `/admin/postcodes/${id}/restore`,
        {},
        {
            preserveScroll: true,
        },
    );
}

function destroy(id: number) {
    router.delete(`/admin/postcodes/${id}`, {
        preserveScroll: true,
    });
}
function edit(id: number) {
    router.get(`/admin/postcodes/${id}/edit`, {
        preserveScroll: true,
    });
}
function create() {
    router.get(`/admin/postcodes/create`, {
        preserveScroll: true,
    });
}
function forceDestroy(id: number) {
    router.delete(`/admin/postcodes/${id}/force`, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Post Codes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
            <div class="relative flex-1 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <div class="overflow-auto rounded-xl">
                    <div class="flex items-center justify-between border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                        <h1 class="text-xl font-semibold">Post Codes</h1>
                        <div class="flex items-center gap-2">
                            <Button variant="secondary" @click="showFilters = !showFilters" :aria-pressed="showFilters">
                                {{ showFilters ? 'Hide filters' : 'Show filters' }}
                            </Button>
                            <Button variant="default" @click="create()"> <font-awesome-icon :icon="faPlus" />New Postcode </Button>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-end gap-3 p-4" v-show="showFilters">
                        <div class="min-w-[220px] flex-1">
                            <Label class="mb-1 block text-xs font-medium" for="search">Search</Label>
                            <Input
                                id="search"
                                type="text"
                                :value="currentFilters.search ?? ''"
                                @change="(e: any) => apply({ ...currentFilters, search: e.target.value })"
                                class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none"
                                placeholder="Postal code or city"
                            />
                        </div>
                        <div class="min-w-[160px]">
                            <Label class="mb-1 block text-xs font-medium" for="country">Country</Label>
                            <Input
                                id="country"
                                type="text"
                                :value="currentFilters.country ?? ''"
                                @change="(e: any) => apply({ ...currentFilters, country: e.target.value })"
                                class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none"
                            />
                        </div>
                        <div class="min-w-[160px]">
                            <Label class="mb-1 block text-xs font-medium" for="state">State</Label>
                            <Input
                                id="state"
                                type="text"
                                :value="currentFilters.state ?? ''"
                                @change="(e: any) => apply({ ...currentFilters, state: e.target.value })"
                                class="w-full rounded-md border border-sidebar-border/70 px-3 py-2 focus:outline-none"
                            />
                        </div>
                        <div class="min-w-[160px]">
                            <Label class="mb-1 block text-xs font-medium" for="trashed">Trash filter</Label>
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
                        <div class="min-w-[200px]">
                            <Label class="mb-1 block text-xs font-medium" for="sort_by">Sort by</Label>
                            <div class="flex gap-2">
                                <Select
                                    :model-value="currentSort.by"
                                    @update:model-value="(v) => apply({ ...currentFilters, sort_by: v, sort_dir: currentSort.dir })"
                                >
                                    <SelectTrigger id="sort_by">
                                        <SelectValue placeholder="Column" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="postal_code">Postal Code</SelectItem>
                                        <SelectItem value="city">City</SelectItem>
                                        <SelectItem value="state">State</SelectItem>
                                        <SelectItem value="country">Country</SelectItem>
                                        <SelectItem value="county">County</SelectItem>
                                        <SelectItem value="created_at">Created</SelectItem>
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
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 text-left dark:bg-gray-800/30">
                            <tr>
                                <th class="p-3"><button class="underline" @click="toggleSort('postal_code')">Postal Code</button></th>
                                <th class="p-3"><button class="underline" @click="toggleSort('city')">City</button></th>
                                <th class="p-3"><button class="underline" @click="toggleSort('county')">County</button></th>
                                <th class="p-3"><button class="underline" @click="toggleSort('state')">State</button></th>
                                <th class="p-3"><button class="underline" @click="toggleSort('country')">Country</button></th>
                                <th class="p-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="c in codes" :key="c.id" class="border-t border-sidebar-border/70 dark:border-sidebar-border">
                                <td class="p-3">{{ c.postal_code }}</td>
                                <td class="p-3">{{ c.city }}</td>
                                <td class="p-3">{{ c.county ?? '-' }}</td>
                                <td class="p-3">{{ c.state ?? '-' }}</td>
                                <td class="p-3">{{ c.country ?? '-' }}</td>
                                <td class="space-x-2 p-3">
                                    <div v-if="c.deleted_at" class="inline-flex gap-2">
                                        <Button variant="secondary" size="sm" @click="restore(c.id)"><fontAwesomeIcon :icon="faRecycle" />Restore</Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm"
                                                    ><fontAwesomeIcon :icon="faDumpster" />Delete permanently</Button
                                                >
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Delete this postcode permanently?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This action cannot be undone. This will permanently delete postcode {{ c.postal_code }} (ID
                                                        {{ c.id }}).
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction as-child>
                                                        <Button variant="destructive" size="sm" class="text-white" @click="forceDestroy(c.id)"
                                                            >Yes, delete</Button
                                                        >
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                    <div v-else class="inline-flex gap-2">
                                        <Button variant="secondary" size="sm" @click="edit(c.id)"><font-awesome-icon :icon="faPencil" />Edit</Button>
                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <Button variant="destructive" size="sm"><font-awesome-icon :icon="faTrash" />Delete</Button>
                                            </AlertDialogTrigger>
                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Delete this postcode?</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        This will soft-delete postcode {{ c.postal_code }} (ID {{ c.id }}). You can restore it later.
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Cancel</AlertDialogCancel>
                                                    <AlertDialogAction as-child>
                                                        <Button variant="destructive" size="sm" class="text-white" @click="destroy(c.id)"
                                                            >Yes, delete</Button
                                                        >
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="codes.length === 0">
                                <td class="p-6 text-center text-gray-500" colspan="8">No postal codes found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Styles align with app layout using Tailwind utility classes. */
</style>
